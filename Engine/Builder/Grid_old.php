<?php
/**
 * @namespace
 */
namespace Engine\Builder;

use Phalcon\Db\Column,
    Engine\Builder\Component,
    Engine\Builder\BuilderException,
    Engine\Builder\Script\Color,
    Phalcon\Text as Utils;

/**
 * GridBuilderComponent
 *
 * Builder to generate grids
 *
 * @category    Engine
 * @package     Builder
 * @subpackage  Grid
 * @copyright   Copyright (c) 2011-2013 Phalcon Team (temafey@gmail.com)
 * @license     New BSD License
 */
class GridOld extends Component
{
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
        if (!isset($options['name'])) {
            throw new BuilderException("Please, specify the grid name");
        }
        if (!isset($options['force'])) {
            $options['force'] = false;
        }
        if (!isset($options['className'])) {
            $options['className'] = Utils::camelize($options['name']);
        }
        if (!isset($options['fileName'])) {
            $options['fileName'] = $options['name'];
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

    /**
     * Returns the associated column type
     *
     * @param string $type
     * @return string
     */
    public function getColumnType($type)
    {
        switch ($type) {
            case Column::TYPE_INTEGER:
            case Column::TYPE_DECIMAL:
            case Column::TYPE_FLOAT:
                return 'Numeric';
                break;
            case Column::TYPE_DATE:
            case Column::TYPE_DATETIME:
                return 'Date';
                break;
            case Column::TYPE_VARCHAR:
            case Column::TYPE_CHAR:
            case Column::TYPE_TEXT:
                return 'Text';
            break;
            default:
                return 'Text';
                break;
        }
    }

    /**
     * Returns the associated filter column type
     *
     * @param string $type
     * @return string
     */
    public function getFilterColumnType($type)
    {
        switch ($type) {
            case Column::TYPE_INTEGER:
            case Column::TYPE_DECIMAL:
            case Column::TYPE_FLOAT:
                return 'Numeric';
                break;
            case Column::TYPE_DATE:
            case Column::TYPE_DATETIME:
                return 'Date';
                break;
            case Column::TYPE_VARCHAR:
            case Column::TYPE_CHAR:
            case Column::TYPE_TEXT:
                return 'Standart';
                break;
            default:
                return 'Standart';
                break;
        }
    }

    public function build()
    {
        $initColumns = "
    /**
	 * Initialize grid columns
	 *
	 * @return void
	 */
	protected function _initColumns()
    {
		\$this->_columns = [
		    %s
		 ];
    }
";
        $initFilters = "
    /**
	 * Initialize grid filters
	 *
	 * @return void
	*/
	protected function _initFilters()
	{
		\$this->_filter = new Filter([
		    %s
		 ], null, 'get');
    }
";

        $templateThis = "\t\t\$this->%s(%s);\n";
        $templateColumn = "\t\t\t'%s' => new Column\\%s('%s', '%s')\n";
        $templateFilterColumn = "\t\t\t'%s' => new Field\\%s('%s', '%s')\n";

        $templateAttributes = "
    /**
     * %s
     * @var %s
     */
    %s \$%s;
     ";

        $templateCode = "<?php
%s

use Engine\Crud\Grid\AbstractGrid as Grid,
    Engine\Crud\Grid\Column,
    Engine\Crud\Grid\Filter,
    Engine\Crud\Grid\Filter\Field,
    Engine\Filter\SearchFilterInterface as Criteria;

%s
class %s extends %s
{
%s
}
";

        if (!$this->_options['name']) {
            throw new BuilderException("You must specify the table name");
        }

        $path = '';
        if (isset($this->_options['directory'])) {
            if ($this->_options['directory']) {
                $path = $this->_options['directory'] . '/';
            }
        }

        $config = $this->_getConfig($path);

        if (!isset($this->_options['gridsDir'])) {
            if (!isset($config->application->gridsDir)) {
                throw new BuilderException(
                    "Builder doesn't knows where is the grids directory"
                );
            }
            $gridsDir = $config->application->gridsDir;
        } else {
            $gridsDir = $this->_options['gridsDir'];
        }

        if ($this->isAbsolutePath($gridsDir) == false) {
            $gridPath = $path . "public" . DIRECTORY_SEPARATOR . $gridsDir;
        } else {
            $gridPath = $gridsDir;
        }

        $methodRawCode = [];
        if (isset($this->_options['className'])) {
            $className = $this->_options['className'];
        } else {
            $tmpGrid = explode("_", $this->_options['name']);
            $model = [];
            $first = false;
            foreach ($tmpGrid as $string) {
                $model[] = ucfirst($string);
                if ($first === false) {
                    $model[] = 'Grid';
                    $first = true;
                }
            }
            $className = "\\".implode("\\", $model);
        }
        $gridPath .= $className . '.php';

        if (file_exists($gridPath)) {
            if (!$this->_options['force']) {
                throw new BuilderException(
                    "The grid file '" . $className .
                    ".php' already exists in grids dir"
                );
            }
        }

        if (!isset($config->database)) {
            throw new BuilderException(
                "Database configuration cannot be loaded from your config file"
            );
        }

        if (!isset($config->database->adapter)) {
            throw new BuilderException(
                "Adapter was not found in the config. " .
                "Please specify a config variable [database][adapter]"
            );
        }

        if (isset($this->_options['module'])) {
            $namespace = 'namespace ' . $this->_options['module'] . '\Grid;'
                . PHP_EOL . PHP_EOL;
        } else {
            $namespace = '';
        }

        $adapter = $config->database->adapter;
        $this->isSupportedAdapter($adapter);

        if (isset($config->database->adapter)) {
            $adapter = $config->database->adapter;
        } else {
            $adapter = 'Mysql';
        }

        if (is_object($config->database)) {
            $configArray = $config->database->toArray();
        } else {
            $configArray = $config->database;
        }

        $adapterName = 'Phalcon\Db\Adapter\Pdo\\' . $adapter;
        unset($configArray['adapter']);
        $db = new $adapterName($configArray);

        $initialize = [];
        if (isset($this->_options['schema'])) {
            $schema = $this->_options['schema'];
        } elseif ($adapter == 'Postgresql') {
            $schema = 'public';
        } else {
            $schema = $config->database->dbname;
        }

        $table = $this->_options['name'];
        if ($db->tableExists($table, $schema)) {
            $fields = $db->describeColumns($table, $schema);
        } else {
            throw new BuilderException('Table "' . $table . '" does not exists');
        }

        if (isset($this->_options['hasMany'])) {
            if (count($this->_options['hasMany'])) {
                foreach ($this->_options['hasMany'] as $relation) {
                    if (is_string($relation['fields'])) {
                        $entityName = $relation['camelizedName'];
                        $initialize[] = sprintf(
                            $templateColumnJoinMany,
                            'hasMany',
                            $relation['fields'],
                            $entityName,
                            $relation['relationFields'],
                            $this->_buildRelationOptions( isset($relation['options']) ? $relation["options"] : NULL)
                        );
                    }
                }
            }
        }

        if (isset($this->_options['belongsTo'])) {
            if (count($this->_options['belongsTo'])) {
                foreach ($this->_options['belongsTo'] as $relation) {
                    if (is_string($relation['fields'])) {
                        $entityName = $relation['referencedgrid'];
                        $initialize[] = sprintf(
                            $templateColumnJoinOne,
                            'belongsTo',
                            $relation['fields'],
                            $entityName,
                            $relation['relationFields'],
                            $this->_buildRelationOptions(isset($relation['options']) ? $relation["options"] : NULL)
                        );
                    }
                }
            }
        }

        $alreadyInitialized = false;
        if (file_exists($gridPath)) {
            try {
                $possibleMethods = [];
                foreach ($fields as $field) {
                    $methodName = Utils::camelize($field->getName());
                    $possibleMethods['set' . $methodName] = true;
                    $possibleMethods['get' . $methodName] = true;
                }
                require $gridPath;

                $linesCode = file($gridPath);
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
                        if ($methodName == 'initColumns') {
                            $alreadyInitColumns = true;
                        } else {
                            if ($methodName == 'initFilters') {
                                $alreadyInitFilters = true;
                            }
                        }
                    }
                }
            } catch (\ReflectionException $e) {
            }
        }

        foreach ($fields as $field) {
            if ($field->getType() === Column::TYPE_CHAR) {
                $domain = [];
                if (preg_match('/\((.*)\)/', $field->getType(), $matches)) {
                    foreach (explode(',', $matches[1]) as $item) {
                        $domain[] = $item;
                    }
                }
            }
            if ($field->getName() == 'email') {
                $columns[] = sprintf(
                    $templateColumnEmail, $field->getName()
                );
                $filters[] = sprintf(
                    $templateFilterEmail, $field->getName()
                );
            }
            if ($field->getName() == 'date' || $field->getName() == 'published') {
                $columns[] = sprintf(
                    $templateColumnDate, $field->getName()
                );
                $filters[] = sprintf(
                    $templateFilterDate, $field->getName()
                );
            }
        }

        /**
         * Check if there has been an extender class
         */
        $extends = '\\Engine\\Crud\\Grid';
        if (isset($this->_options['extends'])) {
            if (!empty($this->_options['extends'])) {
                $extends = $this->_options['extends'];
            }
        }

        $attributes = [];
        $columns = [];
        $filters = [];

        $tmpTitle = explode("_", $table);
        $title = [];
        foreach ($tmpTitle as $string) {
            $title[] = ucfirst($string);
        }
        $title = implode(" ", $title);
        $attributes[] = sprintf(
            $templateAttributes, 'Grid title', 'string', 'protected', '_title', $title
        );

        $tmpModel = explode("_", $table);
        $model = [];
        $first = false;
        foreach ($tmpModel as $string) {
            $model[] = ucfirst($string);
            if ($first === false) {
                $model[] = 'Model';
                $first = true;
            }
        }
        $model = "\\".implode("\\", $model);
        $attributes[] = sprintf(
            $templateAttributes, 'Container model', 'string', 'protected', '_containerModel', $model
        );
        $attributes[] = sprintf(
            $templateAttributes, 'Container condition', 'string|array', 'protected', '_containerConditions', 'null'
        );

        foreach ($fields as $field) {
            $fieldName = $field->getName();
            $fieldCamelName = Utils::camelize($fieldName);
            $tmpTitle = explode("_", $fieldName);
            $fieldTitle = [];
            foreach ($tmpTitle as $string) {
                $fieldTitle[] = ucfirst($string);
            }
            $fieldTitle = implode(" ", $fieldTitle);
            $columnType = $this->getColumnType($field->getType());
            $columns[] = sprintf(
                $templateColumn,
                $fieldCamelName,
                $columnType,
                $title,
                $fieldName
            );

            $filterType = $this->getFilterColumnType($field->getType());
            $filters[] = sprintf(
                $templateFilterColumn,
                $fieldCamelName,
                $columnType,
                $title,
                $fieldName
            );
        }

        $license = '';
        if (file_exists('license.txt')) {
            $license = file_get_contents('license.txt');
        }

        $content = join('', $attributes);
        $columns = sprintf($initColumns, join(",\n", $columns));
        $filters = sprintf($initFilters, join(",\n", $filters));
        $content .= join('', $columns)
            . join('', $filters);


        foreach ($methodRawCode as $methodCode) {
            $content .= $methodCode;
        }

        $code = sprintf(
            $templateCode,
            $license,
            $namespace,
            $className,
            $extends,
            $content
        );
        file_put_contents($gridPath, $code);

        print Color::success(
                'grid "' . $this->_options['name'] .
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
        foreach ($options as $name=>$val)
        {
            if (is_bool($val)) {
                $val = $val ? 'true':'false';
            }
            else if (!is_numeric($val)) {
                $val = '"$val"';
            }

            $values[] = sprintf('"%s"=>%s', $name, $val);
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
