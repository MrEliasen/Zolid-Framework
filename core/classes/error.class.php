<?php
/**
 *  Zolid Framework - MIT licensed
 *  https://github.com/MrEliasen/Zolid-Framework
 *
 *  A class which handles error logging
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

class Error
{
	/**
	 * log the error to the errors.log file inside the Core directory
	 * 
	 * @param  integer $number  error number
	 * @param  string  $string  the error
	 * @param  string  $file    thefilename
	 * @param  string  $line    line number
	 * @param  array   $context ignore this value.
	 * @return boolean           true on success, false on error.
	 */
	public static function log( $number = 0, $string, $file = 'Undefined', $line = 'undefined', $context = array() )
	{
		$error_log = '['.date('H:i:s - d/m/Y', time()).']'."\n".
                     'File: '.str_replace(CORE_PATH, '', $file).''."\n".
                     'Line: '.$line.''."\n".
                     'Error: '.$string.
                     "\n------------------------------------------------------------ \n";
		try
		{
			$theFile = fopen( CORE_PATH . '/errors.log', 'a' );
			$error = fwrite( $theFile, $error_log );
			fclose( $theFile );

			return true;
		}
		catch ( Exception $pe )
		{
            error_log( $error_log );
            error_log( print_r( $pe ) );

            return false;
		}
	}

	/**
	 * Thow a custom PHP error message.
	 * 
	 * @param  string $message   the error message
	 * @param  contant $errorType the error type
	 */
	public static function throwError( $message, $errorType = E_USER_ERROR)
	{
		restore_error_handler();
		trigger_error($message, $errorType);
		exit;
	}
}