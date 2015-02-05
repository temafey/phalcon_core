<?php
/**
 * @namespace
 */
namespace Engine\Paginator\Adapter;
use Engine\Mvc\Model\Query\Builder;

/**
 * Class Grid
 * @package Engine\Paginator\Adapter
 */
class Grid implements \Phalcon\Paginator\AdapterInterface
{
    /**
     * @var \Engine\Mvc\Model\Query\Builder
     */
    protected $_queryBuilder;

    /**
     * @var integer
     */
    protected $_extraLimit = 10;

    /**
     * @var integer
     */
    protected $_limit = 10;

    /**
     * @var integer
     */
    protected $_page = 1;

    /**
     * @var integer
     */
    protected $_total;

    /**
     * @var \Phalcon\Mvc\Model\Resultset\Simple
     */
    protected $_items;

    /**
     * @var \stdClass
     */
    protected $_paginate;

    /**
     * Adapter constructor
     *
     * @param array $config
     */
    public function __construct($config)
    {
        if (!isset($config['builder'])) {
            throw new \Engine\Exception('Query builder not set!');
        }
        $this->_queryBuilder = $config['builder'];

        if (isset($config['limit'])) {
            $this->setLimit($config['limit']);
        }
        if (isset($config['extra_limit'])) {
            $this->setExtraLimit($config['extra_limit']);
        }
        if (isset($config['page'])) {
            $this->setCurrentPage($config['page']);
        }
        if (isset($config['total'])) {
            $this->setTotal($config['total']);
        }
    }

    /**
     * Set the current page number
     *
     * @param int $page
     * @return \Engine\Paginator\Adapter\Grid
     */
    public function setCurrentPage($page)
    {
        $page = (int) $page;
        if ($page == 0) {
            throw new \Engine\Exception('Page incorrect');
        }
        if ($this->_page == $page) {
            return $this;
        }
        if ($this->_getExtraPage($this->_page) !== $this->_getExtraPage($page)) {
            $this->_items = null;
        }

        $this->_paginate = null;
        $this->_page = $page;

        return $this;
    }

    /**
     * Return current page
     *
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->_page;
    }

    /**
     * Set the current page limit
     *
     * @param int $limit
     * @return \Engine\Paginator\Adapter\Grid
     */
    public function setLimit($limit)
    {
        $limit = (int) $limit;
        if ($limit == 0) {
            throw new \Engine\Exception('Limit incorrect');
        }
        if ($this->_limit == $limit) {
            return $this;
        }
        $this->_items = null;
        $this->_paginate = null;
        $this->_limit = $limit;

        return $this;
    }

    /**
     * Return limit
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->_limit;
    }

    /**
     * Set extrapage limit
     *
     * @param int $limit
     * @return \Engine\Paginator\Adapter\Grid
     */
    public function setExtraLimit($limit)
    {
        $limit = (int) $limit;
        if ($limit == 0) {
            throw new \Engine\Exception('Limit incorrect');
        }
        if ($this->_extraLimit == $limit) {
            return $this;
        }
        $this->_items = null;
        $this->_paginate = null;
        $this->_extraLimit = $limit;

        return $this;
    }

    /**
     * Return extralimit
     *
     * @return int
     */
    public function getExtraLimit()
    {
        return $this->_extraLimit;
    }

    /**
     * Returns a slice of the resultset to show in the pagination
     *
     * @return array
     */
    public function getPaginate()
    {
        if ($this->_paginate) {
            return $this->_paginate;
        }

        $this->_paginate = [];
        $this->_paginate['limit'] = $this->_limit;
        $this->_paginate['total_items'] = $this->_getTotal();
        $this->_paginate['data'] = $this->_getItems();
        $this->_paginate['mess_now'] = count($this->_paginate['data']);
        $this->_paginate['page'] = $this->_page;
        $this->_paginate['pages'] = (int) ceil($this->_paginate['total_items']/$this->_limit);

        return $this->_paginate;
    }

    /**
     * Return current page items
     *
     * @return array
     */
    protected function _getItems()
    {
        $items = [];
        $this->_setItems();
        $extraPage = $this->_getExtraPage($this->_page);
        $position = $this->_getCurrentPosition();
        if ($this->_getTotal() > 0) {
            $count = $this->_items->count();
            $itemsTotal = ($position+$this->_limit < $count) ? ($position+$this->_limit) : $count;
            for ($i = $position; $i < $itemsTotal; ++$i) {
                if (!isset($this->_items[$i])) {
                    break;
                }
                $items[] = $this->_items[$i];
            }
        }

        return $items;
    }

    /**
     * Set items by extrapage
     *
     * @return void
     */
    protected function _setItems()
    {
        if ($this->_items) {
            return;
        }

        $extraPage = $this->_getExtraPage($this->_page);
        $builder = clone($this->_queryBuilder);
        $builder->limit($this->_extraLimit);
        $builder->offset(($extraPage-1)*$this->_extraLimit);
        $this->_items = $builder->getQuery()->execute();

        return;
    }

    /**
     * Return current item position
     *
     * @return int
     */
    protected function _getCurrentPosition()
    {
        return $this->_limit*($this->_page-1)-($this->_extraLimit*($this->_getExtraPage($this->_page)-1));
    }

    /**
     * Return extrapage number
     *
     * @param integer $page
     * @return int
     */
    protected function _getExtraPage($page)
    {
        return (int) ceil(($this->_limit*$page)/$this->_extraLimit);
    }

    /**
     * Set total items
     *
     * @param int $total
     * @return \Engine\Paginator\Adapter\Grid
     */
    public function setTotal($total)
    {
        $this->_total = (int) $total;

        return $this;
    }

    /**
     * Return total items
     *
     * @return int
     */
    protected function _getTotal()
    {
        if ($this->_total) {
            return $this->_total;
        }

        $builder = clone($this->_queryBuilder);
        $builder->reset(Builder::COLUMNS);
        $builder->reset(Builder::ORDER);
        $builder->reset(Builder::LIMIT_COUNT);
        $builder->reset(Builder::LIMIT_OFFSET);
        $builder->columns('COUNT(*) rowcount');

        $row = $builder->getQuery()->execute();
        $this->_total = $row[0]->rowcount;

        return $this->_total;
    }

}