<?php

namespace Engine\Builder;

use Engine\Builder\Traits\BasicTemplater as TBasicTemplater;
use Engine\Builder\Traits\SimpleFormTemplater as TSimpleFormTemplater;
use Engine\Builder\Traits\ExtJsFormTemplater as TExtJsFormTemplater;
use Engine\Tools\Inflector;
use Phalcon\Db\Column;
use Engine\Builder\Script\Color;

class Form extends Component {

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
        $config = $this->_getConfig('');


        // build options
        $this->buildOptions($this->_options['table_name'], $config, Component::OPTION_FORM, $this->type);


        // Prepare DB connection
        $this->prepareDbConnection($config);


        // Check if table exist in database
        $table = $this->_options['table_name'];
        if ($this->db->tableExists($table, $config->database->dbname)) {
            $fields = $this->db->describeColumns($table, $config->database->dbname);
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
        $nameSpace = $this->_builderOptions['namespaceClear'];
        $pieces = explode('\\', $nameSpace);
        array_shift($pieces);
        array_shift($pieces);
        $nameSpace = implode('-', $pieces);
        $action = '/'.$this->_builderOptions['moduleName'].'/form/'.Inflector::slug($nameSpace.'-'.$this->_builderOptions['className']);


        $initFields = '';
        foreach ($fields as $field) {
            $type = $this->getType($field->getType());

            if ($field->getName() == 'id') {
                $initFields .= sprintf($this->templateSimpleFormSimpleField, $field->getName(), 'Primary', Inflector::humanize($field->getName()), $field->getName());
            } elseif ($field->getName() == 'title' || $field->getName() == 'name') {
                $initFields .= sprintf($this->templateSimpleFormSimpleField, $field->getName(), 'Name', Inflector::humanize($field->getName()), $field->getName());
            } elseif ($this->isEnum($this->_options['table_name'], $field->getName())) {
                $templateArray = "[%s]";
                $templateArrayPair = "%s => '%s',";
                $enumVals = $this->getEnumValues($this->_options['table_name'], $field->getName());
                $enumValsContent = '';
                $i = 0;
                foreach ($enumVals as $enumVal) {
                    $enumValsContent .= sprintf($templateArrayPair, $i, $enumVal);
                        $i++;
                }
                $templateArray = sprintf($templateArray, $enumValsContent);
                $initFields .= sprintf($this->templateSimpleFormComplexField, $field->getName(), 'ArrayToSelect', Inflector::humanize($field->getName()), $field->getName(), $templateArray);
            } else {
                $initFields .= sprintf($this->templateSimpleFormSimpleField, $field->getName(), $type, Inflector::humanize($field->getName()), $field->getName());
            }
        }


        // Set init fields method
        $templateInitFields = sprintf($this->templateSimpleFormInitFields, $initFields);


        // Prepare class content
        $content = '';
        switch($this->type) {
            case self::TYPE_SIMPLE:
                $content .= sprintf($this->templateSimpleFormTitle, $this->_builderOptions['className']);
                $content .= sprintf($this->templateSimpleFormContainerModel, $this->getNameSpace($table, self::OPTION_MODEL).'\\'.$this->_builderOptions['className']);
                $content .= sprintf($this->templateSimpleFormAction, $action);
                $content .= $templateInitFields;
                break;
            case self::TYPE_EXTJS:
                $content .= sprintf($this->templateExtJsFormKey, Inflector::underscore($this->_builderOptions['className']));
                $content .= $this->templateExtJsFormModulePrefix;
                $content .= sprintf($this->templateExtJsFormModuleName, $this->_builderOptions['moduleName']);
                $content .= sprintf($this->templateSimpleFormTitle, $this->_builderOptions['className']);
                $content .= sprintf($this->templateSimpleFormContainerModel, $this->getNameSpace($table, self::OPTION_MODEL).'\\'.$this->_builderOptions['className']);
                $content .= sprintf($this->templateSimpleFormAction, $action);
                $content .= $templateInitFields;
                break;
            default:
                $content .= sprintf($this->templateSimpleFormTitle, $this->_builderOptions['className']);
                $content .= sprintf($this->templateSimpleFormContainerModel, $this->getNameSpace($table, self::OPTION_MODEL).'\\'.$this->_builderOptions['className']);
                $content .= sprintf($this->templateSimpleFormAction, $action);
                $content .= $templateInitFields;
                break;
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
                'Form "' . $this->_builderOptions['className'] .
                '" was successfully created.'
            ) . PHP_EOL;

    }

} 