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

class ControllersUsersResetpassword extends AppController
{
	/**
	 * Reset the accounts password if the correct token for the given account (part of the token) is provided.
	 * 
	 * @return array A status and message for the success/failure.
	 */
	public function action_resetpassword()
	{
		if( !Misc::receivedFields('reset_password', 'post') || !Misc::receivedFields('pwreset', 'get') )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => 'Error',
				'message_body' => 'An error occured while trying to reset your, please try again.'
			);
		}

		if( strlen($_GET['pwreset']) <= 40 )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'Invalid reset token.'
			);
		}

		$success = $this->model->resetpassword(array(
			'token' => Security::sanitize(substr($_GET['pwreset'], 0, 40), 'string'),
			'userid' => base64_decode(substr($_GET['pwreset'], 40)),
			'password' => password_hash(Misc::data('reset_password'), PASSWORD_BCRYPT, array('cost' => Configure::get('security/hash_cost') )),
			'time' => time()
		));

		if( !$success )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => 'Error',
				'message_body' => 'An error occured while trying to reset your, please try again.'
			);
		}
		else
		{
			Notifications::set('Your password has been reset, and you have been logged in.', 'Success', 'success' );
			$this->forceLogin(Security::sanitize($userid, 'integer'));
			$this->redirect = 'users/dashboard';
		}
	}
}