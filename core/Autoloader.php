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

function AppAutoloader( $class )
{
	// The dirs where the classes might available in
	Configure::load('autoloader');
	$dirs = Configure::get('autoloader/paths');

	// Loop through each of the dirs to see if we can find out class
	foreach( $dirs as $dir )
	{
		if( $dir == 'plugin' && strpos($class, 'Plugin') === 0 )
		{
			$plugin = Misc::camelCaseToFilePath($class);
			$plugin = explode(DS, $plugin);
			$dir = AppAutoloaderPluginSearch(ROOTPATH . str_replace('/*', '', $dir), $class, $plugin[1]);
		}
		
		if( !empty($dir) && is_readable(ROOTPATH . $dir . DS . $class . '.php') )
		{
			require_once ROOTPATH . $dir . DS . $class . '.php';
		}
	}

	// If we are not loading a core class, but a view controller, then we continue below:
	$class = Misc::camelCaseToFilePath($class);
	if( is_readable(ROOTPATH . 'app' . DS . $class . '.php') )
	{
		require_once ROOTPATH . 'app' . DS . $class . '.php';
		return;
	}
}

function AppAutoloaderPluginSearch($path, $class, $plugin)
{
	foreach( scandir($path . DS . $plugin) as $dir )
	{
		if( $dir == '.' || $dir == '..' )
		{
			continue;
		}

		// A fix for plugins.. I know its not the most elegant one..
		if( $path == realpath(dirname(__FILE__) . DS . '..') . DS . 'plugin' )
		{
			$file = realpath(dirname(__FILE__) . DS . '..') . DS . Misc::camelCaseToFilePath($class) . '.php';
		}
		else
		{
			$file = $path . DS . Misc::camelCaseToFilePath($class) . '.php';
		}

		if( !empty($file) && is_readable($file) )
		{
			require_once $file;
			return;
		}
		else if( is_dir($path . DS . $dir) )
		{
			$file = AppAutoloaderPluginSearch($path . DS . $dir, $class, $plugin);
		}
	}
}

// include the Configure class..
require ROOTPATH . 'core' . DS . 'Configure.php';

// and register the autoload function.
spl_autoload_register('AppAutoloader');