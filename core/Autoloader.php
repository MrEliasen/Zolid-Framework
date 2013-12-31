<?php
/**
 *  Zolid Framework
 *  https://github.com/MrEliasen/Zolid-Framework
 *  
 *  @author 	Mark Eliasen (mark.eliasen@zolidsolutions.com)
 *  @copyright 	Copyright (c) 2013, Mark Eliasen
 *  @version    0.1.6.0
 *  @license 	http://opensource.org/licenses/MIT MIT License
 */

// Autoload out classes
function AppAutoloader( $class )
{
	// The dirs where the classes might available in
	Configure::load('autoloader');
	$dirs = Configure::get('autoloader/paths');

	// Loop through each of the dirs to see if we can find out class
	foreach( $dirs as $dir )
	{
		// If the class is found, and is readable, require it!
		if( is_readable(ROOTPATH . $dir . DS . $class . '.php') )
		{
			require ROOTPATH . $dir . DS . $class . '.php';
			break;
		}
	}
}

// include the Configure class..
require ROOTPATH . 'core' . DS . 'Configure.php';

// and register the autoload function.
spl_autoload_register('AppAutoloader');