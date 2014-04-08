<?php
/**
 * @namespace
 */
namespace Engine\Crud\Decorator\Grid;

use Engine\Crud\Decorator,
    Engine\Crud\Grid,
    Engine\Crud\Decorator\Helper;

/**
 * Class Extjs decorator for grid.
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Decorator
 */
class Extjs extends Decorator
{
    /**
     * Render an element
     *
     * @param  string $content
     * @return string
     * @throws \UnexpectedValueException if element or view are not registered
     */
    public function render($content = '')
    {
        $element = $this->getElement();

        $separator = $this->getSeparator();
        $helpers = $element->getHelpers();
        if (empty($helpers)) {
            $helpers = $this->getDefaultHelpers();
        }
        $attribs['id'] = $element->getId();

        foreach ($helpers as $i => $helper) {
            $helpers[$i] = Helper::factory($helper, $element);
        }

        $sections = [];
        $continue = false;
        foreach ($helpers as $i => $helper) {
            $helper['createFile'] = false;
            call_user_func_array([$helper['helper'], 'init'], [$helper['element']]);
            $endTag = call_user_func([$helper['helper'], 'endTag']);
            if (call_user_func([$helper['helper'], 'createJs'])) {
                $objectName = call_user_func([$helper['helper'], 'getName']);
                $path = call_user_func_array([$helper['helper'], 'getJsFilePath'], [$objectName]);
                $path = PUBLIC_PATH."/extjs/apps/".$path;
                if (!$this->_checkFile($path)) {
                    if ($endTag) {
                        $continue = false;
                    }
                    $helper['createFile'] = $path;
                } else {
                    if ($endTag) {
                        $continue = true;
                    }
                    continue;
                }
            }
            if ($endTag === false && $helper['createFile']) {
                if (!file_put_contents($helper['createFile'], call_user_func_array([$helper['helper'], '_'], [$helper['element']]))) {
                    throw new \Engine\Exception("File '".$helper['createFile']."' not save");
                }
            } else {
                if ($continue) {
                    continue;
                }
                if ($endTag !== '') {
                    $key = $i;
                    $sections[$key] = [];
                    $helpers[$key]['createFile'] = $helper['createFile'];
                }
                $sections[$key][] = call_user_func_array([$helper['helper'], '_'], [$helper['element']]);
            }
        }

        foreach ($sections as $key => $fileSections) {
            $elementContent = implode("", $fileSections);
            $elementContent .= call_user_func([$helpers[$key]['helper'], 'endTag']);
            if (empty($elementContent) || !file_put_contents($helpers[$key]['createFile'], $elementContent)) {
                throw new \Engine\Exception("File '".$helpers[$key]['createFile']."' not save");
            }
        }

        return;

        switch ($this->getPlacement()) {
            case self::APPEND:
                return $content . $separator . $elementContent;
            case self::PREPEND:
                return $elementContent . $separator . $content;
            default:
                return $elementContent;
        }
    }

    /**
     * Return defualt helpers
     *
     * @return array
     */
    public function getDefaultHelpers()
    {
        $helpers = [
            'extjs\Controller',
            'extjs\Window',
            'extjs\Model',
            'extjs\Store',
            'extjs\Store\Local',
            'extjs\Store\Local',
            'extjs',
            'extjs\Filter',
            'extjs\Components',
            'extjs\Columns',
            'extjs\Functions',
            'extjs\Paginator'
        ];

        return $helpers;
    }

    /**
     * Check file by path
     *
     * @param string $path
     * @return bool
     */
    protected function _checkFile($path)
    {
        $result = file_exists($path);
        if (!$result) {
            $dir = dirname($path);
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
        }
        return $result;
    }
}