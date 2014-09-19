<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Grid\Standart;

use Engine\Crud\Grid,
    Engine\Crud\Grid\Column;

/**
 * Class grid columns helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class Columns extends \Engine\Crud\Helper
{
	/**
	 * Generates grid table colums head
	 *
	 * @param \Engine\Crud\Grid $grid
	 * @return string
	 */
	static public function _(Grid $grid)
	{
        $code = '
            <thead>
                <tr>';

        foreach ($grid->getColumns() as $column) {
            if ($column instanceof Column) {
                $columnCode = '
                <th width="'.$column->getWidth().'">';
                if ($column->isSortable()) {
                    $columnCode .= self::sortLink($column);
                } elseif ($column->isHidden()) {
                    $columnCode .= '';
                } else {
                    $columnCode .= $column->getTitle();
                }
            } else {
                $columnCode = '
                <th>';
            }
            $columnCode .= '
                </th>';
            $code .= $columnCode;
        }

        $code .= '
                </tr>
            </thead>';

        return $code;
	}

    /**
     * Create column sortable link
     *
     * @param \Engine\Crud\Grid\Column $column
     * @return string
     */
    static public function sortLink(Column $column)
    {
        $grid = $column->getGrid();
        $action = $grid->getAction();
        $sortDirection = $column->getSortDirection();
        $sorted = $column->isSorted();
        $params = $column->getSortParams();
        if ($action) {
            $action = '/'.$action.'/?'.http_build_query($params);
        } else {
            $action = '?'.http_build_query($params);
        }
        $link = '';
        $sortIcon = '';
        if ($sorted) {
            $link .= '<b>';
            $sortIcon = ($sortDirection == "asc") ? "/\\" : "\\/";
        }
        $link .= '<a href="'.$action.'">'.$column->getTitle()." ".$sortIcon."</a>";
        if ($sorted) {
            $link .= '</b>';
        }

        return $link;
    }
}