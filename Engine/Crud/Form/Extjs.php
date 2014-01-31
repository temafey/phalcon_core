<?php
/**
 * @namespace
 */
namespace Engine\Crud\Form;

use Engine\Crud\Form;

/**
 * Class Extjs.
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Form
 */
abstract class Extjs extends Form
{
    /**
     * Default decorator
     */
    const DEFAULT_DECORATOR = 'Extjs';

    /**
     * Content managment system module router prefix
     * @var string
     */
    protected $_modulePrefix = 'cms';

    /**
     * Extjs module name
     * @var string
     */
    protected $_module;

    /**
     * Extjs form key
     * @var string
     */
    protected $_key;

    /**
     * Get grid action
     *
     * @return string
     */
    public function getModulePrefix()
    {
        return $this->_modulePrefix;
    }
    
    /**
     * Return extjs module name
     *
     * @return string
     */
    public function getModuleName()
    {
        return $this->_module;
    }

    /**
     * Return extjs form key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->_key;
    }

    /**
     * Get form action
     *
     * @return string
     */
    public function getAction()
    {
        if (!empty($this->_action)) {
            return $this->_action;
        }
        return $this->_modulePrefix."/".$this->getModuleName()."/".$this->getKey();
    }

    /**
     * Update form rows
     *
     * @param string|array|stdClass $params
     * @param string $key
     * @return array
     */
    public static function updateRows($params, $key)
    {
        $result = [
            'success' => false,
            'error' => []
        ];

        if (is_string($params)) {
            if (!\Engine\Tools\String::isJson($params)) {
                $result['error'][] = 'Params not valid';
                return $result;
            }
            $params = json_decode($params);
        }

        if (is_array($params)) {
            if (!isset($params[$key]) && !is_array($params[$key])) {
                $result['error'][] = 'Array params not valid';
                return $result;
            }
            $rows = (!isset($rows[0])) ? [$params[$key]] : $params[$key];
        } elseif ($params instanceof \stdClass) {
            if (!isset($params->$key)) {
                $result['error'][] = 'Object params not valid';
                return $result;
            }
            $rows = (is_object($params->$key)) ? [(array) $params->$key] : $params->$key;
        } else {
            $result['error'][] = 'Params not valid';
            return $result;
        }

        $false = false;
        foreach ($rows as $row) {
            $rowResult = self::updateRow($row);
            if ($rowResult['success'] === false) {
                $false = true;
                $result['error'] = array_merge($result['error'], $rowResult['error']);
            }
        }

        if (!$false) {
            $result['success'] = true;
            $result['msg'] = "Saved";
        }

        return $result;
    }

    /**
     * Update from row
     *
     * @param string|array|stdClass $row
     * @return array
     */
    public static function updateRow($row)
    {
        $result = [
            'success' => false,
            'error' => []
        ];

        if (is_string($row)) {
            if (!\Engine\Tools\String::isJson($row)) {
                $result['error'][] = 'Params not valid';
                return $result;
            }
            $row = json_decode($row);
        }

        if ($row instanceof \stdClass) {
            $row = (array) $row;
        } elseif (!is_array($row)) {
            $result['error'][] = 'Params not valid';
            return $result;
        }

        $form = new static();
        $primary = $form->getPrimaryField();
        $primaryKey = $primary->getKey();
        if (isset($row[$primaryKey])) {
            $id = $row[$primaryKey];
            $form->loadData($id);
            unset($row[$primaryKey]);
        }
        $form->initForm();
        foreach ($row as $key => $value) {
            $form->$key = $value;
        }

        $rowResult = $form->save();
        if (is_array($rowResult)) {
            $result['error'] = array_merge($result['error'], $rowResult['error']);
        } else {
            $result['success'] = true;
            $result['msg'] = "Saved";
        }

        return $result;
    }



    /**
     * Delete rows by id values.
     *
     * @param string|array $ids
     * @return string
     */
    public static function deleteRows($params, $key)
    {
        $result = [
            'success' => false,
            'error' => []
        ];

        if (is_string($params)) {
            if (!\Engine\Tools\String::isJson($params)) {
                $result['error'][] = 'Params not valid';
                return $result;
            }
            $params = json_decode($params);
        }

        if (is_array($params)) {
            if (!isset($params[$key]) && !is_array($params[$key])) {
                $result['error'][] = 'Array params not valid';
                return $result;
            }
            $rows = (!isset($rows[0])) ? [$params[$key]] : $params[$key];
        } elseif ($params instanceof \stdClass) {
            if (!isset($params->$key)) {
                $result['error'][] = 'Object params not valid';
                return $result;
            }
            $rows = (is_object($params->$key)) ? [(array) $params->$key] : $params->$key;
        } else {
            $result['error'][] = 'Params not valid';
            return $result;
        }

        $false = false;
        $form = new static();
        $primary = $form->getPrimaryField();
        if (!$primary) {
            throw new \Engine\Exception('Primary field not found');
        }
        $primaryKey = $primary->getKey();
        foreach ($rows as $id) {
            if (is_array($id)) {
                if (!isset($id[$primaryKey])) {
                    throw new \Engine\Exception('Primary key not found in params');
                }
                $id = $id[$primaryKey];
            }
            $resultRow = $form->delete($id);
            if ($resultRow === false) {
                $false = true;
                //$result['error'] = array_merge($result['error'], $rowResult['error']);
            }
        }

        if (!$false) {
            $result['success'] = true;
            $result['msg'] = "Deleted";
        }

        return $result;
    }

    /**
     * Generate form item link from link template
     *
     * @return string
     */
    public function getLink()
    {
        if (!$this->_linkTemplate) {
            $this->_linkTemplate = "/".$this->getModuleName()."/".$this->getKey();
            if ($primary = $this->getPrimaryField()) {
                //$this->_linkTemplate .= "/{".$primary->getKey()."}";
            }
        }

        return \Engine\Tools\String::generateStringTemplate($this->_linkTemplate, $this->getData(), "{", "}");
    }
}