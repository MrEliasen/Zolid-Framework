<?php
define('CORE_PATH', dirname(__FILE__));
define('ZF_VERSION', '0.1.0');

$_REQUEST = array_merge($_POST, $_GET);

try
{
    // Include all the required Classes
    require_once(CORE_PATH . '/classes/error.class.php'); ## Static
	set_error_handler( 'Error::log', E_ALL ); ## Set error handling to be done by the error class

    require_once(CORE_PATH . '/classes/notifications.class.php'); ## Static
    require_once(CORE_PATH . '/classes/security.class.php'); ## Static
    require_once(CORE_PATH . '/classes/cache.class.php'); ## Static
    
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