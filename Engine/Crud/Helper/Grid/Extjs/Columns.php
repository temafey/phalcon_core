<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Grid\Extjs;

use Engine\Crud\Grid\Extjs as Grid,
    Engine\Crud\Grid\Column,
    Engine\Crud\Form\Field,
    Engine\Crud\Helper\Form\Extjs\BaseHelper as FieldHelper;

/**
 * Class grid columns helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class Columns extends BaseHelper
{
	/**
	 * Generates grid columns object
	 *
	 * @param \Engine\Crud\Grid\Extjs $grid
	 * @return string
	 */
	static public function _(Grid $grid)
	{
        $code = "
            columnsGet: function(){
                return [";

        $columns = [];
        foreach ($grid->getColumns() as $column) {
            if ($column instanceof Column) {
                $type = $column->getType();
                /*if (!method_exists(__CLASS__, '_'.$type)) {
                    throw new \Engine\Exception("Field with type '".$type."' haven't render method in '".__CLASS__."'");
                }*/
                switch ($type) {
                    case 'image':
                        $columnCode = forward_static_call(['static', '_'.$type], $column);
                        break;
                    case 'check':
                        $columnCode = forward_static_call(['static', '_'.$type], $column);
                        break;
                    default:
                        $columnCode = forward_static_call(['static', '_column'], $column);
                        break;
                }
                $columns[] = $columnCode;
            }
        }

        $code .= implode(",", $columns);

        $code .= "
                ]
            },
            ";
        
        return $code;
	}

    /**
     * Render grid colum
     *
     * @param \Engine\Crud\Grid\Column $column
     * @return string
     */
    public static function _column(Column $column)
    {
        $columnCode = [];
        $columnCode[] = "text: '".$column->getTitle()."'";
        $columnCode[] = "dataIndex: '".$column->getKey()."'";
        $columnCode[] = "width: ".$column->getWidth();
        if ($column->isSortable()) {
            $columnCode[] = "sortable: true";
        }
        if ($column->isHidden()) {
            $columnCode[] = "hidden: true";
        } elseif ($column->isEditable()) {
            $field = $column->getField();
            if (!$field instanceof Field) {
                throw new \Engine\Exception("Form field for column '".$column->getKey()."' does not exist");
            }
            if ($field instanceof Field\ArrayToSelect) {
                $field->setAttrib("autoLoad", true);
            }
            $field->setLabel(false);
            $field->setWidth(false);
            $field->setDesc(false);
            $fieldCode = "field:";
            $fieldCode .= FieldHelper::renderField($field, $column);
            $columnCode[] = $fieldCode;
        }

        return forward_static_call(['static', '_implode'], $columnCode);
    }

    /**
     * Render grid colum
     *
     * @param \Engine\Crud\Grid\Column $column
     * @return string
     */
    public static function _image(Column $column)
    {
        $columnCode = [];
        $columnCode[] = "text: '".$column->getTitle()."'";
        $columnCode[] = "dataIndex: '".$column->getKey()."'";
        $columnCode[] = "width: ".$column->getWidth();
        if ($column->isSortable()) {
            $columnCode[] = "sortable: true";
        }
        if ($column->isHidden()) {
            $columnCode[] = "hidden: true";
        } elseif ($column->isEditable()) {
            /*$field = $column->getField();
            $field->setLabel(false);
            $field->setWidth(false);
            $field->setDesc(false);
            $fieldCode = "field:";
            $fieldCode .= FieldHelper::renderField($field);
            $columnCode[] = $fieldCode;*/
        }

        return forward_static_call(['static', '_implode'], $columnCode);
    }

    /**
     * Render string model column type
     *
     * @param \Engine\Crud\Grid\Column $column
     * @return string
     */
    public static function _string(Column $column)
    {
        $columnCode = [];
        $columnCode[] = "text: '".$column->getTitle()."'";
        $columnCode[] = "dataIndex: '".$column->getKey()."'";
        $columnCode[] = "width: ".$column->getWidth();
        if ($column->isSortable()) {
            $columnCode[] = "sortable: true";
        }
        if ($column->isHidden()) {
            $columnCode[] = "hidden: true";
        }
        if ($column->isEditable()) {
            $columnCode[] = "field: 'textfield'";
        }

        return forward_static_call(['static', '_implode'], $columnCode);
    }

    /**
     * Render date column type
     *
     * @param \Engine\Crud\Grid\Column $column
     * @return string
     */
    public static function _date(Column\Date $column)
    {
        $columnCode = [];
        $columnCode[] = "text: '".$column->getTitle()."'";
        $columnCode[] = "dataIndex: '".$column->getKey()."'";
        $columnCode[] = "width: ".$column->getWidth();
        if ($column->isSortable()) {
            $columnCode[] = "sortable: true";
        }
        if ($column->isHidden()) {
            $columnCode[] = "hidden: true";
        }
        if ($column->isEditable()) {
            $field = $column->getField();
            $format = $field->getFormat();
            $minValue = $field->getMinValue();
            $maxValue = $field->getMaxValue();
            $disabledDays = false;
            $disabledDaysText = false;

            $fieldCode = "field: {
                    xtype: 'datefield',
                    ";
            $fieldCode .= "format: '".$format."'";
            if ($minValue !== null && $minValue !== false) {
                $fieldCode .= ",
                    minValue: '".$minValue."'";
            }
            if ($maxValue !== null && $maxValue !== false) {
                $fieldCode .= ",
                    maxValue: '".$maxValue."' ";
            }
            if ($disabledDays !== null && $disabledDays !== false) {
                    $fieldCode .= ",
                    disabledDays: '".$disabledDays."'";
            }
            if ($disabledDaysText !== null && $disabledDaysText !== false) {
                        $fieldCode .= ",
                    disabledDaysText: '".$disabledDaysText."' ";
            }
            $fieldCode .= "
                }";
            $columnCode[] = $fieldCode;
        }

        return forward_static_call(['static', '_implode'], $columnCode);
    }

    /**
     * Render collection column type
     *
     * @param \Engine\Crud\Grid\Column $column
     * @return string
     */
    public static function _collection(Column\Collection $column)
    {
        $columnCode = [];
        $columnCode[] = "text: '".$column->getTitle()."'";
        $columnCode[] = "dataIndex: '".$column->getKey()."'";
        $columnCode[] = "width: ".$column->getWidth();
        if ($column->isSortable()) {
            $columnCode[] = "sortable: true";
        }
        if ($column->isHidden()) {
            $columnCode[] = "hidden: true";
        }
        if ($column->isEditable()) {
            $field = $column->getField();
            $options = \Engine\Tools\Arrays::assocToArray($field->getOptions());
            $columnCode[] = "field: {
                    xtype: 'combobox',
                    typeAhead: true,
                    triggerAction: 'all',
                    selectOnTab: true,
                    store: ".json_encode($options).",
                    lazyRender: true,
                    listClass: 'x-combo-list-small'
                }";
        }

        return forward_static_call(['static', '_implode'], $columnCode);
    }

    /**
     * Render collection column type
     *
     * @param \Engine\Crud\Grid\Column $column
     * @return string
     */
    public static function _check(Column\Status $column)
    {
        $columnCode = [];
        $columnCode[] = "xtype: 'checkcolumn'";
        $columnCode[] = "header: '".$column->getTitle()."'";
        $columnCode[] = "dataIndex: '".$column->getKey()."'";
        $columnCode[] = "stopSelection: false";
        if ($column->isSortable()) {
            $columnCode[] = "sortable: true";
        }
        $columnCode[] = "width: ".$column->getWidth();

        return forward_static_call(['static', '_implode'], $columnCode);
    }

    /**
     * Render date column type
     *
     * @param \Engine\Crud\Grid\Column $column
     * @return string
     */
    public static function _int(Column\Numeric $column)
    {
        $columnCode = [];
        $columnCode[] = "width: ".$column->getWidth();
        $columnCode[] = "align: 'right'";
        if ($column->isSortable()) {
            $columnCode[] = "sortable: true";
        }
        if ($column->isHidden()) {
            $columnCode[] = "hidden: true";
        }
        $columnCode[] = "text: '".$column->getTitle()."'";
        $columnCode[] = "dataIndex: '".$column->getKey()."'";
        if ($column->isEditable()) {
            $field = $column->getField();
            $minValue = $field->getMinValue();
            $maxValue = $field->getMaxValue();

            $fieldCode = "field: {
                    xtype: 'numberfield',
                    allowBlank: false,
                    ";
            if ($minValue !== null && $minValue !== false) {
                $fieldCode .= ",
                    minValue: '".$minValue."'";
            }
            if ($maxValue !== null && $maxValue !== false) {
                $fieldCode .= ",
                    maxValue: '".$maxValue."' ";
            }
            $fieldCode .= "
                }";
            $columnCode[] = $fieldCode;
        }

        return forward_static_call(['static', '_implode'], $columnCode);
    }

    /**
     * Render collection column type
     *
     * @param \Engine\Crud\Grid\Column $column
     * @return string
     */
    public static function _action(Column\Action $column)
    {
        $columnCode = [];
        $columnCode[] = "xtype: 'actioncolumn'";
        $columnCode[] = "stopSelection: false";
        $columnCode[] = "sortable: false";
        $columnCode[] = "menuDisabled: true";
        $columnCode[] = "width: ".$column->getWidth();
        $columnCode[] = "items: [{
                    icon: '/extjs/apps/Cms/images/icons/fam/delete.gif',
                    tooltip: '".$column->getTitle()."',
                    scope: this,
                    handler: this.'".$column->getAction()."'
                }]
        ";

        return forward_static_call(['static', '_implode'], $columnCode);
    }

    /**
     * Implode column components to formated string
     *
     * @param array $components
     * @return string
     */
    public static function _implode(array $components)
    {
        return "\n\t\t\t\t{\n\t\t\t\t\t".implode(",\n\t\t\t\t\t", $components)."\n\t\t\t\t}";
    }
}