<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Grid\Extjs;

use Engine\Crud\Grid\Extjs as Grid,
    Engine\Crud\Grid\Column as Column;

/**
 * Class extjs grid model helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class Model extends BaseHelper
{
    /**
     * Is create js file prototype
     * @var boolen
     */
    protected static $_createJs = true;

    /**
     * Generates a widget to show a html grid
     *
     * @param \Engine\Crud\Grid\Extjs $grid
     * @return string
     */
    static public function _(Grid $grid)
    {
        $code = "
        Ext.define('".static::getModelName()."', {
            extend: 'Ext.data.Model',";
            $columns = [];
            $validations = [];
            $primary = false;
            foreach ($grid->getColumns() as $column) {
                if ($column instanceof Column) {
                    $field = $column->getKey();
                    $type = $column->getType();
                    $columnCode = "
                    {
                        name: '".$field."',
                        type: '".$type."'
                    }";
                    //$validationCode = "{field: '".$field."', type: }";
                    /*
                     * type:
                        presence
                        length
                        inclusion
                        exclusion
                        format
                     */
                    $columns[] = $columnCode;
                    //$validations[] = $validationCode;

                    if ($column instanceof Column\Primary) {
                        $primary = $field;
                    }
                }
            }
            $code .= "
            fields: [".implode(",", $columns)."
            ],";

            $code .= "
            validations: [".implode(",", $validations)."]";

            if ($primary !== false) {
                $code .= ",
            idProperty: '".$primary."'";
            }
            $code .= "
        });";

        return $code;
    }

    /**
     * Return object name
     *
     * @return string
     */
    public static function getName()
    {
        return static::getModelName();
    }

    /**
     * Crud helper end tag
     *
     * @return string
     */
    static public function endTag()
    {
        return false;
    }

}