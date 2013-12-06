<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Form;

use Engine\Crud\Form\AbstractForm as Form;

/**
 * Class grid filter helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class Standart extends \Engine\Crud\Helper\AbstactHelper
{
	/**
	 * Generates a widget to show a html grid filter
	 *
	 * @param \Engine\Crud\Form\AbstractForm $form
	 * @return string
	 */
	static public function _(Form $crudForm)
	{
        $crudForm->initForm();
        $form = $crudForm->getForm();
        $code = '<form method="'.$form->getMethod().'" action="'.$form->getAction().'" class="form-horizontal">';
        $code .= "
            <fieldset>
            <legend>".$crudForm->getTitle()."</legend>";

		return $code;
	}

    /**
     * Crud helper end tag
     *
     * @return string
     */
    static public function endTag()
    {
        return '</fieldset></form>';
    }
}