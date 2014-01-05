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

// do not allow direct file access
if( !defined('ROOTPATH') )
{
    die();
}

class Notifications
{
	// Protected constructor to prevent instance creation
	protected function __construct()
	{
		
	}

	/**
	 * Sets the notification to be displayed at next opportunity. If a notification is already set, it will be overwritten
	 * 
	 * @param string $body  the notification body
	 * @param string $title the notification title
	 * @param string $type  the type/class the notification will have when output
	 */
	public static function set( $body, $title = '', $type = 'error', $placement = 'general' )
	{
		if( !empty($title) )
		{
			$title = '<strong>' . $title . '</strong>';
		}

		// Bootstrap "fix", remove if you do not use bootstrap
		if( $type == 'error' )
		{
			$type = 'danger';
		}

		$_SESSION['notification'][$placement] = '<div class="alert alert-' . $type . '"><button type="button" class="close" data-dismiss="alert">&#215;</button>' . $title . ' ' . $body . '</div>';

		return true;
	}
	
	/**
	 * displays notifications if any is has been set with Notifications::set()
	 * 
	 * @return string The notification message
	 */
	public static function show( $placement = 'general' )
	{
		if( self::pending( $placement ) )
		{
			$msg = $_SESSION['notification'][ $placement ];
			unset($_SESSION['notification'][ $placement ]);

			return $msg;
		}
	}
	
	/**
	 * Checks if any notifications are avilable to be displayed.
	 * 
	 * @return boolean true/false
	 */
	public static function pending( $placement = 'general' )
	{
		if( empty($_SESSION['notification'][ $placement ]) )
		{
			return false;
		}

		return true;
	}
}