<?php
/**
 * @namespace
 */
namespace Engine\Tools;
	
/**
 * Class resource.
 *
 * @category   Engine
 * @package    Tools
 */
class String 
{
    /**
     * Imrove strip_tags
     *
     * @param $i_html
     * @param array $i_allowedtags
     * @param bool $i_trimtext
     * @returnstring
     */
    static function realStripTags($i_html, $i_allowedtags = [], $i_trimtext = FALSE)
	{
        if (!is_array($i_allowedtags)) {
            $i_allowedtags = !empty($i_allowedtags) ? [$i_allowedtags] : [];
        }
        $tags = implode('|', $i_allowedtags);

        if (empty($tags)) {
            $tags = '[a-z]+';
        }
        preg_match_all('@</?\s*('.$tags.')(\s+[a-z_]+=(\'[^\']+\'|"[^"]+"))*\s*/?>@i', $i_html, $matches);

        $full_tags = $matches[0];
        $tag_names = $matches[1];

        foreach ($full_tags as $i => $full_tag) {
            if (!in_array($tag_names[$i], $i_allowedtags)) {
                if ($i_trimtext) {
                    unset($full_tags[$i]);
                } else {
                    $i_html = str_replace($full_tag, '', $i_html);
                }
            }
        }
	
        return $i_trimtext ? implode('', $full_tags) : $i_html;
	}
	
	/**
	 * Convert string value to utf-8.
	 * 
	 * @param string $str
	 * @return string
	 */
	static function convToUtf8($str)
	{
	    $encoding = self::detect_encoding($str);
		if ($encoding != "UTF-8") {
			return iconv($encoding, "UTF-8", $str);		
		} else {
			return $str;
		}
	}
	
	/**
	 * Convert string value to cp1251.
	 * 
	 * @param string $str
	 * @return string
	 */
	static function convToWin($str)
	{		
	    $encoding = self::detect_encoding($str);
		if ($encoding != "cp1251") {
			return iconv($encoding, "cp1251", $str);
		} else {
			return $str;
		}
	}
	
	/**
	 * Convert string to url.
	 * 
	 * @param string $string
	 * @return string
	 */
	static function stringToUrl($string)
	{
		$pattern = '[^\w\s]+';
		$replacement = '';
		$url = mb_ereg_replace($pattern, $replacement, $string);
		$not_allowed = array(" ");
		$url = strtolower(str_replace($not_allowed, "-", $url));
		
		return $url;
	}
	
	/**
	 * Make a string's first character uppercase
	 * 
	 * @param string $str
	 * @param encoding type $encoding
	 * @param bool $lower_str_end
	 * @return string
	 */
    static function mb_ucfirst($str, $encoding = "UTF-8", $lower_str_end = false) 
    {
        $first_letter = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding);
        $str_end = "";
        if ($lower_str_end) {
            $str_end = mb_strtolower(mb_substr($str, 1, mb_strlen($str, $encoding), $encoding), $encoding);
        } else {
            $str_end = mb_substr($str, 1, mb_strlen($str, $encoding), $encoding);
        }
        $str = $first_letter . $str_end;

        return $str;
    }
	
	/**
	 * Detect string encoding format. 
	 * 
	 * @param string $string
	 * @return string
	 */
    static function detect_encoding($string) 
	{ 
        static $list = ['Windows-1251', 'UTF-8', 'ISO-8859-1', 'GBK', 'cp1251'];

        foreach ($list as $item) {
            $sample = iconv($item, $item, $string);
            if (md5($sample) == md5($string)) {
                return $item;
            }
        }

        return null;
	}

    /**
     * Build and return where sql condition with
     *
     * @param array|string $params
     * @param string $paramName
     * @param string $alias
     * @param string $condition
     * @param string $conditionIn
     * @return bool|string
     * @throws \Engine\Exception
     *
     * @return string
     */
    static function processWhereParam($params, $paramName, $condition = "=", $alias = null, $conditionForArray = "IN")
    {
        $where = false;
        $condition = trim($condition);
        if ($condition == "!=") {
            $conditionIn = "NOT IN";
        } elseif ($condition != "=") {
            $conditionForArray = false;
        }
        if (is_array($params)) {
            if (isset($params[$paramName])) {
                $where = static::processWhereParam($params[$paramName], $paramName, $condition, $alias, $conditionForArray);
            } else {
                if (!$conditionForArray) {
                    $where = [];
                    foreach ($params as $param) {
                        $where[] = static::processWhereParam($param, $paramName, $condition, $alias, $conditionForArray);
                    }
                    if (!empty($param)) {
                        $where = "(".implode(" OR ", $where).")";
                    }
                } else {
                    $where = "`".$alias."`.`".$paramName."` ".$conditionForArray." (" . static::quote($params).")";
                }
            }
        } else {
            $where = "`".$alias."`.`".$paramName."` ".$condition." ".static::quote($params);
        }

        return $where;
    }
	
	/**
	 * Quoting string
	 * 
	 * @param string $value
	 * @param int $type
	 * @return string
	 */
	static function quote($value, $type = null) 
	{
		if (is_array($value)) {
			foreach ($value as &$val) {
				$val = self::quote($val, $type);
			}
			return implode(', ', $value);
		}

		if ($type !== null) {
			switch ($type) {
				case 0 : // 32-bit integer
					return (string) intval($value);
					break;
				case 1 : // 64-bit integer
					// ANSI SQL-style hex literals(e.g. x'[\dA-F]+')
					// are not supported here, because these are string
					// literals, not numeric literals.
					if (preg_match('/^(
                          [+-]?                  # optional sign
                         (?:
                            0[Xx][\da-fA-F]+     # ODBC-style hexadecimal
                            |\d+                 # decimal or octal, or MySQL ZEROFILL decimal
                           (?:[eE][+-]?\d+)?    # optional exponent on decimals or octals
                         )
                       )/x',(string) $value, $matches)
                    ) {
					    return $matches [1];
                    }
                    break;
				case 2 : // float or decimal
					return (string) floatval($value);
					break;
			}
			return '0';
		}
        if (is_object($value)) {
            throw new \Engine\Exception('Value data type incorrect');
        }
        if (is_numeric($value)) {
            $value = (int) $value;
        }
		if (is_int($value) || is_float($value)) {
			return $value;
		}

		return "'" . addcslashes($value, "\000\n\r\\'\"\032")."'";
	}

	/**
	 * Convert latin string to translit.
	 * 
	 * @param string $str
	 * @param int $length
	 * @return string
	 */
	static function convertString($str, $length = 256)
	{
		$Letters = [];
		$Letters ["а"] = "a";
		$Letters ["б"] = "b";
		$Letters ["в"] = "v";
		$Letters ["г"] = "g";
		$Letters ["д"] = "d";
		$Letters ["е"] = "e";
		$Letters ["ё"] = "e";
		$Letters ["ж"] = "zh";
		$Letters ["з"] = "z";
		$Letters ["и"] = "i";
		$Letters ["й"] = "y";
		$Letters ["к"] = "k";
		$Letters ["л"] = "l";
		$Letters ["м"] = "m";
		$Letters ["н"] = "n";
		$Letters ["о"] = "o";
		$Letters ["п"] = "p";
		$Letters ["р"] = "r";
		$Letters ["с"] = "s";
		$Letters ["т"] = "t";
		$Letters ["у"] = "u";
		$Letters ["ф"] = "f";
		$Letters ["х"] = "h";
		$Letters ["ц"] = "c";
		$Letters ["ч"] = "ch";
		$Letters ["ш"] = "sh";
		$Letters ["щ"] = "sch";
		$Letters ["ъ"] = "";
		$Letters ["ы"] = "y";
		$Letters ["ь"] = "";
		$Letters ["э"] = "e";
		$Letters ["ю"] = "yu";
		$Letters ["я"] = "ya";
		$Letters ["А"] = "a";
		$Letters ["Б"] = "b";
		$Letters ["В"] = "v";
		$Letters ["Г"] = "g";
		$Letters ["Д"] = "d";
		$Letters ["Е"] = "e";
		$Letters ["Ё"] = "e";
		$Letters ["Ж"] = "zh";
		$Letters ["З"] = "z";
		$Letters ["И"] = "i";
		$Letters ["Й"] = "y";
		$Letters ["К"] = "k";
		$Letters ["Л"] = "l";
		$Letters ["М"] = "m";
		$Letters ["Н"] = "n";
		$Letters ["О"] = "o";
		$Letters ["П"] = "p";
		$Letters ["Р"] = "r";
		$Letters ["С"] = "s";
		$Letters ["Т"] = "t";
		$Letters ["У"] = "u";
		$Letters ["Ф"] = "f";
		$Letters ["Х"] = "h";
		$Letters ["Ц"] = "c";
		$Letters ["Ч"] = "ch";
		$Letters ["Ш"] = "sh";
		$Letters ["Щ"] = "sch";
		$Letters ["Ъ"] = "";
		$Letters ["Ы"] = "y";
		$Letters ["Ь"] = "";
		$Letters ["Э"] = "e";
		$Letters ["Ю"] = "yu";
		$Letters ["Я"] = "ya";
		//$Letters[" "] = "_";
		$Letters [" "] = "-";

		/* знаки припенания */
		$Letters [","] = "";
		$Letters [";"] = "";
		$Letters [":"] = "";
		$Letters ["."] = "";
		$Letters ["!"] = "";
		$Letters ["?"] = "";
		//$Letters["-"] = "_";


		/* спецсимволы */
		$Letters ["`"] = "";
		$Letters ["\""] = "";
		$Letters ["'"] = "";
		$Letters ["%"] = "";
		$Letters ["&"] = "";
		$Letters ["$"] = "";
		$Letters ["#"] = "";
		$Letters ["/"] = "";
		$Letters ["\\"] = "";

		/* немецкий */
		$Letters ["a"] = "a";
		$Letters ["o"] = "o";
		$Letters ["?"] = "b";
		$Letters ["u"] = "u";

		$new_str = "";
		//$str = @mb_strtolower($str,"UTF8");
		$str = @strtolower($str);

		foreach ($Letters as $key => $value) {
			$str = str_replace($key, $value, $str);
		}
		$new_str = substr($str, 0, $length);
		return $new_str;
	}
	
	/**
	 * Convert latin string to translit.
	 * 
	 * @param string $string
	 * @return string
	 */
	static function rus2translit($string) 
	{
		$converter = array('а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ь' => '\'', 'ы' => 'y', 'ъ' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',

		'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I', 'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C', 'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch', 'Ь' => '\'', 'Ы' => 'Y', 'Ъ' => '', 'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya');
		return strtr($string, $converter);
	}

	/**
	 * Convert translit string to latin.
	 * 
	 * @param string $string
	 * @return string
	 */
	static function translit2rus($text,$type='de')
	{
        $data=explode(" ",$text);
        if (count($data)=='') {
            return '';
        }
        $alphas = [
            'Sc' => 'Сц','Ai'=>'Ай','Yii'=>'Ы','Jа'=>'я','Yo'=>'Ё','Ya'=>'Я','Shc'=>'Щ','Sh'=>'Ш','Ea'=>'И','Ii'=>'Й','Zh'=>'Ж','Ch'=>'Ч',
            'Iy'=>'Ю','Ts'=>'Ц','U'=>'У','W'=>'В','V'=>'В','I'=>'И','Y'=>'и','D'=>'Д','T'=>'Т','B'=>'Б','P'=>'П',
            'N'=>'Н','F'=>'Ф','Z'=>'З','L'=>'Л','K'=>'К','S'=>'С','M'=>'М','R'=>'Р','S'=>'С',
            'H'=>'Х','J'=>'Ж','G'=>'Г','A'=>'А','X' => 'Кс',
            '' => '',
            'sc' => 'сц','yu' => 'ю','ey' => 'и','ck' => 'к','ee' => 'и','oo' => 'у', 'ai'=>'ай','yii'=>'ы','jа'=>'я','yo'=>'ё','ya'=>'я',
            'shc'=>'щ','sh'=>'ш','ea'=>'и','ii'=>'й','zh'=>'ж','ch'=>'ч',
            'iy'=>'ю','ts'=>'ц','u'=>'у','w'=>'в','v'=>'в','i'=>'и','y'=>'и','d'=>'д','t'=>'т','b'=>'б','p'=>'п',
            'n'=>'н','f'=>'ф','\''=>'ь','\''=>'ъ','z'=>'з','l'=>'л','k'=>'к','s'=>'с','m'=>'м','r'=>'р','s'=>'с',
            'h'=>'х','j'=>'ж','g'=>'г','_'=>'','a'=>'а','q' => 'к','x'=>'кс'
        ];

        $total='';
        foreach ($data as $k => $v) {
            if (preg_match("/^[a-zA-Z]*/",$v)) {
                foreach ($alphas as $id=>$value) {
                    if ($type=='de') {
                        if (strcasecmp($v,$id) AND !eregi("->",$v)) {
                            $v=str_replace($id,$value,$v);
                        } elseif (eregi("->",$v)) {
                            $v=str_replace("->","",$v);
                        }
                    } elseif ($type='translit') {
                        if (strcasecmp($v,$value) AND !eregi("->",$v)) {
                            $v=str_replace($value,$id,$v);
                        } elseif (eregi("->",$v)) {
                            $v=str_replace("->","",$v);
                        }
                    }
                }
            }
            $total.=$v." ";
        }

        return $total;
	} 

	/**
	 * Generate string value from template string and keys-values array.
	 * 
	 * @param string $template
	 * @param array  $values
	 * @param string $startDelimeter
	 * @param string $endDelimeter
	 * @return string
	 */
	public static function generateStringTemplate($template , $values, $startDelimeter = '{{', $endDelimeter = '}}')
	{
	    $start = 0;
		while (($start = strpos($template, $startDelimeter, $start)) !== false &&($end = strpos($template, $endDelimeter, $start)) !== false) {
			$key = substr($template, $start + strlen($startDelimeter) , $end - $start - strlen($endDelimeter));
			if (isset($values[$key])) {
				$value = $values[$key];
				$template = str_replace($startDelimeter . $key . $endDelimeter , $value, $template);
			} else {
				$start++;
			}
		}

		return $template;
	}

	/**
	 * Generate link by hostname, href, title, description and href attributes templates.
	 * $host "http://www.yoursite.com"
	 * $templateHref "{module}/{controller}/{action}"
	 * $templateTitle "{title} - {date}"
	 * $templateDesc " - {description}"
	 * 
	 * @param string $host
	 * @param array $data
	 * @param string $templateHref
	 * @param string $templateTitle
	 * @param string $templateDesc
	 * @param string $templateClass
	 * @param string $templateId
	 * @param string $templateHrefTitle
	 * @param string $target
	 * @param string|array $attribs
	 * @return string 
	 */
	static function generateLink(
        $host,
        $data,
        $templateHref,
        $templateTitle,
        $templateDesc = null,
        $templateClass = null,
        $templateId = null,
        $templateHrefTitle = null,
        $target = null,
        $attribs = null
   ) {
	    $link = '<a href="';
	    $p = strlen($host)-1;
	    if ($host[$p] == '/') {
	        $host = substr($host, 0, $p);
	    }
	    $link .= $host."/";
	    
	    if ($templateHref[0] == '/') {
	        $templateHref = substr($templateHref, 1);
	    }
	    $link .= self::generateStringTemplate($templateHref, $data, '{', '}');
	    $link .= '"';
	    
	    if (null !== $templateClass) {
	        $link .= ' class="';
	        $link .= self::generateStringTemplate($templateClass, $data, '{', '}');
	        $link .= '"';
	    }
	    
	    if (null !== $templateId) {
	        $link .= ' id="';
	        $link .= self::generateStringTemplate($templateId, $data, '{', '}');
	        $link .= '"';
	    }
	    
	    if (null !== $templateHrefTitle) {
	        $link .= ' title="';
	        $link .= self::generateStringTemplate($templateHrefTitle, $data, '{', '}');
	        $link .= '"';
	    }
	    
	    if (null !== $target) {
	        $link .= ' target="';
	        $link .= $target;
	        $link .= '"';
	    }
	    
	    if (null !== $attribs) {
	        if (!is_array($attribs)) {
	            $attribs = array($attribs);
	        }
	        
	        foreach ($attribs as $name => $template) {
	            $link .= ' '.$name.'="';
    	        $link .= self::generateStringTemplate($template, $data, '{', '}');
    	        $link .= '"';
	        }	        
	    }
	    
	    $link .= '>';
	    $link .= self::generateStringTemplate($templateTitle, $data, '{', '}');
	    $link .= '</a>';
	    
	    if (null !== $templateDesc) {
	        $link .= self::generateStringTemplate($templateDesc, $data, '{', '}');
	    }
	    
	    return $link;
	}
	
	/*****************************************************************
    This approach uses detection of NUL(chr(00)) and end line(chr(13))
    to decide where the text is:
    - divide the file contents up by chr(13)
    - reject any slices containing a NUL
    - stitch the rest together again
    - clean up with a regular expression
    *****************************************************************/
    
    public static function parseWord($userDocPath) 
    {
        $fileHandle = fopen($userDocPath, "r");
        $line = @fread($fileHandle, filesize($userDocPath));
        fclose($fileHandle);
        
        $lines = explode(chr(0x0D),$line);
        $outtext = "";
        foreach ($lines as $thisline) {
            $pos = strpos($thisline, chr(0x00));
            if (($pos !== FALSE)||(strlen($thisline)==0)) {

            } else {
                $outtext .= $thisline." ";
            }
        }
        $outtext = preg_replace("/[^а-яА-Яa-zA-Z0-9\s\,\.\-\n\r\t@\/\_\(\)]/","",$outtext);
        
        return $outtext;
    }

    /**
     * Remove special chars in string
     *
     * @param array|string $var
     * @return array|string
     */
    public static function formSpecialChars($var)
    {
        $pattern = '/&(#)?[a-zA-Z0-9]{0,};/';
       
        if (is_array($var)) {    // If variable is an array
            $out = [];      // Set output as an array
            foreach ($var as $key => $v) {
                // Run formSpecialChars on every element of the array and return the result. Also maintains the keys.
                $out[$key] = self::formSpecialChars($v);
            }
        } else {
            $out = $var;
            $out = urldecode($out);
            while (preg_match($pattern, $out) > 0) {
                $out = htmlspecialchars_decode($out, ENT_COMPAT);
            }                            
            $out = htmlspecialchars(stripslashes(trim($out)), ENT_COMPAT,'UTF-8', true);     // Trim the variable, strip all slashes, and encode it
           
        }
       
        return $out;
    }

    /**
     * Validate json string
     *
     * @param $string
     * @return bool
     */
    public static function isJson($string)
    {
        json_decode($string);
        $message = json_last_error();
        switch ($message) {
            case JSON_ERROR_NONE:
                $error = ''; // JSON is valid
                break;
            case JSON_ERROR_DEPTH:
                $error = 'Maximum stack depth exceeded.';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $error = 'Underflow or the modes mismatch.';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error = 'Unexpected control character found.';
                break;
            case JSON_ERROR_SYNTAX:
                $error = 'Syntax error, malformed JSON.';
                break;
            // only PHP 5.3+
            case JSON_ERROR_UTF8:
                $error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
                break;
            default:
                $error = 'Unknown JSON error occured.';
                break;
        }

        return ($message == JSON_ERROR_NONE);
    }


}