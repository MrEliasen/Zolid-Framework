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

class users_login extends AppController
{
	protected function preAction()
	{
		Configure::load('users');
		parent::preAction();
	}

	/**
	 * Activates the users account if the activation url provided is correct.
	 * 
	 * @return array A status and message for the success/failure
	 */
	public function action_activate()
	{
		if( !Misc::receivedFields('token', 'get') )
		{
			return array(
				'status' => false,
				'message_body' => 'Invalid activation url.',
				'message_title' => 'Error',
				'message_type' => 'error'
			);
		}

		if( strlen(Misc::data('token', 'get')) <= 35 )
		{
			return array(
				'status' => false,
				'message_body' => 'Invalid activation url.',
				'message_title' => 'Error',
				'message_type' => 'error'
			);
		}

		$token = Security::sanitize(Misc::data('token', 'get'), 'purestring');
		$key = substr($token, 0, 35);
		$uid = intval(@base64_decode(substr($token, 35)));

		if( !$uid || empty($key) )
		{
			return array(
				'status' => false,
				'message_body' => 'Invalid activation url.',
				'message_title' => 'Error',
				'message_type' => 'error'
			);
		}

		$stmt = $this->model->connection->prepare('UPDATE ' . Configure::get('database/prefix') . 'accounts SET active = "" WHERE id = :uid AND active = :token LIMIT 1');
		$stmt->bindValue(':uid', $uid, PDO::PARAM_INT);
		$stmt->bindValue(':token', $key, PDO::PARAM_STR);
		$stmt->execute();
		$success = $stmt->rowCount();
		$stmt->closeCursor();

		if( $success )
		{
			return array(
				'status' => true,
				'message_body' => 'Your account has been activated! You may now login.',
				'message_title' => '',
				'message_type' => 'success'
			);
		}
		else
		{
			return array(
				'status' => false,
				'message_body' => 'Invalid activation url. Your account may already have been activated.',
				'message_title' => 'Error',
				'message_type' => 'error'
			);
		}
	}

	/**
	 * Login the user if the login details they provide are correct.
	 * 
	 * @return array A status and message for the success/failure
	 */
	public function action_login()
	{
		if( !Misc::receivedFields('login_email,login_password', 'post') )
		{
			return array(
				'status' => false,
				'message_body' => 'Please fillout all the required fields.',
				'message_title' => 'Login Failed',
				'message_type' => 'error'
			);
		}

		if( !Security::validateToken('login') )
		{
			return array(
				'status' => false,
				'message_body' => 'The security token is invalid, please refresh the page and try again.',
				'message_title' => 'Login Failed',
				'message_type' => 'error'
			);
		}

		$email = Security::hashEmail(Misc::data('login_email', 'post'));

		$stmt = $this->model->connection->prepare('SELECT id, password, active FROM ' . Configure::get('database/prefix') . 'accounts WHERE email_hash = :email LIMIT 1');
		$stmt->bindValue(':email', $email, PDO::PARAM_STR);
		$stmt->execute();
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		if( !password_verify(Misc::data('login_password', 'post'), $data['password']) )
		{
			return array(
				'status' => false,
				'message_body' => 'The email and password combination did not match any records.',
				'message_title' => 'Login Failed',
				'message_type' => 'error'
			);
		}

		if( !empty($data['active']) )
		{
			return array(
				'status' => false,
				'message_body' => 'Your account has not yet been activated. An email should have arrived in you inbox (or spam folder) containing an activation link.',
				'message_title' => 'Account Inactive',
				'message_type' => 'warning'
			);
		}

		$this->forceLogin($data['id']);
		$this->redirect = 'users/dashboard';
	}
}