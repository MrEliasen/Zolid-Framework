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

class ControllersAdminDeleteaccount extends AppController
{
	/**
	 * Get the id and username for the account with id: $id
	 * 
	 * @param  integer $id The id of the account to get.
	 * @return array
	 */
	public function getAccount( $id )
	{
		return $this->model->getAccount( Security::sanitize($id, 'integer') );
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

		$success = $this->model->deleteAccount( Security::sanitize(Misc::data('delete_id', 'post'), 'integer') );

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