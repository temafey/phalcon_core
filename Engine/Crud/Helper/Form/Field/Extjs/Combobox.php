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
     * @param \Engine\Crud\Form\Field\ArrayToSelect $field
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
        if ($field->isNotEdit()) {
            $fieldCode[] = "readOnly: true";
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
        $fieldCode[] = "valueNotFoundText: 'Nothing found'";

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

        if ($field->getAttrib('changeListener')) {
            $listeners .= "
                        change: function (field, newValue, oldValue) {
                            var record = null;
                            if (field.store.isLoaded !== false) {
                                record = field.store.findRecord('id', newValue);
                                if (record === null) {
                                    record = field.store.findRecord('name', newValue);
                                    if (record !== null) {
                                        field.setValue(record);
                                    }
                                }
                            } else {
                                field.store.addListener('load', function() {
                                    field.store.isLoaded = true;
                                    record = field.store.findRecord('name', newValue);
                                    field.setValue(record);
                                }, field);
                                field.store.load();
                            }
                        }";
        }

        $listeners .= "
                    }";

        return $listeners;
    }
}