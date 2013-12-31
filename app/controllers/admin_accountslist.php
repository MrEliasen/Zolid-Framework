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

class admin_accountslist extends AppController
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

		$stmt = $this->model->connection->prepare('SELECT a.id, username, expire FROM ' . Configure::get('database/prefix') . 'accounts as a LEFT JOIN ' . Configure::get('database/prefix') . 'sessions as s ON s.id = a.sessid ORDER BY id ASC LIMIT ' . $offset . ', ' . $limit);
		$stmt->execute();
		$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

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