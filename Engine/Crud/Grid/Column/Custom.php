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

    private $defaultParams = [
        'isSortable' => true,
        'isHidden' => false,
        'width' => 200,
        'isEditable' => true,
        'fieldKey' => null
    ];

    public function __construct($title, \Closure $closure, $params = [])
    {
        $params = array_merge($params, $this->defaultParams);
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

    public function render($row)
    {
        return $this->closure->__invoke($row);
    }

} 