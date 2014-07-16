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
class Numeric extends BaseHelper
{
    /**
     * Render extjs number form field
     *
     * @param \Engine\Crud\Form\Field $field
     * @return string
     */
    public static function _(Field\Numeric $field)
    {
        $fieldCode = [];

        if ($field->isHidden()) {
            $fieldCode[] = "xtype: 'hiddenfield'";
        } else {
            $fieldCode[] = "xtype: 'numberfield'";
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

        $minValue = $field->getMinValue();
        $maxValue = $field->getMaxValue();

        if ($minValue !== null && $minValue !== false) {
            $fieldCode[] = " minValue: '".$minValue."'";
        }
        if ($maxValue !== null && $maxValue !== false) {
            $fieldCode[] = " maxValue: '".$maxValue."'";
        }

        return forward_static_call(['self', '_implode'], $fieldCode);
    }
}