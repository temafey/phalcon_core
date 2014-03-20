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
class Passwordconfirm extends BaseHelper
{
    /**
     * Render extjs text form field
     *
     * @param \Engine\Crud\Form\Field $field
     * @return string
     */
    public static function _(Field\PasswordConfirm $field)
    {
        $fieldCode = [];

        if ($field->isHidden()) {
            $fieldCode[] = "xtype: 'hiddenfield'";
        } else {
            $fieldCode[] = "xtype: 'textfield'";
        }
        $fieldCode[] = "name: '".$field->getKey()."'";
        $fieldCode[] = "inputType: 'password'";
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

        $confirmKey = $field->getConfirmKey();
        $fieldCode[] = "validator: function(value) {
                    var password1 = this.previousSibling('[name=".$confirmKey."]');
                    return (value === password1.getValue()) ? true : 'Passwords do not match.'
                }";

        return forward_static_call(['self', '_implode'], $fieldCode);
    }
}