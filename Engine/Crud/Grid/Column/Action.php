<?php
/**
 * @namespace
 */
namespace Engine\Crud\Grid\Column;

use Engine\Crud\Grid\Column,
    Engine\Crud\Grid,
    Engine\Crud\Container\Grid as GridContainer,
    Phalcon\Filter;

/**
 * Class Action
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Grid
 */
class Action extends Column
{
    /**
     * Action template
     * @var string
     */
    protected $_template;

    /**
     * Action template title
     * @var null|string
     */
    protected $_templateTitle;

    /**
     * Action icon image
     * @var string
     */
    protected $_icon;

    /**
     * Construct
     *
     * @param string $template
     * @param string $title
     * @param string $icon
     * @param int $width
     */
    public function __construct($template, $title, $icon = null, $width = 80)
    {
        parent::__construct(null, null, false, false, $width);

        $this->_template = $template;
        $this->_templateTitle = $title;
        $this->_icon = $icon;
    }

    /**
     * Update grid container
     *
     * @param \Engine\Crud\Container\Grid\Adapter $container
     * @return \Engine\Crud\Grid\Column
     */
    public function updateContainer(\Engine\Crud\Container\Grid\Adapter $container)
    {
        return $this;
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
        $attribs = $this->getAttribs();
        $href = \Engine\Tools\String::generateStringTemplate($this->_template, $row, '{', '}');
        $code = '<a href="'.$href.'"';

        foreach ($attribs as $name => $value) {
            $code .= ' '.$name.'="'.$value.'"';
        }
        $title = \Engine\Tools\String::generateStringTemplate($this->_templateTitle, $row, '{', '}');
        $code .= '><span>'.$title.'</span></a>';

        return $code;
    }
}