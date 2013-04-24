<?php
/**
 *  Zolid Framework - MIT licensed
 *  https://github.com/MrEliasen/Zolid-Framework
 *
 *  A class which handles system/user notifications.
 *
 *  @author     Mark Eliasen
 *  @website    www.zolidweb.com
 *  @copyright  (c) 2013 - Mark Eliasen
 *  @version    0.1.2
 */
 
if( !defined('CORE_PATH') )
{
    die('Direct file access not allowed.');
}

class Notifications
{
	/**
	 * set the notification to display to the user at the given position on your site.
	 * 
	 * @param string $where the identifier for where to display the notification (see the showNotification function)
	 * @param string $body  the notification body
	 * @param string $title the notification title (can be left blank)
	 * @param string $type  can be one of the following: error, warning, success, info - default is error
	 */
	public static function setNotification( $where, $body, $title = '', $type = 'error' )
	{
		if( !empty($title) )
		{
			$title = '<strong>' . $title . '</strong>';
		}

		$_SESSION['notifications'][$where] = '<div class="alert alert-' . $type . '"><button type="button" class="close" data-dismiss="alert">&#215;</button>' . $title . ' ' . $body . '</div>';

		return true;
	}
	
	/**
	 * displays notifications if any is found
	 * 
	 * @param  string $where the identifier used to look for notification. Eg. if you set a notification with $where = login_1, you need to use this for fetching the notification here
	 * @return string        the notification message
	 */
	public static function showNotification( $where )
	{
		if( !empty($_SESSION['notifications'][$where]) )
		{
			$msg = $_SESSION['notifications'][$where];
			unset( $_SESSION['notifications'][$where] );

			return $msg;
		}
	}
}