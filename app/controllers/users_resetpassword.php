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

class users_resetpassword extends AppController
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

		$token = substr($_GET['pwreset'], 0, 40);
		$userid = base64_decode(substr($_GET['pwreset'], 40));

		$stmt = $this->model->connection->prepare('UPDATE ' . Configure::get('database/prefix') . 'accounts SET password = :passwd, resettoken = "", resetexpire = "" WHERE id = :uid AND resettoken = :token AND resetexpire >= :date');
		$stmt->bindValue(':uid', Security::sanitize($userid, 'integer'), PDO::PARAM_INT);
		$stmt->bindValue(':token', Security::sanitize($token, 'string'), PDO::PARAM_STR);
		$stmt->bindValue(':passwd', password_hash(Misc::data('reset_password'), PASSWORD_BCRYPT, array('cost' => Configure::get('security/hash_cost') )), PDO::PARAM_STR);
		$stmt->bindValue(':date', time(), PDO::PARAM_INT);
		$stmt->execute();
		$success = $stmt->rowCount();
		$stmt->closeCursor();

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