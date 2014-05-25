<?php
/**
 *  Zolid Framework
 *  https://github.com/MrEliasen/Zolid-Framework
 *  
 *  @author 	Mark Eliasen (mark.eliasen@zolidsolutions.com)
 *  @copyright 	Copyright (c) 2014, Mark Eliasen
 *  @version    0.1.6.1
 *  @license 	http://opensource.org/licenses/MIT MIT License
 */

// prevent direct file access
if( !defined('ROOTPATH') )
{
    die();
}

final class Configure
{
	public static $config = array();

	// Protected constructor to prevent instance creation
	protected function __construct()
	{
		
	}

	/**
	 * Fetches a setting set using Configure::get()
	 *
	 * @param string $name The name of the setting to get
	 * @param string $raw  Get the raw settings data. (eg. php array with both value and description)
	 * @return mixed The setting specified by $name, or null if $name was not found
	 */
	public static function get( $name, $raw = false )
	{
		$name = explode('/', strtolower($name));
		$data = self::$config;

		foreach( $name as $n )
		{
			if( !isset($data[ $n ]) )
			{
				$data = null;
				break;
			}

			$data = $data[ $n ];
		}

		return ( is_array($data) && isset($data['value']) && !$raw ? $data['value'] : $data );
	}
	
	/**
	 * Adds the given $value to the configuration using the $name given
	 *
	 * @param string $name The name to give this setting.
	 * @param mixed $value The value to set
	 */
	public static function set( $name, $value )
	{
		$data = '';
		$name = explode('/', $name);

		while( $segment = array_pop($name) )
		{
			if( is_array($segment) )
			{
				$data = array($segment => array('value' => ( empty($data) ? $value : $data ) ) );
			}
			else
			{
		    	$data = array($segment => ( empty($data) ? $value : $data ) );
			}
		}

		self::$config = array_replace_recursive(self::$config, $data);
	}
	
	/**
	 * Checks if the config file has already been loaded
	 *
	 * @param string $file The file name in config dir to load (without .php)
	 */
	public static function isloaded( $file )
	{
		if( !empty(self::$config[$file]) )
		{
			return true;
		}

		return false;
	}
	
	/**
	 * Loads the given file and extracts all array elements, adding each to Configure::$config
	 *
	 * @param string $file The file name in config dir to load (without .php)
	 * @param string $from The directory from which to load the given config file, defaults to the config dir
	 */
	public static function load( $file, $from = '', $reload = false )
	{
		if( self::isloaded($file) )
		{
			if( $reload )
			{
				unset(self::$config[$file]);
			}
			else
			{
				return;
			}
		}

		$from = ( empty($from) ? ROOTPATH . 'config' . DS : $from );
		$file .= '.php';

		if( !is_readable($from . $file) )
		{
			return;
		}

		$config[ str_replace('.php', '', $file) ] = include($from . $file);

		if( !empty($config) )
		{
			self::$config = array_merge(self::$config, $config);
		}

		unset($config);
	}
}