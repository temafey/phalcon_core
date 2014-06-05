<?php

namespace Engine\Builder;

use Engine\Builder\Traits\BasicTemplater as TBasicTemplater,
    Engine\Builder\Traits\SimpleGridTemplater as TSimpleGridTemplater,
    Engine\Builder\Traits\ExtJsGridTemplater as TExtJsGridTemplater,
    Engine\Tools\Inflector,
    Phalcon\Db\Column,
    Engine\Builder\Script\Color;

class Grid extends Component
{
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
                return false;
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
        $this->buildOptions($this->_options['table_name'], $config, Component::OPTION_GRID, $this->type);


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


        // Set $_title template
        $templateTitle = sprintf($this->templateSimpleGridTitle, $this->_builderOptions['className']);


        // Set container model template
        $templateContainerModel = sprintf($this->templateSimpleGridContainerModel, $this->getNameSpace($table, self::OPTION_MODEL)[1].'\\'.$this->_builderOptions['className']);


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

        $templateAction = '';
        if ($this->type !== self::TYPE_EXTJS) {
            $action = $this->_builderOptions['moduleName'].'/grid/'.Inflector::slug($nameSpace.'-'.$this->_builderOptions['className']);
            $templateAction = "
    protected \$_action = '/".$action."';
";
        }

        $initColumns = '';
        $initFilters = '';
        foreach ($fields as $field) {
            $type = $this->getType($field->getType());
            if (!$type) {
                continue;
            }
            $fieldName = $field->getName();
            if ($fieldName == 'id' || $field->isPrimary()) {
                $initColumns .= sprintf($this->templateShortGridColumn, $fieldName, 'Primary', Inflector::humanize($fieldName));
                $initFilters .= sprintf($this->templateShortGridFilterColumn, $fieldName, 'Primary', Inflector::humanize($fieldName));
            } elseif ($fieldName == 'title' || $fieldName == 'name') {
                $initColumns .= sprintf($this->templateShortGridColumn, $fieldName, 'Name', Inflector::humanize($fieldName));
                $initFilters .= sprintf($this->templateShortGridFilterColumn, $fieldName, 'Standart', Inflector::humanize($fieldName));
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
                $initColumns .= sprintf($this->templateSimpleGridComplexColumn, $fieldName, 'Collection', \Engine\Tools\Inflector::humanize($fieldName), $fieldName, $templateArray);
                $initFilters .= sprintf($this->templateSimpleGridComplexFilterColumn, $fieldName, 'ArrayToSelect', \Engine\Tools\Inflector::humanize($fieldName), $fieldName, $templateArray);
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

                    $initColumns .= sprintf($this->templateSimpleGridColumn, $fieldName, 'JoinOne', \Engine\Tools\Inflector::humanize($fieldName), $this->getNameSpace($table, self::OPTION_MODEL)[1].'\\'.$modelName);
                    $initFilters .= sprintf($this->templateSimpleGridFilterColumn, $fieldName, 'Join', \Engine\Tools\Inflector::humanize($fieldName), $this->getNameSpace($table, self::OPTION_MODEL)[1].'\\'.$modelName);
                } else {
                    $fieldComment = $fullFields[$fieldName]['COLUMN_COMMENT'];
                    $options = explode(";", $fieldComment);
                    if (count($options) < 2) {
                        $options = explode(",", $fieldComment);
                    }
                    $vals = [];
                    $colectionType = false;
                    if (count($options) > 1) {
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
                        $initColumns .= sprintf($this->templateSimpleGridComplexColumn, $fieldName, 'Collection', \Engine\Tools\Inflector::humanize($fieldName), $fieldName, $templateArray);
                        $initFilters .= sprintf($this->templateSimpleGridComplexFilterColumn, $fieldName, 'ArrayToSelect', \Engine\Tools\Inflector::humanize($fieldName), $fieldName, $templateArray);
                    } else {
                        $initColumns .= sprintf($this->templateSimpleGridColumn, $fieldName, $type, \Engine\Tools\Inflector::humanize($fieldName), $fieldName);
                        $initFilters .= sprintf($this->templateSimpleGridFilterColumn, $fieldName, 'Standart', \Engine\Tools\Inflector::humanize($fieldName),$fieldName);
                    }
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
            $this->_builderOptions['use'],
            $this->_builderOptions['head'],
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