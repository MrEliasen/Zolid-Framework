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

class ControllersUsersProfile extends AppController
{
	/**
	 * Loads any profile related details for the account with id: $id
	 * WARNING! Be careful with what date you pull out from the database and show to the public..
	 * 
	 * @param  integer $id The account id.
	 * @return array       The details gathered from the database.
	 */
	public function getProfile( $id )
	{
		$user = $this->model->getProfile(Security::sanitize($id, 'integer'));
		return $user;
	}
}