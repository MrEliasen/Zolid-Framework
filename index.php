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

// begin calculation of the script generation time
define('SCRIPT_START', microtime(true));

// Set the directory separator used throughout the application. 
define('DS', DIRECTORY_SEPARATOR);

// Set the Zolid-WHPS version. [Major].[Minor].[Revision].[Patch]
define('ZF_VERSION', '0.2.0.0');

// The required minimum php version to run the system
define('REQ_PHPVERSION', '5.3.7');

// Check if the version of PHP running on the server is new enough
if( version_compare(PHP_VERSION, REQ_PHPVERSION, '<') )
{
	throw new Exception('The available PHP version (' . PHP_VERSION . ') is not new enough to run this script. Please install PHP version ' . REQ_PHPVERSION . ' or newever.', 1);
}

// Set the full path to the system web root dir, where the index.php is.
define('ROOTPATH', dirname(__FILE__) . DS);

// Include the auto loader(s)
include ROOTPATH . 'core' . DS . 'Autoloader.php';

// Load the compatibility library with PHP 5.5's simplified password hashing API, if required.
if( version_compare(PHP_VERSION, '5.5.0', '<') )
{
	require ROOTPATH . 'libs' . DS . 'password_compat' . DS . 'password.php';
}

// Load the core settings
Configure::load('core');

// Set the default timezone, if we can.
if (function_exists('date_default_timezone_set'))
{
	date_default_timezone_set( ( Configure::get('core/timezone') == '' ? 'Europe/Copenhagen' : Configure::get('core/timezone') ) );
}

// Check if the debug flag has been set
if( Configure::get('core/debug') )
{
	if( ini_set('display_startup_errors', true) !== false )
	{
		ini_set('display_errors', true);
		ini_set('error_reporting', E_ALL & ~E_STRICT);
	}
	else
	{
		Notifications::set('Warning! Unable to modify php ini flags. Debug mode will likely not work correctly!', 'Debug Mode', 'error');
	}
}

// Get the requested route (if any). Defaults back to whats specified in the config.
$route = Router::getRoute();

// Instanciate the Application
$model = new $route['model']();
$controller = new $route['controller']($model, $route['route']);
$view = new $route['view']($model, $controller);

// Ta-daaa!
echo $view->output();