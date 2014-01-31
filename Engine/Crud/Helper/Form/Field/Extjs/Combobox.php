<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Form\Field\Extjs;

use Engine\Crud\Form\Extjs as Form,
    Engine\Crud\Form\Field as Field;

/**
 * Class form fields helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class Combobox extends BaseHelper
{
    /**
     * Render extjs combobox form field
     *
     * @param \Engine\Crud\Form\Field $field
     * @return string
     */
    public static function _(Field\ArrayToSelect $field)
    {
        $fieldCode = [];

        if ($field->isHidden()) {
            $fieldCode[] = "xtype: 'hiddenfield'";
        } else {
            $fieldCode[] = "xtype: 'combobox'";
        }
        $fieldCode[] = "name: '".$field->getKey()."'";
        $fieldCode[] = "allowBlank: ".(($field->isRequire()) ? "false" : "true");

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

        $store = forward_static_call(['static', '_getStore'], $field);
        $fieldCode[] = "store: ".$store;

        return forward_static_call(['static', '_implode'], $fieldCode);
    }

    protected static function _getStore(Field\ArrayToSelect $field)
    {
        $key = $field->getKey();
        $form = $field->getForm();
        $formKey = $form->getKey();
        $url = $form->getAction()."/".$key."/options";

        $store = "new Ext.data.Store({
                        autoLoad: true,
                        fields: [{name: 'id'}, {name: 'name'}],
                        proxy: {
                            type: 'ajax',
                            url: '".$url."',
                            reader: {
                                root: '".$formKey."',
                                type: 'json'
                            }
                        }
                    })";

        return $store;
    }
}