<?php
/**
 * @namespace
 */
namespace Engine\Tools;

/**
 * Class File
 *
 * @category   Engine
 * @package    Tools
 */
class File
{
    /**
     * @param $root
     * @param array $dirs
     * @param int $mode
     * @param bool $recursive
     */
    static function sarmdir($root, array $dirs, $mode = 0755, $recursive = false) 
    {
        $result = true;
        $root = str_replace("\\","/",$root);
        $root = (substr($root, -1 , 1) == '/') ? substr($root, 0, -1) : $root;
        foreach ($dirs as $dir) {
            $path = $root.'/'.$dir;
            if (!is_dir($path)) {
                if (!self::rmkdir($path, $mode, $recursive)) {
                    $result = false;
                }
            }
        }
        return $result;
    }

    /**
     * @param $root
     * @param array $dirs
     * @param int $mode
     * @param bool $recursive
     */
    static function rarmdir($root, array $dirs, $mode = 0755, $recursive = true) 
    {
        $result = true;
        $root = str_replace("\\","/",$root);
        $root = (substr($root, -1 , 1) == '/') ? substr($root, 0, -1) : $root;
        foreach ($dirs as $dir) {
            $path = $root.'/'.$dir;
            if (!is_dir($path)) {
                if (!mkdir($path, $mode, $recursive)) {
                    $result = false;
                }
            }
        }

        return $result;
    }

    /**
     * Remove array of dirs recursively
     *
     * @param array $paths
     * @param int $mode
     * @param bool $recursive
     * @return bool
     */
    static function armdir(array $paths, $mode = 0755, $recursive = false) 
    {
        $result = true;
        foreach ($paths as $path) {
            if (!is_dir($path)) {
                if (!self::rmkdir($path, $mode, $recursive)) {
                    $result = false;
                }
            }
        }

        return $result;
    }

    /**
     * Create dirs recursively
     *
     * @param $path
     * @param int $mode
     * @param bool $recursive
     * @return bool
     */
    static function rmkdir($path, $mode = 0755, $recursive = false) 
    {
        $path = rtrim(preg_replace(array("/\\\\/", "/\/{2,}/"), "/", $path), "/");
        $e = explode("/", ltrim($path, "/"));
        if (substr($path, 0, 1) == "/") {
            $e[0] = "/".$e[0];
        }
        $c = count($e);
        $cp = $e[0];
        for ($i = 1; $i < $c; $i++) {
            if (!is_dir($cp) && !mkdir($cp, $mode, $recursive)) {
                return false;
            }
            $cp.= "/".$e[$i];
        }

        return mkdir($path, $mode, $recursive);
    }

    /**
     * Remove dirs recursively
     *
     * @param string $dir
     * @return @void
     */
    static function rrmdir($dir)
    {
        $result = true;
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir."/".$object) == "dir") {
                        if (!self::rrmdir($dir."/".$object)) {
                            $result = false;
                        }
                    } else {
                        if (!unlink($dir."/".$object)) {
                            $result = false;
                        }
                    }
                }
            }
            reset($objects);
            if (!rmdir($dir)) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Check directory have permission
     *
     * @param $dir
     * @return bool
     */
    static function isDirMine($dir)
    {
        // bypasses open_basedir restrictions of is_dir and fileperms
        $tmp_cmd = `ls -dl $dir`;
        $dir_flag = $tmp_cmd[0];
        if ($dir_flag!="d") {
            // not d; use next char (first char might be 's' and is still directory)
            $dir_flag = $tmp_cmd[1];
        }
        return ($dir_flag == "d");
    }

    /**
     * Writes an array to a file.  Can be later used by include/require
     * @param resource $file   : A file resource, (as returned from fopen)
     * @param array    $array  : The array tp be written from
     * @param string   $string : The initial variable name of the array,
     *                           as it will appear in the file
     */
    static function setArrayToFile($file, $array, $string="\$array", $new = true, $end = true)
    {
        if ($new) {
            $file = fopen($file, "w");
            fwrite($file, "<?php\r\n");
        }

        fwrite($file, $string."=[];\r\n");
        foreach ($array as $ind => $val) {
            $str = $string."[".self::_quote($ind)."]";
            if (is_array($val)) {
                if (self::_hasNoSubArrays($val)) {
                    fwrite($file, $str."=".self::_compressArray($val).";\r\n");
                } else {
                    self::setArrayToFile($file, $val, $str, false, false);
                }
            } else {
                fwrite($file, $str."=".self::_quote($val).";\r\n");
            }
        }

        if ($end) {
            fwrite($file, "?>");
            fclose($file);
        }
    }

    /**
     * Checks if an array contains no arrays
     * @param  arary $array : The array to be checked
     * @return boolean      : true if $array contains no sub arrays
     *                        false if it does
     */
    static protected function _hasNoSubArrays($array)
    {
        if (!is_array($array)) {
            return true;
        }

        foreach ($array as $sub) {
            if (is_array($sub)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Compresses an array into a string:
     * $array=[];
     * $array[0]=0;
     * $array["one"]="one";
     * compress_array($array) will return 'array(0=>0,"one"=>"one")'
     * @param array $array : the array to be compressed
     * @return string      : the "compressed" string representation of $array
     * @note               : works recursively, so $array can contain arrays
     */
    static protected function _compressArray($array)
    {
        if (!is_array($array)) {
            return self::_quote($array);
        }
        $strings=[];

        foreach ($array as $ind => $val) {
            $strings[] = self::_quote($ind)."=>".
                (is_array($val) ? self::_compressArray($val) : self::_quote($val));
        }
        return "array(".implode(",", $strings).")";
    }
    /**
     * Adds quotes to $val if its not an integer
     * @param mixed $val : the value to be tested
     */
    static protected function _quote($val)
    {
        return is_int($val) ? $val : "\"".$val."\"";
    }

    /**
     * Expanden file_exists function
     * Searches in include_path
     */
    public static function fileExists($filename)
    {
        $include_path = get_include_path();

        if (false !== strpos($include_path, PATH_SEPARATOR)) {
            if (false !== ($temp = explode(PATH_SEPARATOR, $include_path)) && count($temp) > 0) {
                for($n = 0; $n < count($temp); $n++) {
                    if (false !== file_exists($temp[$n]."/". $filename)) {
                        return true;
                    }
                }
                return false;
            } else {
                return false;
            }
        } elseif (!empty($include_path)) {
            if (false !== file_exists($include_path."/". $filename)) {
                return true;
            }
        }

        return file_exists($filename);
    }

    /**
     * Move directory path to new path
     *
     * @param string $source
     * @param string $dest
     * @param bool $overwrite
     * @param null|string $funcloc
     * @return void
     */
    static function dirmv($source, $dest, $overwrite = false, $funcloc = NULL, $permission = 0777) {

        if (is_null($funcloc)) {
            $funcloc = '/';
        }

        if (!is_dir($dest.$funcloc)) {
            mkdir($dest.$funcloc, $permission, true); // make subdirectory before subdirectory is copied
        }
        if ($handle = opendir($source.$funcloc)) { // if the folder exploration is sucsessful, continue
            while (false !== ($file = readdir($handle))) { // as long as storing the next file to $file is successful, continue
                if ($file != '.' && $file != '..') {
                    $path  = $source.$funcloc.$file;
                    $path2 = $dest.$funcloc.$file;

                    if (is_file($path)) {
                        if (!is_file($path2)) {
                            if (!@rename($path, $path2)) {
                                echo '<font color="red">File ('.$path.') could not be moved, likely a permissions problem.</font>';
                            }
                        } elseif ($overwrite) {
                            if (!@unlink($path2)) {
                                echo 'Unable to overwrite file ("'.$path2.'"), likely to be a permissions problem.';
                            } else
                                if (!@rename($path, $path2)) {
                                    echo '<font color="red">File ('.$path.') could not be moved while overwritting, likely a permissions problem.</font>';
                                }
                        }
                    } elseif (is_dir($path)) {
                        self::dirmv($source, $dest, $overwrite, $funcloc.$file.'/'); //recurse!
                        rmdir($path);
                    }
                }
            }
            closedir($handle);
        }
    } // end of dirmv()

    static function getFiles($directory, $exempt = array('.','..','.ds_store','.svn'), &$files = [])
    {
        $directory = rtrim($directory, '/').'/';
        $handle = opendir($directory);
        while (false !== ($resource = readdir($handle))) {
            if (!in_array(strtolower($resource),$exempt)) {
                if (is_dir($directory.$resource.'/')) {
                    array_merge($files,
                        self::getFiles($directory.$resource.'/',$exempt,$files));
                } else {
                    $files[] = $directory.$resource;
                }
            }
        }
        closedir($handle);

        return $files;
    }

}