<?php
/**
 * @namespace
 */
namespace Engine\Crud\Grid\Column;

use Engine\Crud\Grid,
    Engine\Crud\Container\Grid as GridContainer;
	
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
     * Field type.
     * @var string
     */
    protected $_type = 'image';

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
	 * @param int $width
	 * @param int $height
	 */
	public function __construct(
        $title,
        $column = null,
        $template = null,
        $alt = null,
        $isHidden = false,
        $width = 85,
        $height = null
    ) {
		parent::__construct($title, $column, false, $isHidden, $width, false, null);
		
		$this->_template = $template;
		$this->_alt = $alt;
		$this->width = $width;
		$this->height = $height;
	}

    /**
     * Return render value
     * (non-PHPdoc)
     * @see \Engine\Crud\Grid\Column::render()
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
        //$fullPath = realpath(DOCUMENT_ROOT.$image);
        $alt = '';

        /*if (!file_exists($fullPath)) {
            return 'Image not exists';
        }*/
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
        
	}
}