<?php
/**
 * Created by Slava Basko.
 * Email: basko.slava@gmail.com
 * Date: 5/20/14
 */

namespace Engine\Crud\Grid\Column;


class Custom extends Base
{
    /**
     * @var \Closure
     */
    private $closure;

    /**
     * Default params
     * @var array
     */
    private $defaultParams = [
        'isSortable' => true,
        'isHidden' => false,
        'width' => 200,
        'isEditable' => true,
        'fieldKey' => null
    ];

    /**
     * Column base constructor
     *
     * @param string $title
     * @param callable $closure
     * @param array $params
     */
    public function __construct($title, \Closure $closure, $params = [])
    {
        $params = array_merge($this->defaultParams, $params);
        extract($params);
        /** @var $isSortable bool */
        /** @var $isHidden bool */
        /** @var $width integer */
        /** @var $isEditable bool */
        /** @var $fieldKey */
        parent::__construct($title, null, $isSortable, $isHidden, $width, $isEditable, $fieldKey);

        $this->closure = $closure;
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
     * Render column
     *
     * @param mixed $row
     * @return string
     */
    public function render($row)
    {
        return $this->closure->__invoke($row);
    }

} 