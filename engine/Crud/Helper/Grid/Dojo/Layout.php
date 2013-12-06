<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Grid\Dojo;

/**
 * Class dojo layuot helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class Layout extends \Engine\Crud\Helper\AbstactHelper
{
    /**
     * Generates a widget to show a dojo grid layout
     *
     * @param \Engine\Crud\Grid\AbstractGrid $grid
     * @return string
     */
    static public function _(\Engine\Crud\Grid\AbstractGrid $grid)
    {
        $code = '
        /*set up layout*/
        var layout = [
        ';

        $columns = [];
        foreach ($grid->getColumns() as $column) {
            $columnData = [];
            if ($column instanceof \Engine\Crud\Grid\Column\AbstractColumn) {
                $columnData['name'] = $column->getTitle();
                $columnData['field'] = $column->getKey();
                $columnData['width'] = $column->getWidth()."px";
            }
            $columns[] = $columnData;
        }

        $code .= json_encode($columns);
        $code .= '
        ];';

        return $code;
    }
}