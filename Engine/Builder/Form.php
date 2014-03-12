<?php
/**
 * Created by Slava Basko.
 * Email: basko.slava@gmail.com
 * Date: 2/25/14
 * Time: 3:58 PM
 */

namespace Engine\Builder;

use Engine\Builder\Traits\BasicTemplater as TBasicTemplater;
use Phalcon\Db\Column;
use Engine\Builder\Script\Color;

class Form extends Component {

    use TBasicTemplater;

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
    public function getType($type)
    {
        switch ($type) {
            case Column::TYPE_INTEGER:
                return 'Numeric';
                break;
            case Column::TYPE_DECIMAL:
            case Column::TYPE_FLOAT:
                return 'Text';
                break;
            case Column::TYPE_DATE:
            case Column::TYPE_VARCHAR:
            case Column::TYPE_DATETIME:
            case Column::TYPE_CHAR:
                return 'Text';
                break;
            case Column::TYPE_TEXT:
                return 'TextArea';
                break;
            default:
                return 'Text';
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
        $this->buildOptions($this->_options['table_name'], $config, Component::OPTION_FORM);


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


        // Set extender class template
        $extends = '\\Engine\\Crud\\Form';


        // Set $_title template
        $templateTitle = "
    protected \$_title = '{$this->_builderOptions['className']}';
";


        // Set container model template
        $templateContainerModel = "
    protected \$_containerModel = '".$this->getNameSpace($table, self::OPTION_MODEL).'\\'.$this->_builderOptions['className']."';
";


        // Set action template
        $nameSpace = $this->_builderOptions['namespaceClear'];
        $pieces = explode('\\', $nameSpace);
        array_shift($pieces);
        array_shift($pieces);
        $nameSpace = implode('-', $pieces);
        $action = $this->_builderOptions['moduleName'].'/form/'.\Engine\Tools\Inflector::slug($nameSpace.'-'.$this->_builderOptions['className']);
        $templateAction = "
    protected \$_action = '/".$action."';
";


        $templateInitFields = "
    protected function _initFields()
    {
        \$this->_fields = [
%s
        ];
    }
";


        $templateSimpleField = "\t\t\t'%s' => new \\Engine\\Crud\\Form\\Field\\%s('%s', '%s'),\n";


        $initFields = '';
        foreach ($fields as $field) {
            $type = $this->getType($field->getType());

            if ($field->getName() == 'id') {
                $initFields .= sprintf($templateSimpleField, $field->getName(), 'Primary', \Engine\Tools\Inflector::humanize($field->getName()), $field->getName());
            }elseif ($field->getName() == 'title' || $field->getName() == 'name') {
                $initFields .= sprintf($templateSimpleField, $field->getName(), 'Name', \Engine\Tools\Inflector::humanize($field->getName()), $field->getName());
            }else {
                $initFields .= sprintf($templateSimpleField, $field->getName(), $type, \Engine\Tools\Inflector::humanize($field->getName()), $field->getName());
            }
        }


        // Set init fields method
        $templateInitFields = sprintf($templateInitFields, $initFields);


        // Prepare class content
        $content = $templateTitle;
        $content .= $templateContainerModel;
        $content .= $templateAction;
        $content .= $templateInitFields;


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
                'Form "' . $this->_builderOptions['className'] .
                '" was successfully created.'
            ) . PHP_EOL;

    }

} 