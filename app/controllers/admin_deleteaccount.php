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

class admin_deleteaccount extends AppController
{
	/**
	 * Get the id and username for the account with id: $id
	 * 
	 * @param  integer $id The id of the account to get.
	 * @return array
	 */
	public function getAccount( $id )
	{
		$stmt = $this->model->connection->prepare('SELECT id, username FROM ' . Configure::get('database/prefix') . 'accounts WHERE id = :uid');
		$stmt->bindValue(':uid', Security::sanitize($id, 'integer'), PDO::PARAM_INT);
		$stmt->execute();
		$users = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		return $users;
	}

	/**
	 * Deletes the given account and mailbox.
	 * 
	 * @return array A status and message for the success/failure
	 */
	public function action_deleteaccount()
	{
		if( !$this->hasPermission('admin') )
		{
			return;
		}

		if( !Misc::receivedFields('delete_id', 'post') )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'User not found.'
			);
		}

		if( Misc::data('delete_id', 'post') == Session::get('user/id') )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'You cannot delete your own account.'
			);
		}

		if( !Security::validateToken('deleteaccount') )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'Security Token invalid, please refresh you page.'
			);
		}

		$stmt = $this->model->connection->prepare('DELETE FROM ' . Configure::get('database/prefix') . 'accounts WHERE id = :uid');
		$stmt->bindValue(':uid', Misc::data('delete_id', 'post'), PDO::PARAM_INT);
		$stmt->execute();
		$success = $stmt->rowCount();
		$stmt->closeCursor();

		if( $success )
		{
			$this->redirect = 'admin/accountslist';
			Notifications::set('Account deleted successfully.', '', 'success');
		}
		else
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'Failed to delete account. Maybe the account has already been deleted.'
			);
		}
	}
}