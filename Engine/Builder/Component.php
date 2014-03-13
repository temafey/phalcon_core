<?php

/*
  +------------------------------------------------------------------------+
  | Phalcon Framework                                                      |
  +------------------------------------------------------------------------+
  | Copyright (c) 2011-2013 Phalcon Team (http://www.phalconphp.com)       |
  +------------------------------------------------------------------------+
  | This source file is subject to the New BSD License that is bundled     |
  | with this package in the file docs/LICENSE.txt.                        |
  |                                                                        |
  | If you did not receive a copy of the license and are unable to         |
  | obtain it through the world-wide-web, please send an email             |
  | to license@phalconphp.com so we can send you a copy immediately.       |
  +------------------------------------------------------------------------+
  | Authors: Andres Gutierrez <andres@phalconphp.com>                      |
  |          Eduar Carvajal <eduar@phalconphp.com>                         |
  +------------------------------------------------------------------------+
*/

namespace Engine\Builder;

use Engine\Builder\Script\Color;
use	Engine\Builder\BuilderException;
use Engine\Tools\Inflector;
use Engine\Tools\File;

/**
 * \Phalcon\Builder\Component
 *
 * Base class for builder components
 *
 * @category 	Phalcon
 * @package 	Builder
 * @subpackage  Component
 * @copyright   Copyright (c) 2011-2013 Phalcon Team (team@phalconphp.com)
 * @license 	New BSD License
 */
abstract class Component
{
    const OPTION_MODEL = 1;

    const OPTION_FORM = 2;

    const OPTION_GRID = 3;

    protected $db = null;

	protected $_options = array();

    protected $_builderOptions = array();

	public function __construct($options)
	{
		$this->_options = $options;
	}

    /**
     * Tries to find the current configuration in the application
     *
     * @param $path
     * @return mixed|\Phalcon\Config\Adapter\Ini
     * @throws BuilderException
     */
    protected function _getConfig($path)
	{
		foreach (array('app/config/', '../config/') as $configPath) {
			if (file_exists($path . $configPath . "engine.ini")) {
				return new \Phalcon\Config\Adapter\Ini($path . $configPath . "/engine.ini");
			} else {
				if (file_exists($path . $configPath. "engine.php")) {
					$config = include($path . $configPath . "engine.php");
					return $config;
				}
			}
		}

		$directory = new \RecursiveDirectoryIterator('.');
		$iterator = new \RecursiveIteratorIterator($directory);
		foreach ($iterator as $f) {
			if (preg_match('/engine\.php$/', $f->getPathName())) {
				$config = include $f->getPathName();
				return $config;
			} else {
				if (preg_match('/engine\.ini$/', $f->getPathName())) {
					return new \Phalcon\Config\Adapter\Ini($f->getPathName());
				}
			}
		}
		throw new BuilderException('Builder can\'t locate the configuration file');
	}

    protected function prepareDbConnection($config)
    {
        // If no database configuration in config throw exception
        if (!isset($config->database)) {
            throw new BuilderException(
                "Database configuration cannot be loaded from your config file"
            );
        }


        // if no adapter in database config throw exception
        if (!isset($config->database->adapter)) {
            throw new BuilderException(
                "Adapter was not found in the config. " .
                "Please specify a config variable [database][adapter]"
            );
        }


        // If model already exist throw exception
        if (file_exists($this->_builderOptions['path'])) {
            if (!$this->_options['force']) {
                throw new BuilderException(
                    "The model file '" . $this->_builderOptions['path'] .
                    "' already exists in models dir"
                );
            }
        }


        // Get and check database adapter
        $adapter = $config->database->adapter;
        $this->isSupportedAdapter($adapter);
        if (isset($config->database->adapter)) {
            $adapter = $config->database->adapter;
        } else {
            $adapter = 'Mysql';
        }
        // Get database configs
        if (is_object($config->database)) {
            $configArray = $config->database->toArray();
        } else {
            $configArray = $config->database;
        }
        $adapterName = 'Phalcon\Db\Adapter\Pdo\\' . $adapter;
        unset($configArray['adapter']);
        // Open Connection
        $db = new $adapterName($configArray);

        $this->db = $db;
    }

    /**
     * Check if a path is absolute
     *
     * @param $path
     * @return bool
     */
    public function isAbsolutePath($path)
	{
		if (PHP_OS == "WINNT") {
			if (preg_match('/^[A-Z]:\\\\/', $path)) {
				return true;
			}
		} else {
			if (substr($path, 0, 1) == DIRECTORY_SEPARATOR) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if the script is running on Console mode
	 *
	 * @return boolean
	 */
	public function isConsole()
	{
		return !isset($_SERVER['SERVER_SOFTWARE']);
	}

	/**
	 * Check if the current adapter is supported by Phalcon
	 *
	 * @param string $adapter
	 * @throws BuilderException
	 */
	public function isSupportedAdapter($adapter)
	{
		if (!class_exists('\Phalcon\Db\Adapter\Pdo\\' . $adapter)) {
			throw new BuilderException("Adapter $adapter is not supported");
		}
	}

	/**
	 * Shows a success notification
	 *
	 * @param string $message
	 */
	protected function _notifySuccess($message)
	{
		print Color::success($message);
	}

	abstract public function build();

    protected function buildOptions($table, $config, $type = self::OPTION_MODEL)
    {
        $moduleName = $this->getModuleNameByTableName($table);
        $className = $this->getclassName($table);
        $modelNamespace = $this->getNameSpace($table, $type);

        if ($type === self::OPTION_MODEL) {
            $path = $config->builder->modules->{$moduleName}->modelsDir;
        } elseif ($type === self::OPTION_FORM) {
            $path = $config->builder->modules->{$moduleName}->formsDir;
        } elseif ($type === self::OPTION_GRID) {
            $path = $config->builder->modules->{$moduleName}->gridsDir;
        } else {
            throw new \InvalidArgumentException('Invalid build type');
        }

        $modelPath = $this->getPath($path, $table);

        $this->_builderOptions = array(
            'moduleName' => $moduleName,
            'className' => $className,
            'namespace' => 'namespace '.$modelNamespace.';',
            'namespaceClear' => $modelNamespace,
            'path' => $modelPath
        );

        return true;
    }

    /**
     * Return module name based on table name
     * For example if table name is "front_category" return "front" <-- module name
     *
     * <code>
     * $moduleName = $this->getModuleNameByTableName("front_category");
     * </code>
     *
     * @param $table
     * @return mixed
     */
    protected function getModuleNameByTableName($table)
    {
        $pieces = explode('_', $table);

        if (empty($pieces)) {
            $pieces = [null];
        }

        return $pieces[0];
    }

    /**
     * Return class name
     *
     * @param $table
     * @return null|string
     */
    protected function getClassName($table)
    {
        $name = null;
        $pieces = explode('_', $table);
        return ucfirst(array_pop($pieces));
    }

    /**
     * Return alias by table name without module name
     *
     * @param $str
     * @return string
     */
    protected function getAlias($str)
    {
        $pieces = explode('_', strtolower($str));
        array_shift($pieces);
        return implode('_', $pieces);
    }

    protected function getPath($dirPath, $table)
    {
        $pieces = explode('_', $table);
        array_shift($pieces);

        if (count($pieces) > 1) {
            $modelName = ucfirst(array_pop($pieces));

            $line = '';
            foreach ($pieces as $piece) {
                $line .= ucfirst($piece) . '/';
            }

            $path = $dirPath . $line;

            File::rmkdir($path, 0755, true);

            $modelsDirPath = $path . $modelName . '.php';
        }else {
            $modelsDirPath = $dirPath . $this->getClassName($table) . '.php';
        }

        return $modelsDirPath;
    }

    protected function getNameSpace($table, $type)
    {
        $pieces = explode('_', $table);
        $moduleName = array_shift($pieces);
        array_pop($pieces);

        switch($type) {
            case self::OPTION_MODEL: $buildType = 'Model';
                break;
            case self::OPTION_FORM: $buildType = 'Form';
                break;
            case self::OPTION_GRID: $buildType = 'Grid';
                break;
            default: throw new \InvalidArgumentException('Invalid build type');
                break;
        }

        if (count($pieces) > 0) {
            $namespace = ucfirst($moduleName).'\\'.$buildType;
            foreach ($pieces as $piece) {
                $namespace .= '\\'.ucfirst($piece);
            }
        } else {
            $namespace = ucfirst($moduleName).'\\'.$buildType;
        }

        return $namespace;
    }

    protected function isEnum($table, $filed)
    {
        $res = $this->db->fetchOne("SHOW COLUMNS FROM {$table} WHERE Field = '{$filed}'");
        preg_match('/enum\((.*)\)$/', $res['Type'], $matches);

        $answer = false;

        if (count($matches) > 0) {
            $answer = true;
        }

        return $answer;
    }

    protected function getEnumValues($table, $filed)
    {
        $res = $this->db->fetchOne("SHOW COLUMNS FROM {$table} WHERE Field = '{$filed}'");
        preg_match('/enum\((.*)\)$/', $res['Type'], $matches);

        $return = array();

        if (count($matches) > 0) {
            foreach (explode(',', $matches[1]) as $value) {
                $return[] = trim($value, "'");
            }
        }

        return $return;
    }

}
