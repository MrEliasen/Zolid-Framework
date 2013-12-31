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

class users_logout extends AppController
{
	/**
	 * Logs out the user and redirect them to the default route.
	 */
	protected function action_logout()
	{
		if( !Security::validateToken('logout') )
		{
			return;
		}
		Session::destroyMe();
		$this->redirect = Configure::get('views/default_route');
	}
}