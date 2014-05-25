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

class ControllersAdminAccountslist extends AppController
{
	/**
	 * Gets the list of accounts.
	 * 
	 * @return array
	 */
	public function getAccountsList()
	{
		if( !$this->hasPermission('admin') )
		{
			return;
		}

		$page = Security::sanitize(Misc::data('page', 'get'), 'integer');
		$limit = 15;
		$offset = ( $page < 1 ? 1 : $page ) * 15 - 15;

		$users = $this->model->getAccounts($offset, $limit);

		$list = array();
		if( !empty($users) )
		{
			foreach( $users as $user )
			{
				$list[$user['id']] = array(
					'id' => $user['id'],
					'username' => $user['username'],
					'online' => ( $user['expire'] < time() + 300 ? true : false )
				);
			}
		}

		return $list;
	}
}