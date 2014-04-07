<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Grid\Extjs;

use Engine\Crud\Grid\Extjs as Grid;

/**
 * Class grid paginator helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class Paginator extends BaseHelper
{
    /**
     * Generates grid paginate object
     *
     * @param \Engine\Crud\Grid\Extjs $grid
     * @return string
     */
    static public function _(Grid $grid)
    {
        $action = $grid->getAction();
        $sortParams = $grid->getSortParams();

        if ($sortParams) {
            foreach ($sortParams as $param => $value) {
                $action = self::setUrlParam($action, $param, $value);
            }
        }

        $code = "
            getBottomToolbarItems: function() {
                var me = this;

                var items = [
                    me.getPagingToolbar()
                ];

                return items;
            },";

        return $code;
    }
}