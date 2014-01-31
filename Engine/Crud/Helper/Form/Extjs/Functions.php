<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Form\Extjs;

use Engine\Crud\Form\Extjs as Form;

/**
 * Class form functions helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class Functions extends BaseHelper
{
	/**
	 * Generates form functions object
	 *
	 * @param \Engine\Crud\Form\Extjs $form
	 * @return string
	 */
	static public function _(Form $form)
	{
        $primary = $form->getPrimaryField();
        $key = ($primary) ? $primary->getKey() : false;
        $url = $form->getAction()."/save";

        $code = "
            tbarGet: function() {
                return[
                ]
            },

            bbarGet: function() {
                return [
                ]
            },

            onReset: function() {
                //this.getForm().reset();
                var fields = this.getForm().getFields().items;

                for (var i = 0, len = fields.length; i < len; i++) {";
        if ($key) {
            $code .= "
                        if (fields[i].name !== '".$key."') {
                            fields[i].reset();
                        }";
        } else {
            $code .= "
                       fields[i].reset();";
        }
        $code .= "
                }
            },

            onClear: function() {
                this.getForm().reset();
                var primary = this.getPrimaryField();
                primary.disable();
            },

            onSubmit: function() {
                var me = this;

                if (me.getForm().isValid()) {
                    me.getForm().submit({
                        url: '".$url."',
                        parentForm: me,
                        success: function(form, action) {
                            if (this.parentForm.grid !== undefined) {
                                this.parentForm.grid.onReload();
                            }
                            Ext.Msg.alert('Success', action.result.msg);
                        },
                        failure: function(form, action) {
                            Ext.Msg.alert('Failed', action.result.error);
                        }
                    });
                }
            },

            setActiveRecord: function(record) {
                var me = this;

                me.activeRecord = record;
                if (record) {
                    //me.down('#save').enable();
                    me.getForm().loadRecord(record);
                } else {
                    me.down('#save').disable();
                    me.getForm().reset();
                }
            },

            getPrimaryField: function() {
                var me = this;";
            if ($key) {
                $code .= "

                var fields = me.getForm().getFields().items;

                for (var i = 0, len = fields.length; i < len; i++) {
                    if (fields[i].name === '".$key."') {
                        return fields[i]
                    }
                }";
            }
            $code .= "
                return false;
            },

            getLink: function() {
                var id = this.getPrimaryField().getValue();
                return (id !== '') ? this.rtrim(this.link, '/')+'/'+id : '#';
            },

            rtrim: function (str, charlist) {
                charlist = !charlist ? ' \\s\u00A0' : (charlist + '').replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '\\$1');
                var re = new RegExp('[' + charlist + ']+$', 'g');
                return (str + '').replace(re, '');
            }
            ";

        return $code;
	}
}