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

class AppView
{
	/**
	 * Holds our model.
	 * @var object
	 */
	private $model;
	/**
	 * Holds our controller
	 * @var object
	 */
	private $controller;

	final public function __construct($model, $controller)
	{
		// Load the database configuration
		Configure::load('view');

		$this->model = $model;
		$this->controller = $controller;

		// Check for redirection request, and execute it.
		$this->redirect();
	}

	/**
	 * Redirects the user to the $route
	 * 
	 * @param  string $route The route to redirect the user to.
	 */
	private function redirect( $route = '' )
	{
		if( !empty($route) )
		{
			$this->redirectTo($route); 
		}

		// Have we even received a redirection command?
		if( empty($this->controller->redirect) )
		{
			return;
		}

		// Prevent infinite loops, check if we are already on the page we can to redirect to.
		if( $this->controller->redirect == $this->controller->route )
		{
			return;
		}

		// Bye bye! See you on the other page!
		header('Location: ' . $this->controller->makeUrl( $this->controller->redirect ) );
		exit;
	}

	/**
	 * Will output the page to be viewed by the user.
	 * 
	 * @return html
	 */
	public function output()
	{
        $viewdir = explode(DS, $this->controller->route);
        array_pop($viewdir);
        $viewdir = implode(DS, $viewdir);

        if( !is_readable(ROOTPATH . 'app' . DS . 'views' . DS . $viewdir . DS . 'headerfooter_template.pdt') )
        {
        	$viewdir = 'users';
        }

        if( strpos($this->controller->route, 'plugin' . DS) === 0 )
		{
			$plugin_name = explode(DS, $this->controller->route);
			$pluginController = 'Plugin' . ucfirst($plugin_name[1]) . 'Controller';
			if( property_exists($pluginController, 'plugin') )
			{
				$plugindata = @$pluginController::$plugin;
			}
		}

        $css_includes = '';
        if( !empty($plugindata['css_includes']) )
        {
        	foreach( $plugindata['css_includes'] as $cssfile )
        	{
        		$css_includes .= "\n<link href='" . $this->controller->makeUrl() . '/plugin/' . $plugindata['directory'] . '/' . $cssfile . "' rel='stylesheet'>";
        	}
        }

        $js_includes = '';
        if( !empty($plugindata['js_includes']) )
        {
        	foreach( $plugindata['js_includes'] as $jsfile )
        	{
        		$js_includes .= "\n<script type='text/javascript' src='" . $this->controller->makeUrl() . '/plugin/' . $plugindata['directory'] . '/' . $jsfile . "'></script>";
        	}
        }

		// Get and generate the requested page content
		ob_start();
       	include(ROOTPATH . $this->controller->route . '.pdt');
        $body = ob_get_clean();

        $exec_time = 'Page generated in ' . round(microtime(true) - SCRIPT_START, 6) . ' seconds';

        // add the content to the template and return it
		ob_start();
        include(ROOTPATH . 'app' . DS . 'views' . DS . $viewdir . DS . 'headerfooter_template.pdt');
        return ob_get_clean();
	}

	/**
	 * Will create a breadcrumb based on the $pages input.
	 * 
	 * @param  array  $pages Contains the breadcrumb title (Key) => and the route (Value) for each item to show on the breadcrumb trail. 
	 * @return html
	 */
	protected function breadcrumb( array $pages = array() )
	{
		if( empty($pages) )
		{
			$pages = array(
				'Home' => $this->controller->makeUrl('home')
			);
		}

		$pagelist = '<ol class="breadcrumb">';
		foreach( $pages as $title => $page )
		{
			$pagelist .= '<li class="' . $this->activePage($page) . '"><a href="' . $this->controller->makeUrl($page) . '"></i> ' . $title . '</a></li>';
		}
		$pagelist .= '</ol>';

		return $pagelist;
	}

	/**
	 * Checks if any of the values in the $pages array matches the current route/page the user is on, and if so return the active class.
	 * @param  array $pages the list of routes to check against.
	 * @return string 		the class "active" to be used in eg. navigations.
	 */
	protected function activePage( $pages )
	{
		$pages = explode(',', $pages);
		return ( in_array($this->controller->route, $pages) ? 'active' : '' );
	}

	/**
	 * Redirect the user to the given $route. Set $force to true to redirect them even if they are already on the given page.
	 * 
	 * @param  string  $route The route to redirect the user to.
	 * @param  boolean $force Whether to force redirect the users even if they are already on this page.
	 */
	public function redirectTo( $route, $force = false )
	{
		if( !Router::exists($route) )
		{
			return;
		}

		if( !$force && $route == $this->controller->route )
		{
			return;
		}

		header('Location: ' . $this->controller->makeUrl( $route ) );
		exit;
	}
}