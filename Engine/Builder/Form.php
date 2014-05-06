<?php

namespace Engine\Builder;

use Engine\Builder\Traits\BasicTemplater as TBasicTemplater,
    Engine\Builder\Traits\SimpleFormTemplater as TSimpleFormTemplater,
    Engine\Builder\Traits\ExtJsFormTemplater as TExtJsFormTemplater,
    Engine\Tools\Inflector,
    Phalcon\Db\Column,
    Engine\Builder\Script\Color;

class Form extends Component
{
    use TBasicTemplater, TSimpleFormTemplater, TExtJsFormTemplater;

    protected $type = self::TYPE_SIMPLE;

    /**
     * Constructor
     *
     * @param $options
     * @throws BuilderException
     */
    public function __construct($options)
    {
        if (!isset($options['table_name']) || empty($options['table_name'])) {
            throw new BuilderException("Please, specify the model name");
        }
        $this->_options = $options;
    }

    /**
     * Setup builder type
     *
     * @param int $type
     * @return $this
     */
    public function setType($type = self::TYPE_SIMPLE)
    {
        switch($type) {
            case self::TYPE_SIMPLE: $this->type = self::TYPE_SIMPLE;
                break;
            default: $this->type = self::TYPE_SIMPLE;
                break;
        }
        return $this;
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
        $path = '';
        if (isset($this->_options['config_path'])) {
            $path = $this->_options['config_path'];
        } elseif (isset($this->_options['app_path']))  {
            $path = $this->_options['app_path'];
        } elseif (isset($this->_options['module_path']))  {
            $path = $this->_options['module_path'];
        }
        $config = $this->_getConfig($path);


        // build options
        $this->buildOptions($this->_options['table_name'], $config, Component::OPTION_FORM, $this->type);


        // Prepare DB connection
        if (!$this->prepareDbConnection($config)) {
            return false;
        }


        // Check if table exist in database
        $table = $this->_options['table_name'];
        if ($this->db->tableExists($table, $config->database->dbname)) {
            $fields = $this->db->describeColumns($table, $config->database->dbname);
            $rows = $this->db->fetchAll("SELECT * FROM `information_schema`.`columns` WHERE `table_schema` = '".$config->database->dbname."' and `table_name` = '".$table."'");
            $fullFields = [];
            foreach ($rows as $row) {
                $fullFields[$row['COLUMN_NAME']] = $row;
            }
        } else {
            throw new BuilderException('Table "' . $table . '" does not exists');
        }

        // Set extender class template
        switch($this->type) {
            case self::TYPE_SIMPLE: $extends = $this->templateSimpleFormExtends;
                break;
            case self::TYPE_EXTJS: $extends = $this->templateExtJsFormExtends;
                break;
            default: $extends = $this->templateSimpleFormExtends;
            break;
        }


        // Set action template
        if ($this->type !== self::TYPE_EXTJS) {
            $nameSpace = $this->_builderOptions['namespaceClear'];
            $pieces = explode('\\', $nameSpace);
            array_shift($pieces);
            array_shift($pieces);
            $nameSpace = implode('-', $pieces);
            $action = '/'.$this->_builderOptions['moduleName'].'/form/'.Inflector::slug($nameSpace.'-'.$this->_builderOptions['className']);
        }


        $initFields = '';
        foreach ($fields as $field) {
            $type = $this->getType($field->getType());
            $fieldName = $field->getName();
            if ($fieldName == 'id' || $field->isPrimary()) {
                $initFields .= sprintf($this->templateShortFormSimpleField, $fieldName, 'Primary', Inflector::humanize($fieldName));
            } elseif ($fieldName == 'title' || $fieldName == 'name') {
                $initFields .= sprintf($this->templateShortFormSimpleField, $fieldName, 'Name', Inflector::humanize($fieldName));
            } elseif ($this->isEnum($this->_options['table_name'], $fieldName)) {
                $templateArray = "[%s]";
                $templateArrayPair = "%s => '%s',";
                $enumVals = $this->getEnumValues($this->_options['table_name'], $fieldName);
                $enumValsContent = '';
                $i = 0;
                foreach ($enumVals as $enumVal) {
                    $enumValsContent .= sprintf($templateArrayPair, $i, $enumVal);
                        $i++;
                }
                $templateArray = sprintf($templateArray, $enumValsContent);
                $initFields .= sprintf($this->templateSimpleFormComplexField, $fieldName, 'ArrayToSelect', Inflector::humanize($fieldName), $fieldName, $templateArray);
            } else {
                preg_match('/^(.*)\_i{1}d{1}$/', $fieldName, $matches);
                if (!empty($matches)) {
                    $pieces = explode('_', $fieldName);
                    if (count($pieces) > 2) {
                        array_shift($pieces);
                    }
                    array_pop($pieces);

                    $camelize = function($pieces) {
                        $c = array();
                        foreach ($pieces as $piece) {
                            $c[] = ucfirst($piece);
                        }

                        return $c;
                    };
                    $modelName = implode('\\', $camelize($pieces));
                    $fieldName = implode('_', $pieces);
                    $initFields .= sprintf($this->templateSimpleFormSimpleField, $fieldName, 'ManyToOne', Inflector::humanize($fieldName), $this->getNameSpace($table, self::OPTION_MODEL)[1].'\\'.$modelName);
                  } else {
                    $fieldComment = $fullFields[$fieldName]['COLUMN_COMMENT'];
                    $options = explode(";", $fieldComment);
                    if (count($options) < 2) {
                        $options = explode(",", $fieldComment);
                    }
                    $vals = [];
                    $colectionType = false;
                    if (count($rows) > 1) {
                        foreach ($options as $option) {
                            if (strpos($option, ":") === false) {
                                $colectionType = false;
                                break;
                            }
                            list($key, $value) = explode(":", $option);
                            $vals[$key] = $value;
                            $colectionType = true;
                        }
                    }
                    if ($colectionType) {
                        $templateArray = "[%s]";
                        $templateArrayPair = "'%s' => '%s'";
                        $valsContent = [];
                        foreach ($vals as $key => $value) {
                            $valsContent[] = sprintf($templateArrayPair, $key, $value);
                        }
                        $templateArray = sprintf($templateArray, implode(", ", $valsContent));
                        $initFields .= sprintf($this->templateSimpleFormComplexField, $fieldName, 'ArrayToSelect', Inflector::humanize($fieldName), $fieldName, $templateArray);
                    } else {
                        $initFields .= sprintf($this->templateSimpleFormSimpleField, $fieldName, $type, Inflector::humanize($fieldName), $fieldName);
                    }
                }
            }
        }


        // Set init fields method
        $templateInitFields = sprintf($this->templateSimpleFormInitFields, $initFields);


        // Prepare class content
        $content = '';
        switch($this->type) {
            case self::TYPE_SIMPLE:
                $content .= sprintf($this->templateSimpleFormTitle, $this->_builderOptions['className']);
                $content .= sprintf($this->templateSimpleFormContainerModel, $this->getNameSpace($table, self::OPTION_MODEL)[1].'\\'.$this->_builderOptions['className']);
                $content .= sprintf($this->templateSimpleFormAction, $action);
                $content .= $templateInitFields;
                break;
            case self::TYPE_EXTJS:
                $content .= sprintf($this->templateExtJsFormKey, Inflector::underscore($this->_builderOptions['className']));
                $content .= $this->templateExtJsFormModulePrefix;
                $content .= sprintf($this->templateExtJsFormModuleName, $this->_builderOptions['moduleName']);
                $content .= sprintf($this->templateSimpleFormTitle, $this->_builderOptions['className']);
                $content .= sprintf($this->templateSimpleFormContainerModel, $this->getNameSpace($table, self::OPTION_MODEL)[1].'\\'.$this->_builderOptions['className']);
                $content .= $templateInitFields;
                break;
            default:
                $content .= sprintf($this->templateSimpleFormTitle, $this->_builderOptions['className']);
                $content .= sprintf($this->templateSimpleFormContainerModel, $this->getNameSpace($table, self::OPTION_MODEL)[1].'\\'.$this->_builderOptions['className']);
                $content .= sprintf($this->templateSimpleFormAction, $action);
                $content .= $templateInitFields;
                break;
        }


        $code = sprintf(
            $this->templateClassFullStack,
            '',
            $this->_builderOptions['namespace'],
            $this->_builderOptions['use'],
            $this->_builderOptions['head'],
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