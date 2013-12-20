<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Grid\Extjs;

use Engine\Crud\Grid\Extjs as Grid,
    Engine\Crud\Grid\Column as Column;

/**
 * Class grid columns helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class Columns extends BaseHelper
{
	/**
	 * Generates grid columns object
	 *
	 * @param \Engine\Crud\Grid\Extjs $grid
	 * @return string
	 */
	static public function _(Grid $grid)
	{
        $code = "
            columnsGet: function(){
                return [";

        $columns = [];
        foreach ($grid->getColumns() as $column) {
            if ($column instanceof Column) {
                $columnCode = [];
                $columnCode[] = "width: ".$column->getWidth();
                if ($column->isSortable()) {
                    $columnCode[] = "sortable: true";
                }
                if ($column->isHidden()) {
                    $columnCode[] = "hidden: true";
                }
                $columnCode[] = "text: '".$column->getTitle()."'";
                $columnCode[] = "field: 'textfield'";
                $columnCode[] = "dataIndex: '".$column->getKey()."'";

                $columnCode = "\n\t\t\t{\n\t\t\t".implode(",\n\t\t\t", $columnCode)."\n\t\t\t}";
                $columns[] = $columnCode;
            }
        }

        $code .= implode(",", $columns);

        $code .= "
                ]
            },";

        return $code;
	}
}