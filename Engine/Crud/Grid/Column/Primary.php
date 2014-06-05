<?php
/**
 * @namespace
 */
namespace Engine\Crud\Grid\Column;

/**
 * class Primary
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Grid
 */
class Primary extends Base
{
    /**
     * Constructor
     *
     * @param string $title
     * @param bool $isSortable
     * @param bool $isHidden
     * @param int $width
     */
    public function __construct($title, $isSortable = true, $isHidden = false, $width = 80)
    {
        $this->_title = $title;

        $this->_isSortable = (bool) $isSortable;
        $this->_isHidden = (bool) $isHidden;
        $this->_isEditable = false;
        $this->_width = intval($width);
    }

    /**
     * Initialize field (used by extending classes)
     *
     * @return void
     */
	protected function _init()
	{
        parent::_init();

        $model = $this->_grid->getContainer()->getModel();
        $this->_name = $model->getPrimary();
	}

    /**
     * Primary column can't be editable
     *
     * @return \Engine\Crud\Grid\Column
     */
    public function setEditable()
    {
        return $this;
    }
}