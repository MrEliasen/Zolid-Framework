<?php
/**
 *  Zolid Framework - MIT licensed
 *  https://github.com/MrEliasen/Zolid-Framework
 *
 *  A class which handles caching
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

class Cache
{
	/**
	 * retuns the cached content if it exists and is not expired, else it returns empty.
	 * 
	 * @param  string $name the unique cache identifier aka name
	 * @return string       the cached content.
	 */
	public static function showCache($name)
	{
		$dir = CORE_PATH . '/cache/';
		$from_file = $name . '.cache';
		
		if( file_exists($dir.$from_file) )
		{
			if( filesize($dir.$from_file) > 0 )
			{
				if( filemtime($dir.$from_file) > time()-600 )
				{
					ob_start();
					include($dir . $from_file);
					$cache = ob_get_clean();

					return $cache;
				}
			}
		}

		return '';
	}

	/**
	 * caches the $output to a .cache file with the name of $name
	 * 
	 * @param mised $output the output you wish to cache
	 * @param string $name   the name of the cache file, used later for retrieval
	 */
	public static function addCache($output, $name)
	{
		$dir = CORE_PATH . '/cache/';
		$to_file = $name . '.cache';

		$theFile = fopen($dir . $to_file, 'w');
		$saved = fwrite($theFile, $output);
		fclose($theFile);

		if($saved)
		{
			return true;
		}

		return false;
	}
}