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

class ControllersUsersLogout extends AppController
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