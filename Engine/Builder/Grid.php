<?php
/**
 * Created by Slava Basko.
 * Email: basko.slava@gmail.com
 * Date: 2/25/14
 * Time: 3:58 PM
 */

namespace Engine\Builder;

use Engine\Builder\Traits\BasicTemplater as TBasicTemplater;
use Engine\Crud\Grid\Column\JoinOne;
use Phalcon\Db\Column;
use Engine\Builder\Script\Color;

class Grid extends Component {

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
        $this->buildOptions($this->_options['table_name'], $config, Component::OPTION_GRID);


        // Prepare DB connection
        $this->prepareDbConnection($config);


        // Check if table exist in database
        $table = $this->_options['table_name'];
        if ($this->db->tableExists($table, $config->database->dbname)) {
            $fields = $this->db->describeColumns($table, $config->database->dbname);
        } else {
            throw new BuilderException('Table "' . $table . '" does not exists');
        }


        $templateFileCode = '<?php
%s

use Engine\Crud\Grid\AbstractGrid as Grid,
    Engine\Crud\Grid\Column,
    Engine\Crud\Grid\Filter,
    Engine\Crud\Grid\Filter\Field,
    Engine\Filter\SearchFilterInterface as Criteria;

class %s extends %s
{
%s
}
';


        // Set $_title template
        $templateTitle = "
    protected \$_title = '{$this->_builderOptions['className']}';
";


        // Set container model template
        $templateContainerModel = "
    protected \$_containerModel = '".$this->getNameSpace($table, self::OPTION_MODEL).'\\'.$this->_builderOptions['className']."';
";


        // Set extender class template
        $extends = '\\Engine\\Crud\\Grid';


        $templateInitColumns = "
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

        $templateInitFilters = "
    /**
	 * Initialize grid filters
	 *
	 * @return void
	*/
	protected function _initFilters()
	{
		\$this->_filter = new Filter([
		    'search' => new Field\\Search('search','Search:', [
                Criteria::COLUMN_ID => Criteria::CRITERIA_EQ,
                Criteria::COLUMN_NAME => Criteria::CRITERIA_BEGINS,
                'command' => Criteria::CRITERIA_LIKE
			]),
%s
		 ], null, 'get');
    }
";


        $templateColumn = "\t\t\t'%s' => new Column\\%s('%s', '%s'),\n";
        $templateComplexColumn = "\t\t\t'%s' => new Column\\%s('%s', '%s', %s),\n";
        $templateFilterColumn = "\t\t\t'%s' => new Field\\%s('%s', '%s'),\n";
        $templateConplexFilterColumn = "\t\t\t'%s' => new Field\\%s('%s', '%s', %s),\n";


        // Set action template
        $nameSpace = $this->_builderOptions['namespaceClear'];
        $pieces = explode('\\', $nameSpace);
        array_shift($pieces);
        array_shift($pieces);
        $nameSpace = implode('-', $pieces);
        $action = $this->_builderOptions['moduleName'].'/grid/'.\Engine\Tools\Inflector::slug($nameSpace.'-'.$this->_builderOptions['className']);
        $templateAction = "
    protected \$_action = '/".$action."';
";


        $initColumns = '';
        $initFilters = '';
        foreach ($fields as $field) {
            $type = $this->getType($field->getType());

            if ($field->getName() == 'id') {
                $initColumns .= sprintf($templateColumn, $field->getName(), 'Primary', \Engine\Tools\Inflector::humanize($field->getName()), $field->getName());
                $initFilters .= sprintf($templateFilterColumn, $field->getName(), 'Primary', \Engine\Tools\Inflector::humanize($field->getName()), $field->getName());
            } elseif ($field->getName() == 'title' || $field->getName() == 'name') {
                $initColumns .= sprintf($templateColumn, $field->getName(), 'Name', \Engine\Tools\Inflector::humanize($field->getName()), $field->getName());
                $initFilters .= sprintf($templateFilterColumn, $field->getName(), 'Standart', \Engine\Tools\Inflector::humanize($field->getName()), $field->getName());
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
                $initColumns .= sprintf($templateComplexColumn, $field->getName(), 'Collection', \Engine\Tools\Inflector::humanize($field->getName()), $field->getName(), $templateArray);
                $initFilters .= sprintf($templateConplexFilterColumn, $field->getName(), 'ArrayToSelect', \Engine\Tools\Inflector::humanize($field->getName()), $field->getName(), $templateArray);
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

                    $initColumns .= sprintf($templateColumn, $field->getName(), 'JoinOne', \Engine\Tools\Inflector::humanize(implode('_', $pieces)), $this->getNameSpace($table, self::OPTION_MODEL).'\\'.$modelName);
                    $initFilters .= sprintf($templateFilterColumn, $field->getName(), 'Join', \Engine\Tools\Inflector::humanize(implode('_', $pieces)), $this->getNameSpace($table, self::OPTION_MODEL).'\\'.$modelName);
                } else {
                    $initColumns .= sprintf($templateColumn, $field->getName(), $type, \Engine\Tools\Inflector::humanize($field->getName()), $field->getName());
                    $initFilters .= sprintf($templateFilterColumn, $field->getName(), 'Standart', \Engine\Tools\Inflector::humanize($field->getName()), $field->getName());
                }
            }
        }


        // Set init fields method
        $templateInitColumns = sprintf($templateInitColumns, $initColumns);
        $templateInitFilters = sprintf($templateInitFilters, $initFilters);


        // Prepare class content
        $content = $templateTitle;
        $content .= $templateContainerModel;
        $content .= $templateAction;
        $content .= $templateInitColumns;
        $content .= $templateInitFilters;


        $code = sprintf(
            $templateFileCode,
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