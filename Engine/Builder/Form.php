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
        if (!isset($this->_options['formsDir'])) {

            // if specify in config --> get from config
            if (isset($config->builder->modules->{$this->_options['module']}->formsDir)) {
                $formsDir = $config->builder->modules->{$this->_options['module']}->formsDir;

                // if dir not specify in config tru search folder for model
            }elseif (is_readable('../apps/'.$this->_options['module'].'/form')) {
                $formsDir = '../apps/'.$this->_options['module'].'/form';
            }else {
                throw new BuilderException(
                    "Builder doesn't knows where is the forms directory"
                );
            }
        } else {
            $formsDir = $this->_options['formsDir'];
        }


        $methodRawCode = array();
        // Set model path
        $formPath = $formsDir.$this->_options['name'] . '.php';

        // If model already exist throw exception
        if (file_exists($formPath)) {
            if (isset($this->_options['force']) && !$this->_options['force']) {
                throw new BuilderException(
                    "The form file '" . $this->_options['name'] .
                    ".php' already exists in forms dir"
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
        $namespace = 'namespace '.ucfirst($this->_options['module']).'\\Form;';


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
    protected \$_title = '{$this->_options['name']}';
";


        // Set container model template
        $templateContainerModel = "
    protected \$_containerModel = '".ucfirst($this->_options['module']).'\\Model\\'.$this->_options['name']."';
";


        // Set action template
        $templateAction = "
    protected \$_action = '/".$this->_options['module']."/form/".\Engine\Tools\Inflector::slug(\Engine\Tools\Inflector::humanize(\Engine\Tools\Inflector::underscore($this->_options['name'])))."';
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


        // Check license
        $license = '';
        if (file_exists('license.txt')) {
            $license = file_get_contents('license.txt');
        }


        // Prepare class content
        $content = $templateTitle;
        $content .= $templateContainerModel;
        $content .= $templateAction;
        $content .= $templateInitFields;


        $code = sprintf(
            $this->templateClassFullStack,
            $license,
            $namespace,
            $this->_options['name'],
            $extends,
            $content
        );
        file_put_contents($formPath, $code);

        print Color::success(
                'Form "' . $this->_options['name'] .
                '" was successfully created.'
            ) . PHP_EOL;

    }

} 