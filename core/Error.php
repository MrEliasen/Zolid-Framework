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