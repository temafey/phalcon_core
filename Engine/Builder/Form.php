<?php

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
        $templateComplexField = "\t\t\t'%s' => new \\Engine\\Crud\\Form\\Field\\%s('%s', '%s', %s),\n";


        $initFields = '';
        foreach ($fields as $field) {
            $type = $this->getType($field->getType());

            if ($field->getName() == 'id') {
                $initFields .= sprintf($templateSimpleField, $field->getName(), 'Primary', \Engine\Tools\Inflector::humanize($field->getName()), $field->getName());
            } elseif ($field->getName() == 'title' || $field->getName() == 'name') {
                $initFields .= sprintf($templateSimpleField, $field->getName(), 'Name', \Engine\Tools\Inflector::humanize($field->getName()), $field->getName());
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
                $initFields .= sprintf($templateComplexField, $field->getName(), 'ArrayToSelect', \Engine\Tools\Inflector::humanize($field->getName()), $field->getName(), $templateArray);
            } else {
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