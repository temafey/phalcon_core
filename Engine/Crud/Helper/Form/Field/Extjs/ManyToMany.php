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
        static::addRequires('Ext.ux.form.field.BoxSelect');

        $fieldCode = [];

        if ($field->isHidden()) {
            $fieldCode[] = "xtype: 'hiddenfield'";
        } else {
            $fieldCode[] = "xtype: 'comboboxselect'";
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
        $separator = $field->getSeparator();
        if ($separator) {
            $fieldCode[] = "delimiter: '".$separator."'";
        } else {
            $fieldCode[] = "delimiter: ' '";
        }
        $fieldCode[] = "typeAhead: true";
        $fieldCode[] = "triggerAction: 'all'";
        $fieldCode[] = "selectOnTab: true";
        $fieldCode[] = "lazyRender: true";
        $fieldCode[] = "displayField: 'name'";
        $fieldCode[] = "valueField: 'name'";
        $fieldCode[] = "valueNotFoundText: 'Nothing found'";
        $fieldCode[] = "rowMin: 20";
        //$fieldCode[] = "growMax: 24";
        $fieldCode[] = "queryMode: 'remote'";
        //$fieldCode[] = "forceSelection: true";
        $fieldCode[] = "pageSize: 10";
        $fieldCode[] = "minChars: 1";
        //$fieldCode[] = "triggerOnClick": false";
        $fieldCode[] = "listConfig: {
                            tpl: [
                                '<ul><tpl for=\".\">',
                                '<li role=\"option\" class=\"x-boundlist-item\">{name}</li>',
                                '</tpl></ul>'
                            ]
                        }";

        $store = forward_static_call(['static', '_getStore'], $field);
        $fieldCode[] = "store: ".$store;

        $options = [];
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
    protected static function _getStore(Field\ManyToMany $field)
    {
        $key = $field->getKey();
        $form = $field->getForm();
        $formKey = $form->getKey();
        $url = $form->getAction()."/".$key."/multi-options";

        $autoLoad = ($field->getAttrib('autoLoad')) ? true : false;
        $isLoaded = ($field->getAttrib('isLoaded')) ? true : false;

        $store = "new Ext.data.Store({
                        autoLoad: ".($autoLoad ? "true" : "false").",
                        pageSize: 10,"
            .($isLoaded ? "
                        isLoaded: false," : "")."
                        fields: [{name: 'id'}, {name: 'name'}],
                        proxy: {
                            type: 'ajax',
                            url: '".$url."',
                            reader: {
                                root: '".$formKey."',
                                type: 'json',
                                totalProperty: 'results'
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
    protected static function _getListeners(Field\ManyToMany $field)
    {
        $listeners = "{";

        $listeners .= "
                    }";

        return $listeners;
    }
}