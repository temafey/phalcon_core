<?php
/**
 * Created by Slava Basko.
 * Email: basko.slava@gmail.com
 * Date: 3/14/14
 * Time: 6:33 PM
 */

namespace Engine\Builder\Traits;


trait SimpleGridTemplater {

    public $templateSimpleGridFileCode = '<?php
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
    public $templateSimpleGridTitle = "
    protected \$_title = '%s';
";

    public $templateSimpleGridContainerModel = "
    protected \$_containerModel = '%s';
";

    public $templateSimpleGridExtends = '\\Engine\\Crud\\Grid';

    public $templateSimpleGridInitColumns = "
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

    public $templateSimpleGridInitFilters = "
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

    public $templateSimpleGridColumn = "\t\t\t'%s' => new Column\\%s('%s', '%s'),\n";

    public $templateSimpleGridComplexColumn = "\t\t\t'%s' => new Column\\%s('%s', '%s', %s),\n";

    public $templateSimpleGridFilterColumn = "\t\t\t'%s' => new Field\\%s('%s', '%s'),\n";

    public $templateSimpleGridComplexFilterColumn = "\t\t\t'%s' => new Field\\%s('%s', '%s', %s),\n";

} 