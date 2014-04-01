<?php

namespace Engine\Builder;

use Engine\Builder\Traits\BasicTemplater as TBasicTemplater;
use Engine\Builder\Traits\SimpleGridTemplater as TSimpleGridTemplater;
use Engine\Builder\Traits\ExtJsGridTemplater as TExtJsGridTemplater;
use Engine\Tools\Inflector;
use Phalcon\Db\Column;
use Engine\Builder\Script\Color;

class Grid extends Component {

    use TBasicTemplater, TSimpleGridTemplater, TExtJsGridTemplater;

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
        $this->buildOptions($this->_options['table_name'], $config, Component::OPTION_GRID, $this->type);


        // Prepare DB connection
        $this->prepareDbConnection($config);


        // Check if table exist in database
        $table = $this->_options['table_name'];
        if ($this->db->tableExists($table, $config->database->dbname)) {
            $fields = $this->db->describeColumns($table, $config->database->dbname);
        } else {
            throw new BuilderException('Table "' . $table . '" does not exists');
        }


        // Set $_title template
        $templateTitle = sprintf($this->templateSimpleGridTitle, $this->_builderOptions['className']);


        // Set container model template
        $templateContainerModel = sprintf($this->templateSimpleGridContainerModel, $this->getNameSpace($table, self::OPTION_MODEL).'\\'.$this->_builderOptions['className']);


        // Set extender class template
        switch($this->type) {
            case self::TYPE_SIMPLE: $extends = $this->templateSimpleGridExtends;
                break;
            case self::TYPE_EXTJS: $extends = $this->templateExtJsGridExtends;
                break;
            default: $extends = $this->templateSimpleGridExtends;
            break;
        }


        $templateInitColumns = $this->templateSimpleGridInitColumns;


        $templateInitFilters = $this->templateSimpleGridInitFilters;


        // Set action template
        $nameSpace = $this->_builderOptions['namespaceClear'];
        $pieces = explode('\\', $nameSpace);
        array_shift($pieces);
        array_shift($pieces);
        $nameSpace = implode('-', $pieces);
        $action = $this->_builderOptions['moduleName'].'/grid/'.Inflector::slug($nameSpace.'-'.$this->_builderOptions['className']);
        $templateAction = "
    protected \$_action = '/".$action."';
";


        $initColumns = '';
        $initFilters = '';
        foreach ($fields as $field) {
            $type = $this->getType($field->getType());

            if ($field->getName() == 'id') {
                $initColumns .= sprintf($this->templateSimpleGridColumn, $field->getName(), 'Primary', Inflector::humanize($field->getName()), $field->getName());
                $initFilters .= sprintf($this->templateSimpleGridFilterColumn, $field->getName(), 'Primary', Inflector::humanize($field->getName()), $field->getName());
            } elseif ($field->getName() == 'title' || $field->getName() == 'name') {
                $initColumns .= sprintf($this->templateSimpleGridColumn, $field->getName(), 'Name', Inflector::humanize($field->getName()), $field->getName());
                $initFilters .= sprintf($this->templateSimpleGridFilterColumn, $field->getName(), 'Standart', Inflector::humanize($field->getName()), $field->getName());
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
                $initColumns .= sprintf($this->templateSimpleGridComplexColumn, $field->getName(), 'Collection', \Engine\Tools\Inflector::humanize($field->getName()), $field->getName(), $templateArray);
                $initFilters .= sprintf($this->templateSimpleGridComplexFilterColumn, $field->getName(), 'ArrayToSelect', \Engine\Tools\Inflector::humanize($field->getName()), $field->getName(), $templateArray);
            } else {
                preg_match('/^(.*)\_i{1}d{1}$/', $field->getName(), $matches);
                if (!empty($matches)) {
                    $pieces = explode('_', $field->getName());
                    array_shift($pieces);
                    array_pop($pieces);

                    $camelize = function($pieces) {
                        $c = array();
                        foreach ($pieces as $piece) {
                            $c[] = ucfirst($piece);
                        }

                        return $c;
                    };
                    $modelName = implode('\\', $camelize($pieces));

                    $initColumns .= sprintf($this->templateSimpleGridColumn, $field->getName(), 'JoinOne', \Engine\Tools\Inflector::humanize(implode('_', $pieces)), $this->getNameSpace($table, self::OPTION_MODEL).'\\'.$modelName);
                    $initFilters .= sprintf($this->templateSimpleGridFilterColumn, $field->getName(), 'Join', \Engine\Tools\Inflector::humanize(implode('_', $pieces)), $this->getNameSpace($table, self::OPTION_MODEL).'\\'.$modelName);
                } else {
                    $initColumns .= sprintf($this->templateSimpleGridColumn, $field->getName(), $type, \Engine\Tools\Inflector::humanize($field->getName()), $field->getName());
                    $initFilters .= sprintf($this->templateSimpleGridFilterColumn, $field->getName(), 'Standart', \Engine\Tools\Inflector::humanize($field->getName()), $field->getName());
                }
            }
        }


        // Set init fields method
        $templateInitColumns = sprintf($templateInitColumns, $initColumns);
        $templateInitFilters = sprintf($templateInitFilters, $initFilters);


        // Prepare class content
        $content = '';
        switch($this->type) {
            case self::TYPE_SIMPLE:
                $content .= $templateTitle;
                $content .= $templateContainerModel;
                $content .= $templateAction;
                $content .= $templateInitColumns;
                $content .= $templateInitFilters;
                break;
            case self::TYPE_EXTJS:
                $content .= sprintf($this->templateExtJsGridKey, Inflector::underscore($this->_builderOptions['className']));
                $content .= $this->templateExtJsGridModulePrefix;
                $content .= sprintf($this->templateExtJsGridModuleName, $this->_builderOptions['moduleName']);
                $content .= $templateTitle;
                $content .= $templateContainerModel;
                $content .= $templateAction;
                $content .= $templateInitColumns;
                $content .= $templateInitFilters;
                break;
            default:
                $content .= $templateTitle;
                $content .= $templateContainerModel;
                $content .= $templateAction;
                $content .= $templateInitColumns;
                $content .= $templateInitFilters;
            break;
        }


        $code = sprintf(
            $this->templateSimpleGridFileCode,
            $this->_builderOptions['namespace'],
            $this->_builderOptions['className'],
            $extends,
            $content
        );
        file_put_contents($this->_builderOptions['path'], $code);

        print Color::success(
                'Grid "' . $this->_builderOptions['className'] .
                '" was successfully created.'
            ) . PHP_EOL;

    }

} 