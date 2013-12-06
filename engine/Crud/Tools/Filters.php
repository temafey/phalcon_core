<?php
/**
 * @namespace
 */
namespace Engine\Crud\Tools;

use Phalcon\Filter,
    Engine\Filter\FilterInterface;

/**
 * Trait Filters
 *
 * @category    Engine
 * @package     Crud
 * @subcategory Tools
 */
trait Filters
{

    /**
     * Filter
     * @var \Phalcon\Filter
     */
    protected $_filter;

    /**
     * Array of filters
     * @var array
     */
    protected $_filters = [];

    /**
     * Array of filters
     * @var array
     */
    private $_sanitize = [];

    /**
     * Initialize filters
     *
     * @return void
     */
    protected function _initFilters()
    {
        $this->_filter = new Filter();
        $this->setFilters($this->_filters);
    }

    /**
     * Returns the result of filtering $value
     *
     * @param  mixed $value
     * @return mixed
     */
    public function filter($value)
    {
        if (count($this->_sanitize) == 0) {
            return $value;
        }
        foreach ($this->_sanitize as $filter) {
            $value = $this->_filter->sanitize($value, $filter);
        }

        return $value;
    }

    /**
     * Add a filter to the element
     *
     * @param  string|\Engine\Filter\FilterInterface|array
     * @return \Engine\Crud\Tools\Filters
     */
    public function addFilter($filter)
    {
        if ($filter instanceof FilterInterface) {
            $parts = explode("\\", get_class($filter));
            $origName = strtolower(end($parts));
            $this->_filter->add($origName, $filter);
        } elseif (is_array($filter)) {
            $origName = strtolower($filter['filter']);
            if ($class = $this->getFilterClassName($origName)) {
                if (empty($filter['options'])) {
                    $filter = new $class;
                } else {
                    $r = new \ReflectionClass($class);
                    if ($r->hasMethod('__construct')) {
                        $filter = $r->newInstanceArgs((array) $filter['options']);
                    } else {
                        $filter = $r->newInstance();
                    }
                }
                $this->_filter->add($origName, $filter);
            }
        } elseif (is_string($filter)) {
            $origName = strtolower($filter);
            if ($class = $this->getFilterClassName($origName)) {
                $filter = new $class;
                $this->_filter->add($origName, $filter);
            }
        } else {
            throw new \Engine\Exception("Invalid filter passed to addFilter");
        }

        $this->_sanitize[] = $origName;

        return $this;
    }

    /**
     * Add filters to element
     *
     * @param  array $filters
     * @return \Engine\Crud\Tools\Filters
     */
    public function addFilters(array $filters)
    {
        foreach ($filters as $filter) {
            $this->addFilter($filter);
        }

        return $this;
    }

    /**
     * Add filters to element, overwriting any already existing
     *
     * @param  array $filters
     * @return \Engine\Crud\Tools\Filters
     */
    public function setFilters(array $filters)
    {
        $this->clearFilters();
        return $this->addFilters($filters);
    }

    /**
     * Get all filters
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->_sanitize;
    }

    /**
     * Remove a filter by name
     *
     * @param  string $name
     * @return \Engine\Crud\Tools\Filters
     */
    public function removeFilter($name)
    {
        if (isset($this->_filters[$name])) {
            unset($this->_filters[$name]);
        } else {
            $len = strlen($name);
            foreach (array_keys($this->_filters) as $filter) {
                if ($len > strlen($filter)) {
                    continue;
                }
                if (0 === substr_compare($filter, $name, -$len, $len, true)) {
                    unset($this->_filters[$filter]);
                    break;
                }
            }
        }

        return $this;
    }

    /**
     * Clear all filters
     *
     * @return \Engine\Crud\Tools\Filters
     */
    public function clearFilters()
    {
        $this->_filters = [];
        return $this;
    }

    /**
     * Return filter class name
     *
     * @param string $name
     * @return string
     */
    public function getFilterClassName($name)
    {
        $filter = '\Engine\Filter\\'.ucfirst($name);
        if (!class_exists($filter)) {
            return false;
        }

        return $filter;
    }
}