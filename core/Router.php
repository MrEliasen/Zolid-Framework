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
		$newroute = '';
		$i = 1; //avoid long routes sent to evil minded people, I cannot believe you could have something as deep as 20 sub directories. I could be wrong, just increate the number if this is the case.
		foreach( $routepieces as $piece )
		{
			$newroute = ( empty($newroute) ? '' : '/' ) . $piece;
			if( $i > 20 || in_array($newroute, self::$blocked) || in_array($newroute . '/*', self::$blocked) )
			{
				return true;
			}

			$i++;
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
	public static function exists( $route, $check = 'view' )
	{
		if( $check == 'controllers' )
		{
			$path = ROOTPATH . 'app' . DS . 'controllers' . DS . $route . '.php';
		}
		else if( $check == 'models' )
		{
			$path = ROOTPATH . 'app' . DS . 'models' . DS . $route . '.php';
		}
		else
		{
			$path = ROOTPATH . 'app' . DS . 'views' . DS . $route . '.pdt';
		}

		if( !self::isBlocked($route) && is_readable($path) )
		{
			return true;
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

		$model = Configure::get('views/default_route');
		$view = Configure::get('views/default_route');
		$controller = Configure::get('views/default_route');
		$route = Configure::get('views/default_route');

		if( Misc::maintenance() )
		{
			$controller = $view =  'app' . DS . 'users' . DS . 'maintenance';
		}
		else
		{
			// check if we have received any actions we need to perform
			if( Misc::data('route', 'request') != '' )
			{
				$route = str_replace('/', DS, Security::sanitize(Misc::data('route', 'request'), 'route'));
				if( strpos($route, 'plugin' . DS) === 0)
				{
					return self::getPluginRoute($route);
				}
				else
				{
					$view = 'app' . DS . $route;
					if( self::exists($route, 'controllers') )
					{
						$controller = $route;
					}
				}
			}
		}

		if( empty($controller) || !self::exists($controller, 'controllers') )
		{
			$controller = 'AppController';
		}
		else
		{
			$controller = 'Controllers' . Misc::toCamelCase($controller);
		}

		$model = str_replace('Controllers', 'Models', $controller);
		if( !self::exists($route, 'models') )
		{
			$model = 'AppModel';
		}

		$route = 'app' . DS . 'views' . DS . $route;
		if( !is_readable(ROOTPATH . DS . $route . '.pdt') )
		{
			$view = 'AppView';
			$controller = 'ControllersErrors404';
			$model = 'AppModel';
			$route = 'app' . DS . 'views' . DS . 'errors' . DS . '404';

			if( !self::exists($controller, 'controllers') )
			{
				$controller = 'AppController';
			}
		}

		return array(
			'controller' => $controller,
			'model' => $model,
			'view' => 'AppView',
			'route' => $route
		);
	}

	protected static  function getPluginRoute( $route )
	{
		$route = str_replace('plugins' . DS, '', $route);
		$route = explode(DS, $route);
		array_shift($route);
		$plugin_name = strtolower(current($route));
		array_shift($route);
		$route = implode(DS, $route);
        
		if( empty($route) )
		{
			$route = 'home';
		}

		$controller = ROOTPATH . 'plugin' . DS . $plugin_name . DS . 'controllers' . DS . $route;
		$model 		= ROOTPATH . 'plugin' . DS . $plugin_name . DS . 'models' . DS . $route;
		$view 		= ROOTPATH . 'plugin' . DS . $plugin_name . DS . 'Plugin' . ucfirst($plugin_name) . 'view';

		if( is_readable($controller . '.php') )
		{
			$controller = 'Plugin' . Misc::toCamelCase($plugin_name) . 'Controllers' . Misc::toCamelCase($route);
		}
		else
		{
			if( is_readable(ROOTPATH . 'plugin' . DS . $plugin_name . DS . 'controller.php') )
			{
				$controller = 'Plugin' . ucfirst($plugin_name) . 'Controller';
			}
			else
			{
				$controller = 'AppController';
			}
		}

		if( is_readable($model . '.php') )
		{
			$model = 'Plugin' . Misc::toCamelCase($plugin_name) . 'Models' . Misc::toCamelCase($route);
		}
		else
		{
			if( is_readable(ROOTPATH . 'plugin' . DS . $plugin_name . DS . 'model.php') )
			{
				$model = 'Plugin' . ucfirst($plugin_name) . 'Model';
			}
			else
			{
				$model = 'AppModel';
			}
		}

		if( is_readable($view . '.php') )
		{
			$view = Misc::toCamelCase('plugin' . DS . 'views' . DS . $plugin_name . DS . $route);
		}
		else
		{
			$view = 'AppView';
		}

		$route = 'plugin' . DS . $plugin_name . DS . 'views' . DS . $route;
		if( !is_readable(ROOTPATH . DS . $route . '.pdt') )
		{
			$view = 'AppView';
			$controller = 'ControllersErrors404';
			$model = 'AppModel';
			$route = 'app' . DS . 'views' . DS . 'errors' . DS . '404';

			if( !self::exists($controller, 'controllers') )
			{
				$controller = 'AppController';
			}
		}

		return array(
			'controller' => $controller,
			'model' => $model,
			'view' => $view,
			'route' => $route
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