<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Filter\Field\Standart;

use Engine\Crud\Grid\Filter\Field as Field;

/**
 * Class grid filter field helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class Element extends \Engine\Crud\Helper\AbstactHelper
{
	/**
	 * Generates a widget to show a html grid filter
	 *
	 * @param \Engine\Crud\Grid\Filter\Field\AbstractField $filter
	 * @return string
	 */
	static public function _(Field\AbstractField $field)
	{
        if ($field instanceof Field\Submit) {
            $field->getElement()->setAttribute('class', 'btn');
        }
        $code = $field->getElement()->render();

		return $code;
	}
}