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

class users_resetlogin extends AppController
{
	/**
	 * Will generate a reset token for the given account and email the user with the reset url for them to reset their account password.
	 * 
	 * @return array A status and message for the success/failure.
	 */
	public function action_resetlogin()
	{
		if( !Misc::receivedFields('reset_email', 'post') )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'Please fill out the email associated with your account.'
			);
		}

		if( !Security::validateEmail($_POST['reset_email']) )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'The email address you entered does not appear to be valid.'
			);
		}

		if( !Security::validateToken('resetlogin') )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'Security token was invalid, please try again.'
			);
		}

		$resettoken = Security::randomGenerator(40, true);
		$timeout = time() + Configure::get('users/reset_timeout');

		$stmt = $this->model->connection->prepare('UPDATE ' . Configure::get('database/prefix') . 'accounts SET resettoken = :token, resetexpire = :date WHERE email_hash = :email LIMIT 1');
		$stmt->bindValue(':token', $resettoken, PDO::PARAM_STR);
		$stmt->bindValue(':date', $timeout, PDO::PARAM_INT);
		$stmt->bindValue(':email', Security::hashEmail($_POST['reset_email']), PDO::PARAM_STR);
		$stmt->execute();
		$rows = $stmt->rowCount();
		$stmt->closeCursor();

		if( $rows > 0 )
		{
			$stmt = $this->model->connection->prepare('SELECT id FROM ' . Configure::get('database/prefix') . 'accounts WHERE email_hash = :email LIMIT 1');
			$stmt->bindValue(':email', Security::hashEmail($_POST['reset_email']), PDO::PARAM_STR);
			$stmt->execute();
			$user = $stmt->fetch(PDO::FETCH_ASSOC);
			$stmt->closeCursor();

			$values = array(
				'reset_timeout' => date('d/m/Y - H:i', $timeout),
				'reset_url' => $this->makeUrl('users/resetpassword') . '&pwreset=' . $resettoken . base64_encode($user['id'])
			);

			$mailsend = $this->sendEmail($_POST['reset_email'], 'Password Reset Request', 'resetpassword', $values);
			if( $mailsend['status'] )
			{
				return array(
					'status' => false,
					'message_type' => 'success',
					'message_title' => 'Success',
					'message_body' => 'An email has been sent containing the reset link. <b>Please check your spam folder</b> in case the email ends up in there.'
				);
			}
			else
			{
				return array(
					'status' => false,
					'message_type' => 'error',
					'message_title' => 'Error',
					'message_body' => 'An error occured while trying to send your password reset link. Please contact support. Err: ' . $mailsend['message']
				);
			}
		}
		else
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => 'Failed',
				'message_body' => 'We do not have any account on file using that email address.'
			);
		}
	}
}