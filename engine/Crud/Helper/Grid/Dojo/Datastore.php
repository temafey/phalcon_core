<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Grid\Dojo;

/**
 * Class dojo datastore helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class Datastore extends \Engine\Crud\Helper\AbstactHelper
{
    /**
     * Generates a widget to show a dojo grid datastore
     *
     * @param \Engine\Crud\Grid\AbstractGrid $grid
     * @return string
     */
    static public function _(\Engine\Crud\Grid\AbstractGrid $grid)
    {
        $code = '
        /*set up data store*/
        var data = {
            identifier: "id",
            items: []
        };
        var data_list = [
        ';
        $code .= $grid->toJson();
        $code .= '
        ];
        var rows = '.$grid->getCountCurrentPage().';
        for (var i = 0, l = data_list.length; i < rows; i++) {
            data.items.push(lang.mixin({ id: i+1 }, data_list[i%l]));
        }
        var store = new ItemFileWriteStore({data: data});
        ';

        return $code;
    }
}