<?php
/**
 * @namespace
 */
namespace Engine\Crud\Form\Field;
	
/**
 * Text field
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Form
 */
class Image extends File
{	
	/**
	 * Template for render image source link 
	 * @var string
	 */
	protected $_renderTemplate;
	
	/**
	 * Template for image title from form fields render value
	 * @var string
	 */
	protected $_titleTemplate;

	/**
	 * Constructor
	 *
     * @param string $label
	 * @param string $name
	 * @param string $uploadDirectory
	 * @param string $template
	 * @param string $label
	 * @param string $desc
	 * @param int $size
	 * @param bool $required
	 * @param int $width
	 * @param string $extensions
	 * @param string $renderTemplate
	 * @param string $labelTemplate
	 */
	public function __construct(
        $label = null,
        $name = false,
        $uploadDirectory,
        $template = '{id}',
        $desc = null,
        $size = '1024000',
        $required = false,
        $width = 280,
        $extensions = 'jpg,png,gif,jpeg,bmp',
        $renderTemplate = null,
        $labelTemplate = null
    ) {
		parent::__construct(
            $label,
            $name,
            $uploadDirectory,
            $template,
            $desc,
            $size,
            $required,
            $width,
            $extensions
        );

		$this->_renderTemplate = $renderTemplate;
		$this->_labelTemplate = $labelTemplate;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Crud\Form\Field.Field::getRenderValue()
	 */
	public function getRenderValue()
	{
		$value = $this->getValue();
			
		$values = $this->_form->getData();
		$source = \Engine\Tools\String::generateStringTemplate($this->_renderTemplate, $values, '{', '}');
		if($source === false) {
			return $value;
		}
		$values = $this->_form->getRenderData();
		$label = \Engine\Tools\String::generateStringTemplate($this->_labelTemplate, $values, '{'.'}');
        $class = $this->getAttrib('class');
		$xhtml = $this->createImage($source, $label, $class);
		
		return $xhtml;
	}

	/**
	 * Create image html
	 * 
	 * @param string $image
	 * @param string $label
	 * @return string
	 */
	public function createImage($image, $label = null, $class = null)
	{
		$src = ' src="' . $image . '"';
		$imageTitle = ($label) ? ' title="' . $label . '"' : '';
		$imageAlt =  ($label) ? ' alt="' . $label . '"' : '';
        $class = ($class) ? ' class="' . $class . '"' : '';
		$endTag = ' />';
		// build the element
		$xhtml = '<img '
            .' id="'.$this->getId().'"'
            .$src
            .$class
            .$imageTitle
            .$imageAlt
            .$endTag;

		return $xhtml;
	}
	
}