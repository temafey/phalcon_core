<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Grid\Extjs;

use Engine\Crud\Grid\Extjs as Grid;

/**
 * Class grid functions helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class Functions extends BaseHelper
{
	/**
	 * Generates grid functions object
	 *
	 * @param \Engine\Crud\Grid\Extjs $grid
	 * @return string
	 */
	static public function _(Grid $grid)
	{
        $code = "
            tbarGet: function(){
                var me = this;

                return[
                    {
                        xtype: 'button',
                        scope: me,
                        text: 'Add',
                        iconCls: 'icon-add',
                        handler: this.onCreate
                    },
                    {
                        type: 'button',
                        scope: me,
                        text: 'Remove',
                        iconCls: 'icon-delete',
                        handler: this.onDelete
                    },
                    '|',
                    'Search: ',{
                        xtype: 'textfield',
                        name: 'searchField',
                        width: 200,
                        listeners: {
                            change: {
                                fn: me.onTextFieldChange,
                                scope: me,
                                buffer: 100
                            }
                        }
                    }
                ]
            },

            onCreate: function(){
                this.fireEvent('select', this, undefined);
            },

            onDelete: function(){
                var me = this;
                var selections = me.getSelectionModel().getSelection();
                if(selections.length > 0) {
                    Ext.MessageBox.confirm('Information', 'Do you really want to remove the selected items?' , function (btn) {
                        if(btn == 'yes') {
                            for (var i = 0, len = selections.length; i < len; i++) {
                                me.getStore().remove(selections[i]);
                            }
                            me.getStore().load();
                        }
                    });
                } else {
                    Ext.MessageBox.alert('Information', 'No selected items');
                }
            },

            onSelect: function(model, selections){
                var selected = selections[0];
                if (selected) {
                    this.fireEvent('select', this, selected);
                }
            },

            onEdit: function(editor, e){
                var me = this;
                var rec = e.record;
                for (var key in e.newValues) {
                    if (e.newValues[key] !== e.originalValues[key]) {
                        var column = me.getColumnByName(key);
                        if (column.field !== undefined && column.field.xtype === 'combobox') {
                            var value = column.field.store.getById(e.newValues[key]);
                            rec.data[key] = value.data.name;
                        }
                    }
                }

                me.fireEvent('select', me, rec);
            },

            onReload: function(){
                this.getStore().load();
            },

            onDbClick: function(model, selections){
                var selected = selections[0];
                if (selected) {
                }
            },

            getSearchValue: function() {
                var me = this,
                    value = me.textField.getValue();

                if (value === '') {
                    return null;
                }

                return value;
            },

            onTextFieldChange: function() {
                var me = this,
                    store = this.getStore(),
                    count = 0;

                me.view.refresh();
                me.searchValue = me.getSearchValue();

                    store.addBaseParamKeyValue('search', me.searchValue);
                    store.reload({params: {start:0}});
            },

            getColumnByName: function(dataIndex) {
                var me = this;
                for (var i = 0; i < me.columns.length; i++) {
                    if (me.columns[i].dataIndex == dataIndex) {
                        return me.columns[i];
                    }
                }
                return false;
            },
            ";

        return $code;
	}
}