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

	public function __construct($model, $controller)
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

		// Does the route exists?
		if( !Router::exists($this->controller->redirect) )
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
        $template = explode('/', $this->controller->route);
        $viewdir = (array)array_shift($template);
        $viewdir = implode('', $viewdir);

        if( !in_array($viewdir, array('users', 'admin') ))
        {
        	$viewdir = 'users';
        }

        if( $viewdir == 'admin' && !$this->controller->hasPermission('admin') )
        {
        	$viewdir = 'users';
        	$this->controller->route = 'errors/403';
        }

		// Get and generate the requested page content
		ob_start();
        include(ROOTPATH . 'app' . DS . 'views' . DS . $this->controller->route . '.pdt');
        $body = ob_get_clean();

        // In case we use the same template in the admin directory, make the system know.
        if( !is_readable(ROOTPATH . 'app' . DS . 'views' . DS . $viewdir . DS . 'views_template.pdt') )
        {
        	$viewdir = 'users';
        }

        // add the content to the template and return it
		ob_start();
        include(ROOTPATH . 'app' . DS . 'views' . DS . $viewdir . DS . 'views_template.pdt');
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