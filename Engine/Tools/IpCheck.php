<?php
/**
 * @namespace
 */
namespace Engine\Tools;
	
/**
 * Class Crypt
 *
 * @category   Engine
 * @package    Crypt
 */
class IpCheck 
{
	protected $_model;
	protected $_errors;
	protected $_db_flag;
	
	protected $_ip;
	protected $_ipArr;
	protected $_ipValue;
	
	protected $_data = ['allow' => [], 'deny' => []];
	
	protected $_allow = [];
	protected $_deny  = [];
	
	protected $_count = 0;
	
	/**
	 * Constructor
	 * 
	 * @param srtring $model
	 */
	public function __construct($model = false)
	{
		$this->_model = $model;
		$this->_setup();
	}
	
	/**
	 * Set ip list
	 * 
	 * @return void
	 */
	public function apply()
	{
		$this->_setIpList();
	}
	
	/**
	 * Check if ip in allow ip list
	 * 
	 * @param string $ip
	 * @param string $namespace
	 * @return bool
	 */
	public function allow($ip, $namespace = 0)
	{
		if (!$this->_checkIpAddr($ip)) {
			$this->_errors = 'Bad IP address! Should be in xxx.xxx.xxx.xxx format!';
			
			return false;
		}
		$this->_ipArr = $this->_getIpArr($ip);
		$this->_ipValue = $this->_getIpValue($this->_ipArr);
		
		return $this->_checkIpInList($namespace);
	}
	
	/**
	 * Add file with allow ips
	 * 
	 * @param string $filepath
	 * @return Engine_Crud_Tools_IpCheck
	 */
	public function addFileAllow($filepath, $namespace = 0)
	{
		if (!isset($this->_data['allow'][$namespace] )) {
			$this->_data['allow'][$namespace] = array();
		}
		if (file_exists($filepath)) {
    		$this->_data['allow'][$namespace] += file($filepath);
		}
		
		return $this;
	}
	
	/**
	 * Add file with deny ips
	 * 
	 * @param string $filepath
	 * @return Engine_Crud_Tools_IpCheck
	 */
	public function addFileDeny($filepath, $namespace = 0)
	{
		if (!isset($this->_data['deny'][$namespace] )) {
			$this->_data['deny'][$namespace] = array();
		}
		if (file_exists($filepath)) {
    		$this->_data['deny'][$namespace] += file($filepath);
		}
		
		return $this;
	}

	/**
	 * Add line with allow ip rule
	 * 
	 * @param string $ip
	 * @return Engine_Crud_Tools_IpCheck
	 */
	public function addIpAllow($ip, $namespace = 0)
	{
		if (!isset($this->_data['allow'][$namespace])) {
			$this->_data['allow'][$namespace] = array();
		}
		$this->_data['allow'][$namespace][] = $ip;
		
		return $this;
	}
	
	/**
	 * Add line with deny ip rule
	 * 
	 * @param string $ip
	 * @return Engine_Crud_Tools_IpCheck
	 */
	public function addIpDeny($ip, $namespace = 0)
	{
		if (!isset($this->_data['deny'][$namespace])) {
			$this->_data['deny'][$namespace] = array();
		}
		$this->_data['deny'][$namespace][] = $ip;
		
		return $this;
	}
	
	/**
	 * Clear allow ip rules
	 * 
	 * @return Engine_Crud_Tools_IpCheck
	 */
	public function clearIpAllow()
	{
		$this->_data['allow'] = array();
		
		return $this;
	}
	
	/**
	 * Clear deny ip rules
	 * 
	 * @return Engine_Crud_Tools_IpCheck
	 */
	public function clearIpDeny()
	{
		$this->_data['deny'] = array();
		
		return $this;
	}
	
	/**
	 * Clear all ip datas
	 * 
	 * @return Engine_Crud_Tools_IpCheck
	 */
	public function clearIpData()
	{
		$this->_data = array('allow' => array(), 'deny' => allow());
		$this->_allow = array();
		$this->_deny = array();
		
		return $this;
	}
	
	protected function _setup()
	{
	    $this->_initDb();
	}
	
	/**
	 * Init table.
	 * 
	 * @return bool
	 */
	protected function _initDb()
	{
	    return false;
	}
	
	/**
	 * Set ip list.
	 * 
	 * @return void
	 */
	protected function _setIpList()
	{
		$this->_allow = array();
		$this->_deny = array();

		foreach($this->_data['allow'] as $namespace => &$lines) {
			if (!isset($this->_allow [$namespace])) {
				$this->_allow [$namespace] = array();
			}
			foreach($lines as &$line) {				
				$item = $this->_processItem($line);
				$this->_allow [$namespace][] = $item;
			}
		}
		
		foreach($this->_data['deny'] as $namespace => &$lines) {
			if (!isset($this->_deny [$namespace])) {
				$this->_deny [$namespace] = array();
			} 
			foreach($lines as &$line) {
				$item = $this->_processItem($line);
				$this->_deny [$namespace][] = $item;
			}
		}
		
	}
	
	/**
	 * Process line from data and generate ip options array.
	 * 
	 * @param unknown_type $line
	 * @return array
	 */
	protected function _processItem($line)
	{
	    $item = array( 'eq' => false, 'from' => false , 'to' => false);
		$line = trim($line);
		$line = str_replace(" ", "", $line);
		
		if (strpos($line, "-") !== false) {
			$interval = explode("-",$line);
			if (!$this->_checkIpAddr($interval[0]) || !$this->_checkIpAddr($interval[1])) {
				return $item;
			}
			$item ['from'] = $this->_getIpValue( $this->_getIpArr($interval[0]) );
			$item ['to'] = $this->_getIpValue( $this->_getIpArr($interval[1]) );
		} else {
			if (!$this->_checkIpAddr($line)) {
				return $item;
			}
			$item ['eq'] = $this->_getIpValue( $this->_getIpArr($line) );				
		}
		
		return $item;
	}
	
	/**
	 * Check ip in list
	 * 
	 * @param string $namespace
	 * @return bool
	 */
	protected function _checkIpInList($namespace)
	{
		$allow = true;

		if (isset($this->_allow[$namespace]))
		foreach ($this->_allow[$namespace] as &$item) {
			$result = $this->_checkIp($item);
			if ($result === true) {
			    $allow = true;			    
			    break;
			}
			$allow = false;
		}
		if (isset($this->_deny[$namespace]))
	    foreach ($this->_deny[$namespace] as &$item) {
			$result = $this->_checkIp($item);
			if ($result === true) {
			    $allow = false;
			    break;
			}
		}
		
		return $allow;
	}
	
	/**
	 * Check ip
	 * 
	 * @param array $item
	 * @return bool
	 */
	private function _checkIp($item)
	{
	    $result = false;
	    
	    if ($item ['eq'] !== false){
			$result = $this->_checkEqual($this->_ipValue, $item ['eq']);
		} elseif (($item ['from'] !== false) && ($item ['to'] !== false)){
			$result = $this->_checkInterval($this->_ipValue, $item ['from'], $item ['to']);
		}
		
		return $result;
	}
	
	/**
	 * Check equal
	 * 
	 * @param integer $ip
	 * @param integer $value
	 */
	private function _checkEqual($ip, $value)
	{
		return ($ip == $value) ? true : false;
	}
	
	/**
	 * Check interval
	 * 
	 * @param integer $ip
	 * @param integer $from
	 * @param integer $to
	 * return bool
	 */
	private function _checkInterval($ip, $from, $to)
	{
		return ($ip >= $from && $to >= $ip) ? true : false;
	}
	
	/**
	 * Check ip adress
	 * 
	 * @param string $ip
	 * return bool
	 */
	private function _checkIpAddr($ip)
	{
		//first of all the format of the ip address is matched
		  if (preg_match("/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/", $ip))
		  {
		    //now all the intger values are separated
		    $parts=explode(".",$ip);
		    //now we need to check each part can range from 0-255
		    foreach($parts as $ip_parts)
		    {
		      if (intval($ip_parts)>255 || intval($ip_parts)<0)
		      return false; //if number is not within range of 0-255
		    }
		    return true;
		  }
		  else
		    return false; //if format of ip address doesn't matches
	}
	
	/**
	 * returns IP address in array of integer values
	 *
	 * @return array
	 */
	private function _getIpArr($ip)
	{
		$vars = explode('.',$ip);
		return [
			intval($vars[0]),
			intval($vars[1]),
			intval($vars[2]),
			intval($vars[3])
		];
	}
	
	/**
	 * returns numerical representation of IP address.
	 *       Example: (from Right to Left)
	 *       1.2.3.4 = 4 + (3 * 256) + (2 * 256 * 256) + (1 * 256 * 256 * 256)
	 *       is 4 + 768 + 13,1072 + 16,777,216 = 16,909,060
	 *
	 * @return integer
	 */
	private function _getIpValue($ipArr)
	{
		return $ipArr[3] + ( $ipArr[2] * 256 ) + ( $ipArr[1] * 256 * 256 ) + ( $ipArr[0] * 256 * 256 * 256 );
	}
	
	public static function getClientIp($checkProxy = true)
    {	
    	if ($checkProxy && isset($_SERVER['HTTP_X_REAL_IP']) &&  $_SERVER['HTTP_X_REAL_IP'] != null) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
    	} else if ($checkProxy && isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP'] != null) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else if ($checkProxy && isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != null) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];        
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }
    
    /**
     * Get client ip from request
     * Enter description here ...
     * @param mixed $request
     * @param bool $checkProxy
     * return string
     */
	protected function _getClientIp($request, $checkProxy = true)
    {
        if ($checkProxy && $request->getServer('HTTP_X_REAL_IP') != null) {
            $ip = $request->getServer('HTTP_X_REAL_IP');
    	} else if ($checkProxy && $request->getServer('HTTP_CLIENT_IP') != null) {
            $ip = $request->getServer('HTTP_CLIENT_IP');
        } else if ($checkProxy && $request->getServer('HTTP_X_FORWARDED_FOR') != null) {
            $ip = $request->getServer('HTTP_X_FORWARDED_FOR');
        } else {
            $ip = $request->getServer('REMOTE_ADDR');
        }

        return $ip;
    }
    
    /**
     * Return client ip adress
     * 
     * @param array $server
     * @return string
     */
	public static function getClientIp2(array $server = null)
    {
    	if (null === $server) {
    		$server = $_SERVER;
    	}
    	
        if (isset($_SERVER['HTTP_X_REAL_IP']) != null) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
    	} else if (isset($_SERVER['HTTP_CLIENT_IP']) != null) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) != null) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }
}