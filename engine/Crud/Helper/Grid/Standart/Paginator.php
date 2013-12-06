<?php
/**
 * @namespace
 */
namespace Engine\Crud\Helper\Grid\Standart;

use Engine\Crud\Grid\AbstractGrid as Grid;

/**
 * Class grid datastore helper
 *
 * @category   Engine
 * @package    Crud
 * @subpackage Helper
 */
class Paginator extends \Engine\Crud\Helper\AbstactHelper
{
    /**
     * Generates a widget to show a html grid
     *
     * @param \Engine\Crud\Grid\AbstractGrid $grid
     * @return string
     */
    static public function _(Grid $grid)
    {
        $action = $grid->getAction();
        $pages = $grid->getPaginateParams();
        $sortParams = $grid->getSortParams();
        $sortPageParamName = $grid->getPageParamName();

        if ($sortParams) {
            foreach ($sortParams as $param => $value) {
                $action = self::setUrlParam($action, $param, $value);
            }
        }

        $code = "
        <div class=\"pagination\">
            <ul>";

        if ($pages['first']) {
            $link = self::setUrlParam($action, $sortPageParamName, $pages['first']);
            //$first  = '<li><a href="'.$link.'">First</a></li>';
            $first  = '<li><a href="'.$link.'"><span>&laquo;</span></a></li>';
        } else {
            //$first  = '<li><img src="/img/page_arrow_first.gif"/></li>';
            //$first  = '<li class="disabled"><a href="#">First</a></li>';
            $first  = '<li class="disabled"><a href="#"><span>&laquo;</span></a></li>';
        }
        unset($pages['first']);
        if ($pages['prev']) {
            $link   = self::setUrlParam($action, $sortPageParamName, $pages['prev']);
            //$prev   = '<li><a href="'.$link.'"><img src="/img/page_arrow_prev.gif"/></a></li>';
            $prev_w = '<li><a href="'.$link.'"><span>&#8249;</span></a></li>';
        }
        else {
            //$prev   = '<li><img src="/img/page_arrow_prev.gif"/></li>';
            //$prev_w = '<li>Prev</li>';
            $prev_w = '<li class="disabled"><a href="#"><span>&#8249;</span></a></li>';
        }
        unset($pages['prev']);
        if ($pages['next']) {
            $link   = self::setUrlParam($action, $sortPageParamName, $pages['next']);
            //$next   = '<li><a href="'.$link.'"><img src="/img/page_arrow_next.gif"/></a></li>';
            $next_w = '<li><a href="'.$link.'"><span>&#8250;</span></a></li>';
        } else {
            //$next   = '<li><img src="/img/page_arrow_next.gif"/></li>';
            $next_w = '<li class="disabled"><a href="#"><span>&#8250;</span></a></li>';
        }
        unset($pages['next']);
        if ($pages['last']) {
            $link   = self::setUrlParam($action, $sortPageParamName, $pages['last']);
            //$last   = '<li><a href="'.$link.'"><img src="/img/page_arrow_last.gif"/></a></li>';
            //$last_w = '<li><a href="'.$link.'">Last</a></li>';
            $last_w = '<li><a href="'.$link.'"><span>&raquo;</span></a></li>';
        } else {
            //$last = '<li><img src="/img/page_arrow_last.gif"/></li>';
            //$last_w =  '<li>Last</li>';
            $last_w  = '<li class="disabled"><a href="#"><span>&raquo;</span></a></li>';
        }
        unset($pages['last']);
        $code .= $first.$prev_w; //$prev_w.$first.$prev;
        foreach ($pages as $page => $status) {
            if ($status == 'now') {
                // $code .= '<li class="page_now">'.$page.'</li>';
                $code .= '<li class="active"><a href="#"><span>'.$page.'</span></a></li>';
            } else {
                $link = self::setUrlParam($action, $sortPageParamName, $page);
                $code .= '<li class="page_link"><a href="'.$link.'"><span>'.$page.'</span></a></li>';
            }
        }
        $code .= $next_w.$last_w;//$next.$last.$next_w;
        $code .= "
            </ul>
         </div>";

        return $code;
    }

    /*
     * function setUrlParam sets parameters values in URL
     * $url - URL to set parameters in
     * $paramName - array of parameters names
     *              if one parameter to set $paramName can be string
     * $paramValue - array of parameters values
     *               if one parameter to set $paramValue can be string
     * $paramName and $paramValue must be same size arrays!
     *
     * if not set $paramValue - $paramName must be
     * array of names and values: array(name1=>value1, name2=>value2)
     */
    static function setUrlParam($url, $paramName, $paramValue = null, $urlDecode = false)
    {
        if (!is_array($paramName)){
            $paramName = [$paramName];
        }
        if ($paramValue !== null){
            if (!is_array($paramValue)) {
                $paramValue = array($paramValue);
            }
            if (($paramsArray = array_combine($paramName, $paramValue)) === false) {
                return $url;
            }
        } else {
            $paramsArray = $paramName;
        }
        $parse_url = parse_url($url);
        $url = '';
        if (isset($parse_url['scheme']) && isset($parse_url['host'])) {
            $url .= $parse_url['scheme'].'://'.$parse_url['host'].$parse_url['path'];
        }
        if (isset($parse_url['path'])) {
            $url .= $parse_url['path'];
        }
        $parse_str = [];
        if (isset($parse_url['query'])) {
            $parse_str = self::parseStr($parse_url['query']);
        }
        $parse_str = array_merge($parse_str, $paramsArray);
        $query = '';
        if ($query = http_build_query($parse_str)){
            $url .= '?'.$query;
        }
        if ($urlDecode) {
            $url = urldecode($url);
        }

        return $url;
    }

    /**
     * Clear query params from url
     *
     * @param $url
     * @param $clearArray
     * @return string
     */
    static function clearUrlParam($url, $clearArray)
    {
        $parse_url = parse_url($url);
        $url = $parse_url['scheme'].'://'.$parse_url['host'].$parse_url['path'];
        $parse_str = self::parseStr($parse_url['query']);
        foreach ($clearArray as $paramName){
            unset($parse_str[$paramName]);
        }
        $query = '';
        if ($query = http_build_query($parse_str)){
            $url .= '?'.$query;
        }

        return $url;
    }

    /**
     * @param $urlParamStr
     * @return array
     */
    static function parseStr($urlParamStr)
    {
        $paramArr = explode('&', $urlParamStr);
        $return = array();
        foreach ($paramArr as $param) {
            $tmp = explode('=', $param);
            if ($tmp[0])
                $return[$tmp[0]] = $tmp[1];
        }
        return $return;
    }
}