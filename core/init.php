<?php
/**
 *  Zolid Framework - MIT licensed
 *  https://github.com/MrEliasen/Zolid-Framework
 *
 *  @author     Mark Eliasen
 *  @website    www.zolidweb.com
 *  @copyright  (c) 2013 - Mark Eliasen
 *  @version    0.1.2
 */
 
define('CORE_PATH', dirname(__FILE__));
define('ZF_VERSION', '0.1.2');

$_REQUEST = array_merge($_POST, $_GET);

try
{
    // Include all the required Classes
    require_once(CORE_PATH . '/classes/error.class.php'); ## Static
	set_error_handler( 'Error::log', E_ALL ); ## Set error handling to be done by the error class

    require_once(CORE_PATH . '/classes/notifications.class.php'); ## Static: Notification
    require_once(CORE_PATH . '/classes/security.class.php'); ## Static: Security
    require_once(CORE_PATH . '/classes/cache.class.php'); ## Static: Cache
    
    require_once(CORE_PATH . '/classes/core.class.php'); 
    require_once(CORE_PATH . '/classes/user.class.php'); ## Extends core
    require_once(CORE_PATH . '/classes/template.class.php'); ## Extends user
}
catch (Exception $e)
{
    die('Error loading system.');
}

// Instantiate the classes.
$core = new Template();