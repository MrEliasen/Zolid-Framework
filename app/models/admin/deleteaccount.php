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

class ModelsAdminDeleteaccount extends AppModel
{
	/**
	 * Get the id and username for the account with id: $id
	 * 
	 * @param  integer $id The id of the account to get.
	 * @return array
	 */
	public function getAccount( $id )
	{
		$stmt = $this->connection->prepare('SELECT id, username FROM ' . Configure::get('database/prefix') . 'accounts WHERE id = :uid');
		$stmt->bindValue(':uid', $id, PDO::PARAM_INT);
		$stmt->execute();
		$user = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		return $user;
	}

	/**
	 * Deletes the given account and mailbox.
	 * 
	 * @return array A status and message for the success/failure
	 */
	public function action_deleteaccount( $id )
	{
		$stmt = $this->connection->prepare('DELETE FROM ' . Configure::get('database/prefix') . 'accounts WHERE id = :uid');
		$stmt->bindValue(':uid', $id, PDO::PARAM_INT);
		$stmt->execute();
		$success = $stmt->rowCount();
		$stmt->closeCursor();

		return $success;
	}
}