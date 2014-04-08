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
class Mail extends BaseHelper
{
    /**
     * Render extjs mail filter field
     *
     * @param \Engine\Crud\Grid\Filter\Field $field
     * @return string
     */
    public static function _(Field $field)
    {
        $fieldCode = [];

        $fieldCode[] = "xtype: 'textfield'";
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

        $fieldCode[] = "vtype: 'email'";

        return forward_static_call(['self', '_implode'], $fieldCode);
    }
}