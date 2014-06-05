<?php
/**
 * @namespace
 */
namespace Engine\Tools;
	
/**
 * Class Arrays
 *
 * @category   Engine
 * @package    Tools
 */
class Arrays 
{
	/**
	 * Convert linear array to assoc array
	 *
	 * @param array $array
	 * @param string $display
	 * @param string $key
	 * @param string $empty
	 * @return array
	 */
	static function arrayToAssoc($array, $display = 'name', $key = 'id', $empty = '') 
	{
		$arrReturn = [];
		if ($empty) {
			$arrReturn[0] = $empty;
		}

		foreach ($array as $k => &$v) {
			if (is_array($v) && !empty($v[$key])) {
				if ($display && isset($v[$display])) {
					$arrReturn[$v[$key]] = $v[$display];
				} else {
					$arrReturn[$v[$key]] = $v[$key];
				}
					
			} else {
				//$arrReturn[$k]=$v;
			}
		}

		return $arrReturn;
	}

    /**
     * Convert linear array to assoc array
     *
     * @param array $array
     * @param string $key
     * @param string $value
     * @return array
     */
    static function assocToArray($array, $key = false, $value = false)
    {
        $result = [];
        foreach ($array as $k => $v) {
            $subValue = ($key && $value) ? [$key => $k, $value => $v] : [$k, $v];
            $result[] = $subValue;
        }

        return $result;
    }

    /**
     * Convert linear array to assoc array
     *
     * @param array $array
     * @param string $key
     * @param string $value
     * @return array
     */
    static function resultArrayToJsonType($array, $key = 'id', $value = 'name')
    {
        $result = [];
        foreach ($array as $val) {
            $subValue = [$key => $val[$key], $value => $val[$value]];
            $result[] = $subValue;
        }

        return $result;
    }

    /**
     * Generate linear array from assoc array
     * 
     * @param array $array
     * @param string $key
     * @return array
     */
    static function assocToLinearArray(array $array, $key = 'id') 
	{
		$res =[];
		foreach ($array as &$v) {
			$res[] = $v[$key];
		}
        
		return $res;
	}

	/**
	 * Convert more then two levels array to linear array
	 *
	 * @param array $array входной масив
	 * @param string $prefix префикс для ключей
	 * @return array
	 */
	static function arrayToLinearArray(array $array, $prefix = "") 
	{
		$res =[];
		foreach ($array as $k => &$v) {
			if (is_array($v)) {
				$a = self::arrayToLinearArray($v, $prefix);
				$res = array_merge($res, $a);
			} else {
				$res[$prefix.$k] = $v;
			}
		}
		return $res;
	}

    /**
     * 
     * 
     * @param array $array
     * @param string $key
     * @param string $name
     * @return array
     */
    static function accessArrayToLinearArray(array $array, $key = "key", $name = "name") 
	{
		$res =[];
		foreach ($array as $v) {
			if (!empty($v[$key]) && !empty($v[$name])) {
				$res[$v[$key]] = $v[$name];
			}
			if (!empty($v['children']) && is_array($v['children'])) {
				$a = self::accessArrayToLinearArray($v['children'], $key, $name);
				$res = array_merge($res, $a);
			}
		}
        
		return $res;
	}

    /**
     * @param $post
     * @param $array
     * @param string $key
     * @return array
     */
    static function setAccessArrayFromTree($post, $array, $key = "key") 
	{
		$res =[];
		foreach ($array as &$v) {				
			if (!empty($post[$v[$key]]) || !empty($v['inherit'])) {
				if (!empty($v[$key])) {
					$res[$v[$key]] = 1;
				}
				if (!empty($v['children']) && is_array($v['children'])) {						
					$a = self::setAccessArrayFromTree($post, $v['children'], $key);
					$res = array_merge($res, $a);
				}
			}
		}

		return $res;
	}

    /**
     * @param $tree
     * @param $access
     * @return array
     */
    static function filterAccessTree($tree, $access)
	{
		$res = [];
		foreach ($tree as $k => &$v) {
			if (!isset($v['access']) || !empty($access[$v['access']])) {
				$p = $v;

				if (isset($p['children'])) {
					unset($p['children']);
				}

				if (!empty($v['children']) && is_array($v['children'])) {
						
					$a = self::filterAccessTree($v['children'], $access);
					if (!empty($a)) {
						$p['children'] = $a;
					}
				}
				$res[] = $p;
			}
		}

		return $res;
	}

    /**
     * @param $tree
     * @return array
     */
    static function filterInheritedAccessTree($tree)
	{
		$res = [];
		foreach ($tree as $k => &$v) {
			if (empty($v['inherit'])) {
				$p = $v;
				if (isset($p['children'])) {
					unset($p['children']);
				}
				if (!empty($v['children']) && is_array($v['children'])) {
						
					$a = self::filterInheritedAccessTree($v['children']);
					if (!empty($a)) {
						$p['children'] = $a;
					}
				}
				$res[] = $p;
			}
		}

		return $res;
	}

    /**
     * @param $multiarray
     * @return bool
     */
    static function isMultiArray($multiarray)
	{
		if (is_array($multiarray)) { // confirms array
			foreach ($multiarray as &$array) { // goes one level deeper
				if (is_array($array)) { // is subarray an array
					return true; // return will stop function
				} // end 2nd check
			} // end loop
		} // end 1st check

		return false; // not a multiarray if this far
	}

    /**
     * @param $needle
     * @param $haystack
     * @param bool $strict
     * @param array $path
     * @param bool $key_search
     * @param bool $new_value
     * @return array|bool
     */
    static function arraySearchRecursive($needle, &$haystack, $strict = false, $path = [] , $key_search = false, $new_value = false)
	{
		if ( !is_array($haystack)) {
			return false;
		}
		foreach ( $haystack as $key => &$val) {
			if ( is_array($val) && $subPath = self::arraySearchRecursive($needle, $haystack[$key], $strict, $path, $key_search, $new_value)) {
				if ($key_search) {
					return $subPath;
				}
				//elseif ($key_search && $new_value)
				//return $haystack[$key] = $subPath;
				else {
					$path = array_merge($path, array($key), $subPath);
				}				
				return $path;
			} else {
				if ($key_search == true) {
					$value = $val;
					$val = $key;
				}
				if ((!$strict && $val === $needle) ||($strict && $val === $needle)) {
					$path[] = $key;
					if ($key_search && $new_value) {
						$haystack[$needle] = $new_value;
					}
					return($key_search) ? $haystack[$needle] : $path;
				}
			}
		}
		
		return false;
	}
	
	static function joinr($join, $value, $lvl=0)
    {
        if (!is_array($join)) return self::joinr(array($join), $value, $lvl);
        $res = [];
        if (is_array($value) && sizeof($value) && is_array(current($value))) { // Is value are array of sub-arrays?
            foreach ($value as &$val) {
                $res[] = self::joinr($join, $val, $lvl+1);
            }
        } elseif (is_array($value)) {
            $res = $value;
        } else {
        	$res[] = $value;
        }

        return join(isset($join[$lvl])?$join[$lvl]:"", $res);
    }
	
    static function mt_implode($char,$array,$fix='',$addslashes=false)
	{
	    $lem = array_keys($array);
	    $char = htmlentities($char);
        $str = '';
	    for ($i=0; $i < sizeof($lem); $i++) {
	      if ($addslashes) {
	        $str .= $fix.(($i == sizeof($lem)-1) ? addslashes($array[$lem[$i]]).$fix : addslashes($array[$lem[$i]]).$fix.$char);
	      } else {
	        $str .= $fix.(($i == sizeof($lem)-1) ? $array[$lem[$i]].$fix : $array[$lem[$i]].$fix.$char);
	      }
	    }

	    return $str;
	}
    
	/**
	 * Генерируеться все возможные варианты из многомерного массива, массив должен иметь квадратную структуру.
	 * 
	 * @param array $arrays
	 * @param bool $sub_variants
	 * @param bool $inverted
	 */
    static function getAllValuesVariantsFromArrays(array $arrays, $sub_variants = false, $inverted = false)
    {
        $arrays = self::fixVariantArray($arrays);
    	$results = [];

    	if (count($arrays) == 1) {
            return $arrays;
        }

    	if (count($arrays) == 2) {
    		if ($sub_variants){    			
	    		$result = self::getValuesVariantsFrom2Array($arrays[0], false);
	    		$results = array_merge($results, $result);
    			$result = self::getValuesVariantsFrom2Array($arrays[1], false);
    			$results = array_merge($results, $result);
    		}
    		$result = self::getValuesVariantsFrom2Array($arrays[0], $arrays[1]);
    		$results = array_merge($results, $result);
    		
    		return $results;
    	}
    	
    	foreach ($arrays as $x => $array1) {
    		$d_array = [];
	    	foreach ($arrays as $y => $array2){
	    		if ($inverted === false && $y < $x) {
	    			continue;
	    		}
	    		if ($x == $y){ 
	    			if ($sub_variants) {
	    				$array2 = false;
	    			} else { 
	    				continue;
	    			}
	    		}
	    		if ($sub_variants === false &&($y-1) > $x) {
	    			continue;
	    		}
	    		$result = self::getValuesVariantsFrom2Array($array1, $array2);
	    		//$d_array[] = array_merge($d_array, $result);
	    		$d_array[] = $result;
	    	}
	    	if ($sub_variants) {
	    		foreach ($d_array as &$array){
	    			$results = array_merge($results, $array);
	    		}
	    	}
	    	$t_array =($sub_variants) ? $d_array[1] : $d_array[0];
	    	for ($i=$x+2; $i < count($arrays); $i++){
	    		$t_array = self::getValuesVariantsFrom2Array($t_array, $arrays[$i]);
	    		if (!$sub_variants &&($i+1 < count($arrays))) {
	    			continue;
	    		}
	    		$results = array_merge($results, $t_array);
	    	}
	    	if ($sub_variants === false) {
	    		return $results;
	    	}
    	}
    	
    	return $results;
    }
    
    /**
     * Check array rank.
     * 
     * @param array $arrays
     * @return bool
     */
    static function fixVariantArray(array $arrays)
    {
        foreach ($arrays as $k => $array) {
            if (!is_array($array)) {
                $arrays[$k] = array($array);
            }
        }
        
        return $arrays;
    }

    /**
     * @param array $array1
     * @param bool $array2
     * @return array
     */
    static function getValuesVariantsFrom2Array(array $array1, $array2 = false)
    {
    	$results = [];
    	if (!is_array($array1)) {
    	    $array1 = array( $array1);
    	}
        if (!is_array($array2) && $array2 !== false) {
    	    $array2 = array( $array2);
    	}
    	foreach ($array1 as $value1){
    		if (!is_array($value1)){
    			$value1 = array($value1);
    		}
    		if ($array2 === false){
    			$results[] = $value1;
    			continue;
    		}
    		foreach ($array2 as $value2){
	    		if (!is_array($value2)){
	    			$value2 = array($value2);
	    		}
	    		$results[] = array_merge($value1, $value2);
    		}
    	}
    	
    	return $results;
    }
    
	/**
	 * Shorten an multidimensional array into a single dimensional array concatenating all keys with separator.
	 *
	 * @example array('country' => array(0 => array('name' => 'Bangladesh', 'capital' => 'Dhaka')))
	 *          to array('country.0.name' => 'Bangladesh', 'country.0.capital' => 'Dhaka')
	 *
	 * @param array $inputArray, arrays to be marged into a single dimensional array
	 * @param string $path, Default Initial path
	 * @param string $separator, array key path separator
	 * @return array, single dimensional array with key and value pair
	 * @access public
	 * @static
	 */
	static public function shorten(array $inputArray, $path = null, $separator = "_"){
	   $data = [];
	   if (!is_null($path)) {
	      $path = $path . $separator;
	   }
	
	   if (is_array($inputArray)) {
	      foreach ($inputArray as $key => &$value) {
	         if (!is_array($value)) {
	            $data[$path . $key] = $value;
	         } else {
	            $data = array_merge($data, self::shorten($value, $path . $key, $separator));
	         }
	      }
	   }
	
	   return $data;
	}
	
	/**
	 * Unshorten a single dimensional array into multidimensional array.
	 *
	 * @example array('country.0.name' => 'Bangladesh', 'country.0.capital' => 'Dhaka')
	 *          to array('country' => array(0 => array('name' => 'Bangladesh', 'capital' => 'Dhaka')))
	 *
	 * @param array $data data to be converted into multidimensional array
	 * @param string $separator key path separator
	 * @return array multi dimensional array
	 * @access public
	 * @static
	 */
	static public function unshorten($data, $separator = '_'){
	   $result = [];
	
	   foreach ($data as $key => $value){
	      if (strpos($key, $separator) !== false){
	         $str = explode($separator, $key, 2);
	         $result[$str[0]][$str[1]] = $value;
	         if (strpos($str[1], $separator)){
	            $result[$str[0]] = self::unshorten($result[$str[0]], $separator);
	         }
	      }else{
	         $result[$key] = is_array($value)?  self::unshorten($value, $separator) : $value;
	      }
	   }
	   return $result;
	}
	
	/**
	 * Get part of array from a multidimensional array specified by concatenated keys path.
	 *
	 * @example
	 *          path = "0.name"
	 *          data =
	 *          array(
	 *                  array('name' => array('Bangladesh', 'Srilanka', 'India', 'Pakistan')),
	 *                  array('help' => 'help.php'),
	 *                  'test' => 'value',
	 *                  10 =>
	 *          false)
	 *          will return array('Bangladesh', 'Srilanka', 'India', 'Pakistan')
	 * @param string $path
	 * @param array $data
	 * @param string $separator path separator default '.'
	 * @return mixed and return NULL if not found.
	 * @access public
	 * @static
	 */
	static public function subarray($path, &$data, $separator = '_') {
	   if (strpos($path, $separator) === false) {
	      if (isset($data[$path])) {
	         return $data[$path];
	      }
	   } else {
	      $keys = explode($separator, $path, 2);
	      if (array_key_exists($keys[0], $data)) {
	         return self::subarray($keys[1], $data[$keys[0]], $separator);
	      }
	   }
	}
	
	/**
	 * Create multi array tree structure from linear array.
	 * 
	 * @param array $rows
	 * @param string $parent
	 * @param string $id
	 * @return array
	 */
	static function createTree($rows, $parent = 'parent_id', $id = 'id') 
	{
	    $rs = [];
        foreach ($rows as $row) {
            $rs[$row[$parent]][]=$row;
        }
        return self::recursiveTree($rs, 0, $id);
	}
	
	/**
	 * 
	 * 
	 * @param array $rs
	 * @param int $parent
	 * @param string $id
	 * @return array
	 */
    static protected function recursiveTree(&$rs, $parent = 0, $id = 'id')
    {
        $out = [];
        if (!isset($rs[$parent])) {
            return $out;
        }
        
        foreach ($rs[$parent] as $row) {            
            $row['children' ] = self::recursiveTree($rs, $row[$id]);            
            $out[$row[$id]] = $row;
        }
        
        return $out;
    }
}