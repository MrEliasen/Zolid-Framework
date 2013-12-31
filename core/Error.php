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

class Error
{
	/**
	 * Thow a custom PHP error message.
	 * 
	 * @param  string $message   the error message
	 * @param  contant $errorType the error type
	 */
	public static function throwNew( $message, $errorType = E_USER_ERROR)
	{
		restore_error_handler();
		trigger_error($message, $errorType);
		exit;
	}
}