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
class Layout extends \Engine\Crud\Helper
{
    /**
     * Generates a widget to show a dojo grid layout
     *
     * @param \Engine\Crud\Grid $grid
     * @return string
     */
    static public function _(\Engine\Crud\Grid $grid)
    {
        $code = '
        /*set up layout*/
        var layout = [
        ';

        $columns = [];
        foreach ($grid->getColumns() as $column) {
            $columnData = [];
            if ($column instanceof \Engine\Crud\Grid\Column) {
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