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

class Router
{
	/*
	 * Blocked views the user should not be able to access "directly".
	 * Block individual views, or whole directories (with /*).
	 */
	private static $blocked = array(
		'users/views_template',
		'admin/views_template',
		'emails/*',
		'errors/*'
	);

	/**
	 * Checks if the specified $route is blocked or not.
	 * 
	 * @param  string  $route The route to check
	 * @return boolean        True if blocked, false if not.
	 */
	public static function isBlocked( $route )
	{
		$routepieces = explode('/', $route);

		if( in_array($routepieces[0] . '/*', self::$blocked) || in_array($route, self::$blocked))
		{
			return true;
		}

		return false;
	}

	/**
	 * Checks if the specified $route (or if the $controller flag is set, then it will check controllers) exists.
	 * 
	 * @param  string  $route      The route (or controller) to check
	 * @param  boolean $controller If you are checking a controller exists and not a view
	 * @return boolean             True if it exists, false if not.
	 */
	public static function exists( $route, $controller = false )
	{
		if( $controller || ( !$controller && !self::isBlocked($route) ) )
		{
			if( is_readable(ROOTPATH . 'app' . DS . 'views' . DS . $route . '.pdt') || ( $controller && is_readable(ROOTPATH . 'app' . DS . 'controllers' . DS . $route . '.php') ) )
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Get the route and controller if available for the given route.
	 * Will fallback to route "404" if not found.
	 * Will fallback to route "maintenance" if the maintenance flag is set.
	 * 
	 * @return array The controller and view for the given route.
	 */
	public static function getRoute()
	{
		// Load the database configuration
		Configure::load('views');

		$view = Configure::get('views/default_route');
		$controller = str_replace('/' , '_', Configure::get('views/default_route'));

		if( !Misc::maintenance() )
		{
			// check if we have received any actions we need to perform
			if( Misc::data('route', 'request') )
			{
				$route = Security::sanitize(str_replace('/', DS, Misc::data('route', 'request')), 'route');
				if( self::exists($route) )
				{
					$view = $route;
				}
				else
				{
					$view = 'errors' . DS . '404';
					$controller = str_replace(DS , '_', $view);
				}

				if( self::exists(str_replace(DS, '_', $route), true) )
				{
					$controller = str_replace(DS, '_', $route);
				}
			}
		}
		else
		{
			$view = 'users/maintenance';
			$controller = '';
		}

		if( empty($controller) || !is_readable(ROOTPATH . 'app' . DS . 'controllers' . DS . $controller . '.php') )
		{
			$controller = 'AppController';
		}

		return array(
			'controller' => $controller,
			'view' => $view
		);
	}

	/**
	 * Checks if the request is an ajax request or not.
	 * 
	 * @return boolean true/false
	 */
	protected static function isAjaxAction()
	{
		if( !empty($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == 'xmlhttprequest' )
		{
			return true;
		}

		return false;
	}

	/**
	 * Graps any user action and returns it so we know what the user wants to do.
	 * 
	 * @return array The method name of the action the user wants to do, and whether the action is submitted via ajax or not.
	 */
	public static function getAction()
	{
		if( !Misc::maintenance() && Misc::data('action') )
		{
			$action = Security::sanitize(Misc::data('action'), 'purestring');
		}

		return array(
			'action' => ( !empty($action) ? 'action_' . $action : '' ),
			'ajax' => self::isAjaxAction()
		);
	}
}