<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Filter\Field\Extjs;

use Engine\Crud\Grid\Filter as Filter,
    Engine\Crud\Grid\Filter\Field as Field;

/**
 * Class filter fields helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class Combobox extends BaseHelper
{
    /**
     * Render extjs combobox filter field
     *
     * @param \Engine\Crud\Grid\Filter\Field $field
     * @return string
     */
    public static function _(Field\ArrayToSelect $field)
    {
        $fieldCode = [];

        $fieldCode[] = "xtype: 'combobox'";
        $fieldCode[] = "name: '".$field->getKey()."'";

        $label = $field->getLabel();
        if ($label) {
            $fieldCode[] = "fieldLabel: '".$label."'";
        }
        $desc = $field->getDesc();
        if ($desc) {
            $fieldCode[] = "boxLabel: '".$desc."'";
        }
        $width = $field->getWidth();
        if ($width) {
            $fieldCode[] = "width: ".$width;
        }

        $fieldCode[] = "typeAhead: true";
        $fieldCode[] = "triggerAction: 'all'";
        $fieldCode[] = "selectOnTab: true";
        $fieldCode[] = "lazyRender: true";
        $fieldCode[] = "listClass: 'x-combo-list-small'";
        $fieldCode[] = "queryMode: 'local'";
        $fieldCode[] = "displayField: 'name'";
        $fieldCode[] = "valueField: 'id'";
        $fieldCode[] = "valueNotFoundText: 'Nothing found'";

        $store = forward_static_call(['static', '_getStore'], $field);
        $fieldCode[] = "store: ".$store;

        return forward_static_call(['static', '_implode'], $fieldCode);
    }

    /**
     * Return combobox datastore code
     *
     * @param Field\ArrayToSelect $field
     * @return string
     */
    protected static function _getStore(Field\ArrayToSelect $field)
    {
        $key = $field->getKey();
        $grid = $field->getGridFilter()->getGrid();
        $gridKey = $grid->getKey();
        $url = $grid->getAction()."/".$key."/filter-options";


        $store = "new Ext.data.Store({
                        autoLoad: true,
                        fields: [{name: 'id'}, {name: 'name'}],
                        proxy: {
                            type: 'ajax',
                            url: '".$url."',
                            reader: {
                                root: '".$gridKey."',
                                type: 'json'
                            }
                        }
                    })";

        return $store;
    }
}