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

class ModelsUsersProfile extends AppModel
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
		$stmt = $this->connection->prepare('SELECT a.id, username, avatar, created, expire FROM ' . Configure::get('database/prefix') . 'accounts as a LEFT JOIN ' . Configure::get('database/prefix') . 'sessions as s ON s.id = a.sessid WHERE a.id = :uid');
		$stmt->bindValue(':uid', $id, PDO::PARAM_INT);
		$stmt->execute();
		$user = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		return $user;
	}
}