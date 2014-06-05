<?php
/**
 * @namespace
 */
namespace Engine\Mvc\Model;

use Phalcon\Mvc\Model\Query as PhQuery;

/**
 * Class Query
 *
 * @category    Engine
 * @package     Mvc
 * @subcategory Model
 */
class Query extends PhQuery
{
    /**
     * Implement a method that returns a string key based
     * on the query parameters
     */
    protected function _createKey($parameters)
    {
        if (!$parameters) {
            return $this->_phql;
        }
        $uniqueKey = [];
        foreach ($parameters as $key => $value) {
            if (is_scalar($value)) {
                $uniqueKey[] = $key . ':' . $value;
            } else {
                if (is_array($value)) {
                    $uniqueKey[] = $key . ':[' . self::_createKey($value) .']';
                }
            }
        }

        return $this->_phql."_".join(',', $uniqueKey);
    }

    /**
     * Executes a parsed PHQL statement
     *
     * @param array $bindParams
     * @param array $bindTypes
     * @return mixed
     */
    public function execute($bindParams=null, $bindTypes=null)
    {
        $key = $this->_createKey($bindParams);
        $this->cache(["key" => $key, "lifetime" => 300]);
        return parent::execute($bindParams, $bindTypes);
    }

}