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

    /**
     * Mapa de datos escalares a objetos
     *
     * @var array
     */
    private $_typeMap = array(//'Date' => 'Date',
        //'Decimal' => 'Decimal'
    );

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


        // Check path
        $path = '';
        if (isset($this->_options['directory'])) {
            if ($this->_options['directory']) {
                $path = $this->_options['directory'] . '/';
            }
        }


        // Get config
        $config = $this->_getConfig($path);


        // Get module name
        $this->_options['module'] = $this->getModuleNameByTableName($this->_options['table_name']);


        // Get Model name
        $this->_options['name'] = $this->getModelName($this->_options['table_name']);


        // Check models folder
        if (!isset($this->_options['modelsDir'])) {

            // if specify in config --> get from config
            if (isset($config->builder->modules->{$this->_options['module']}->modelsDir)) {
                $modelsDir = $config->builder->modules->{$this->_options['module']}->modelsDir;

            // if dir not specify in config tru search folder for model
            }elseif (is_readable('../apps/'.$this->_options['module'].'/model')) {
                $modelsDir = '../apps/'.$this->_options['module'].'/model';
            }else {
                throw new BuilderException(
                    "Builder doesn't knows where is the models directory"
                );
            }
        } else {
            $modelsDir = $this->_options['modelsDir'];
        }


        /*if ($this->isAbsolutePath($modelsDir) == false) {
            $modelPath = $path . "public" . DIRECTORY_SEPARATOR . $modelsDir;
        } else {
            $modelPath = $modelsDir;
        }*/


        $methodRawCode = array();
        // Set model path
        $modelPath = $modelsDir.$this->_options['name'] . '.php';


        // If model already exist throw exception
        if (file_exists($modelPath)) {
            if (!$this->_options['force']) {
                throw new BuilderException(
                    "The model file '" . $this->_options['name'] .
                    ".php' already exists in models dir"
                );
            }
        }


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


        // Set namespace for model
        $namespace = 'namespace '.ucfirst($this->_options['module']).'\\Model;';


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


        $alreadyInitialized = false;
        if (file_exists($modelPath)) {
            try {
                $possibleMethods = array();

                require $modelPath;

                $linesCode = file($modelPath);
                $reflection = new \ReflectionClass($this->_options['className']);
                foreach ($reflection->getMethods() as $method) {
                    if ($method->getDeclaringClass()->getName() == $this->_options['className']) {
                        $methodName = $method->getName();
                        if (!isset($possibleMethods[$methodName])) {
                            $methodRawCode[$methodName] = join(
                                '',
                                array_slice(
                                    $linesCode,
                                    $method->getStartLine() - 1,
                                    $method->getEndLine() - $method->getStartLine() + 1
                                )
                            );
                        } else {
                            continue;
                        }
                        if ($methodName == 'initialize') {
                            $alreadyInitialized = true;
                        } else {
                            if ($methodName == 'validation') {
                                $alreadyValidations = true;
                            }
                        }
                    }
                }
            } catch (\ReflectionException $e) {
            }
        }


        // Set extender class
        $extends = '\\Engine\\Mvc\\Model';


        /**
         * Check if there have been any excluded fields
         */
        $exclude = array();
        if (isset($this->_options['excludeFields'])) {
            if (!empty($this->_options['excludeFields'])) {
                $keys = explode(',', $this->_options['excludeFields']);
                if (count($keys) > 0) {
                    foreach ($keys as $key) {
                        $exclude[trim($key)] = '';
                    }
                }
            }
        }


        $attributes = array();
        $belongsTo = [];
        foreach ($fields as $field) {
            $type = $this->getPHPType($field->getType());
            $attributes[] = sprintf(
                $this->templateEmptyAttribute, $type, 'public', $field->getName()
            );

            // Build belongsTo relations
            preg_match('/^(.*)\_i{1}d{1}$/', $field->getName(), $matches);
            if (!empty($matches)) {
                $belongsTo[] = sprintf($this->templateModelRelation, 'belongsTo', 'id', ucfirst($this->_options['module']).'\Model\\'.Inflector::modelize($matches[1]), $matches[0], $this->_buildRelationOptions([
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


        if ($alreadyInitialized == false) {
            if (count($initialize) > 0) {
                $initCode = sprintf(
                    $this->templateModelAttribute,
                    join('', $initialize)
                );
            } else {
                $initCode = "";
            }
        } else {
            $initCode = "";
        }


        $initializeCode = "";
        if (count($belongsTo) > 0) {
            foreach ($belongsTo as $rel) {
                $initializeCode .= $rel."\n";
            }
        }


        $license = '';
        if (file_exists('license.txt')) {
            $license = file_get_contents('license.txt');
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
        $content .= $initCode;
        if (!empty($initializeCode)) {
            $content .= sprintf($this->templateInitialize, $initializeCode);
        }


        // Join Model::getSource() code to content
        $content .= sprintf($this->templateModelGetSource, $this->_options['table_name']);


        foreach ($methodRawCode as $methodCode) {
            $content .= $methodCode;
        }


        if (isset($this->_options['mapColumn'])) {
            $content .= $this->_genColumnMapCode($fields);
        }


        $code = sprintf(
            $this->templateClassFullStack,
            $license,
            $namespace,
            $this->_options['name'],
            $extends,
            $content
        );
        file_put_contents($modelPath, $code);

        print Color::success(
                'Model "' . $this->_options['name'] .
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
