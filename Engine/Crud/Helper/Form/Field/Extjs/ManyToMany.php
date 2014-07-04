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
class ManyToMany extends BaseHelper
{
    /**
     * Render extjs combobox form field
     *
     * @param \Engine\Crud\Form\Field\ManyToMany $field
     * @return string
     */
    public static function _(Field\ManyToMany $field)
    {
        $fieldCode = [];

        if ($field->isHidden()) {
            $fieldCode[] = "xtype: 'hiddenfield'";
        } else {
            $fieldCode[] = "xtype: 'comboboxselect'";
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
        $fieldCode[] = "queryMode: 'local'";
        $fieldCode[] = "displayField: 'name'";
        $fieldCode[] = "valueField: 'id'";
        $fieldCode[] = "valueNotFoundText: 'Nothing found'";
        $fieldCode[] = "labelWidth: 130";
        $fieldCode[] = "rowMin: 20";
        //$fieldCode[] = "growMax: 24";

        $fieldCode[] = "pageSize: 10";
        $fieldCode[] = "queryMode: 'remote'";
        $fieldCode[] = "delimiter: ','";
        $fieldCode[] = "forceSelection: true";
        //$fieldCode[] = "triggerOnClick": false";
        $fieldCode[] = "listConfig: {
                            tpl: [
                                '<ul><tpl for=\".\">',
                                '<li role=\"option\" class=\"x-boundlist-item\">{name}</li>',
                                ''</tpl></ul>''
                            ]
                        }'";

        $store = forward_static_call(['static', '_getStore'], $field);
        $fieldCode[] = "store: ".$store;

        $listeners = forward_static_call(['static', '_getListeners'], $field);
        $fieldCode[] = "listeners: ".$listeners;



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
        $form = $field->getForm();
        $formKey = $form->getKey();
        $url = $form->getAction()."/".$key."/options";

        $autoLoad = ($field->getAttrib('autoLoad')) ? true : false;
        $isLoaded = ($field->getAttrib('isLoaded')) ? true : false;

        $store = "new Ext.data.Store({
                        autoLoad: ".($autoLoad ? "true" : "false").","
            .($isLoaded ? "
                        isLoaded: false," : "")."
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

    /**
     * Return combobox listeners code
     *
     * @param Field\ArrayToSelect $field
     * @return string
     */
    protected static function _getListeners(Field\ArrayToSelect $field)
    {
        $listeners = "{";

        $listeners .= "
                    }";

        return $listeners;
    }
}