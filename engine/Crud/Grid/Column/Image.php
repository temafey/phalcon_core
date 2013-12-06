<?php
/**
 * @namespace
 */
namespace Engine\Crud\Grid\Column;

use Crud\Grid\AbstractGrid,
	Crud\Container\Grid as GridContainer,
	Zend\Filter\Filter;
	
/**
 * Image join column
 *
 * @uses       \Engine\Crud\Grid\Exception
 * @uses       \Engine\Crud\Grid\Filter
 * @uses       \Engine\Crud\Grid
 * @category   Engine
 * @package    Crud
 * @subpackage Grid
 */
class Image extends Base
{
	/**
	 * Image path template
	 * @var string
	 */
	protected $_template;
	
	/**
	 * Image alt template
	 * @var string
	 */
	protected $_alt;
	
	/**
	 * Image width
	 * @var integer
	 */
	public $width;
	
	/**
	 * Image height
	 * @var integer
	 */
	public $height;
	
	/**
	 * Empty image path
	 * @var string
	 */
	protected $_empty = '/img/nophoto.jpg';
	
	/**
	 * Constructor
	 * 
	 * @param string $title
	 * @param string $column
	 * @param string $template
	 * @param string $alt
	 * @param bool $isHidden
	 * @param integer $width
	 * @param integer $height
	 */
	public function __construct($title, $column = null, $template = null, $alt = null, $isHidden = false, $width = 85, $height = null) 
	{
		parent::__construct($title, $column, false, $isHidden, $width);
		
		$this->_template = $template;
		$this->_alt = $alt;
		$this->width = $width;
		$this->height = $height;
	}

    /**
     * Return render value
     * (non-PHPdoc)
     * @see \Engine\Crud\Grid\Column\AbstractColumn::render()
     * @param mixed $row
     * @return string
     */
	public function render($row)
	{	
		if ($this->_template){			
			$image = \Engine\Tools\String::generateStringTemplate($this->_template, $row, '{', '}');
		} else {
		    $image = $this->_empty;
		}

		return $this->createImage($image);
	}
	
	/**
	 * Create image html code
	 * 
	 * @param string $image
	 * @return string
	 */
	public function createImage($image)
	{        
        $fullPath = realpath(DOCUMENT_ROOT.$image);
        $alt = '';

        if (file_exists($fullPath)) {
            $src = ' src="' . $image . '"';
			$alt = ($alt) ? ' alt="' . $alt . '"' : "";
            $width = 'width: ' . $this->width .'px;';
            if($this->height) {
                $height = 'height: ' . $this->height .'px;';
            } else {
                $height = '';
            }

            $endTag = ' />';


            // build the element
            $xhtml = '<img '
            . $src
            . $alt
            . ' style="' . $width . $height . 'margin: auto;"'
            . $endTag;

            return $xhtml;
        } else {
            return false;
        }
        
	}
}