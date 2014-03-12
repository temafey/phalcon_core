<?php

namespace Engine\Builder;

use Phalcon\Db\Column,
    Engine\Builder\Component,
    Engine\Builder\BuilderException,
    Engine\Builder\Script\Color,
    Phalcon\Text as Utils;
use Engine\Builder\Traits\BasicTemplater as TBasicTemplater;
use Engine\Builder\Traits\ModelTemplater as TModelTemplater;
use Engine\Tools\Inflector;

/**
 * ModelBuilderComponent
 *
 * Builder to generate models
 */
class Model extends Component
{
    use TBasicTemplater, TModelTemplater;

    public function __construct($options)
    {
        if (!isset($options['table_name']) || empty($options['table_name'])) {
            throw new BuilderException("Please, specify the model name");
        }
        $this->_options = $options;
    }

    /**
     * Returns the associated PHP type
     *
     * @param string $type
     * @return string
     */
    public function getPHPType($type)
    {
        switch ($type) {
            case Column::TYPE_INTEGER:
                return 'integer';
                break;
            case Column::TYPE_DECIMAL:
            case Column::TYPE_FLOAT:
                return 'double';
                break;
            case Column::TYPE_DATE:
            case Column::TYPE_VARCHAR:
            case Column::TYPE_DATETIME:
            case Column::TYPE_CHAR:
            case Column::TYPE_TEXT:
                return 'string';
                break;
            default:
                return 'string';
                break;
        }
    }

    public function build()
    {
        // Check name (table name)
        if (!$this->_options['table_name']) {
            throw new BuilderException("You must specify the table name");
        }


        // Get config
        $config = $this->_getConfig('');


        // build options
        $this->buildOptions($this->_options['table_name'], $config);


        // If no database configuration in config throw exception
        if (!isset($config->database)) {
            throw new BuilderException(
                "Database configuration cannot be loaded from your config file"
            );
        }


        // if no adapter in database config throw exception
        if (!isset($config->database->adapter)) {
            throw new BuilderException(
                "Adapter was not found in the config. " .
                "Please specify a config variable [database][adapter]"
            );
        }


        // If model already exist throw exception
        if (file_exists($this->_builderOptions['path'])) {
            if (!$this->_options['force']) {
                throw new BuilderException(
                    "The model file '" . $this->_builderOptions['path'] .
                    "' already exists in models dir"
                );
            }
        }


        // Get and check database adapter
        $adapter = $config->database->adapter;
        $this->isSupportedAdapter($adapter);
        if (isset($config->database->adapter)) {
            $adapter = $config->database->adapter;
        } else {
            $adapter = 'Mysql';
        }
        // Get database configs
        if (is_object($config->database)) {
            $configArray = $config->database->toArray();
        } else {
            $configArray = $config->database;
        }
        $adapterName = 'Phalcon\Db\Adapter\Pdo\\' . $adapter;
        unset($configArray['adapter']);
        // Open Connection
        $db = new $adapterName($configArray);


        $initialize = array();
        if (isset($this->_options['schema'])) {
            if ($this->_options['schema'] != $config->database->dbname) {
                $initialize[] = sprintf(
                    $this->templateThis, 'setSchema', '"' . $this->_options['schema'] . '"'
                );
            }
            $schema = $this->_options['schema'];
        } elseif ($adapter == 'Postgresql') {
            $schema = 'public';
            $initialize[] = sprintf(
                $this->templateThis, 'setSchema', '"' . $this->_options['schema'] . '"'
            );
        } else {
            $schema = $config->database->dbname;
        }


        // Check if table exist in database
        $table = $this->_options['table_name'];
        if ($db->tableExists($table, $schema)) {
            $fields = $db->describeColumns($table, $schema);
        } else {
            throw new BuilderException('Table "' . $table . '" does not exists');
        }


        // Set extender class
        $extends = '\\Engine\\Mvc\\Model';


        $attributes = array();
        $belongsTo = array();
        foreach ($fields as $field) {
            $type = $this->getPHPType($field->getType());
            $attributes[] = sprintf(
                $this->templateEmptyAttribute, $type, 'public', $field->getName()
            );

            // Build belongsTo relations
            preg_match('/^(.*)\_i{1}d{1}$/', $field->getName(), $matches);
            if (!empty($matches)) {
                $belongsTo[] = sprintf($this->templateModelRelation, 'belongsTo', 'id', ucfirst($this->_builderOptions['moduleName']).'\Model\\'.Inflector::modelize($matches[1]), $matches[0], $this->_buildRelationOptions([
                    'alias' => $this->getAlias($matches[1])
                ]));
            }

            if ($field->getName() == 'id') {
                $this->_options['primary_column'] = $field->getName();
                $this->_options['order_expr'] = $field->getName();
            }

            if ($field->getName() == 'title' || $field->getName() == 'name') {
                $this->_options['name_expr'] = $field->getName();
            }
        }


        // Model::initialize() code
        $initializeCode = "";
        if (count($belongsTo) > 0) {
            foreach ($belongsTo as $rel) {
                $initializeCode .= $rel."\n";
            }
        }


        // Join attributes to content
        $content = join('', $attributes);


        // Join engine properties
        if (isset($this->_options['primary_column'])) {
            $content .= sprintf($this->templateModelPrimaryColumn, $this->_options['primary_column']);
        }


        // Join engine name_expr
        if (isset($this->_options['name_expr'])) {
            $content .= sprintf($this->templateModelDefaultTitleColumn, $this->_options['name_expr']);
        }


        // Join engine attributes
        if (isset($this->_options['attributes']) && is_array($this->_options['attributes'])) {
            $content .= sprintf($this->templateModelAttribute, $this->_options['attributes']);
        }


        // Join engine orderExpr
        if (isset($this->_options['order_expr'])) {
            $content .= sprintf($this->templateModelOrderExpr, $this->_options['order_expr']);
        }


        // Join engine orderAsc
        if (isset($this->_options['order_asc']) && is_bool($this->_options['order_asc'])) {
            $content .= sprintf($this->templateModelOrder, $this->_options['order_asc']);
        }else {
            $content .= sprintf($this->templateModelOrder, 'true');
        }


        // Join initialize code to content
        $content .= '';
        if (!empty($initializeCode)) {
            $content .= sprintf($this->templateInitialize, $initializeCode);
        }


        // Join Model::getSource() code to content
        $content .= sprintf($this->templateModelGetSource, $this->_options['table_name']);


        if (isset($this->_options['mapColumn'])) {
            $content .= $this->_genColumnMapCode($fields);
        }


        $code = sprintf(
            $this->templateClassFullStack,
            '',
            $this->_builderOptions['namespace'],
            $this->_builderOptions['className'],
            $extends,
            $content
        );
        file_put_contents($this->_builderOptions['path'], $code);

        print Color::success(
                'Model "' . $this->_builderOptions['className'] .
                '" was successfully created.'
            ) . PHP_EOL;
    }

    /**
     * Builds a PHP syntax with all the options in the array
     * @param array $options
     * @return string PHP syntax
     */
    private function _buildRelationOptions($options)
    {
        if (empty($options)) {
            return 'NULL';
        }

        $values = array();
        foreach ($options as $name => $val)
        {
            if (is_bool($val)) {
                $val = $val ? 'true':'false';
            }
            else if (!is_numeric($val)) {
                $val = '\''.$val.'\'';
            }

            $values[] = sprintf('\'%s\' => %s', $name, $val);
        }


        $syntax = 'array('. implode(',', $values). ')';

        return $syntax;
    }

    private function  _genColumnMapCode($fields)
    {
        $template = '
    /**
     * Independent Column Mapping.
     */
    public function columnMap() {
        return array(
            %s
        );
    }
';
        $contents = array();
        foreach ($fields as $field) {
            $name = $field->getName();
            $contents[] = sprintf('\'%s\' => \'%s\'', $name, $name);
        }

        return sprintf($template, join(", \n            ", $contents));
    }

}
