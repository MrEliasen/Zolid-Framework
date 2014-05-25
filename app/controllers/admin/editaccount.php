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

class ControllersAdminEditaccount extends AppController
{
	/**
	 * Updates the accounts email and/or password.
	 * 
	 * @return array A status and message for the success/failure
	 */
	public function action_updateaccount()
	{
		if( !$this->hasPermission('admin') )
		{
			return;
		}

		if( !Misc::receivedFields('updateaccount_email,updateaccount_current', 'post') || !Misc::receivedFields('id', 'get') )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'Please fill out all the required fields.'
			);
		}

		$passquery = '';
		if( Misc::data('updateaccount_pass', 'post') != '' )
		{
			if( strlen(Misc::data('updateaccount_pass')) < Configure::get('security/min_password_length') )
			{
				return array(
					'status' => false,
					'message_type' => 'error',
					'message_title' => '',
					'message_body' => 'Please use a password which consist of at least 6 characters.'
				);
			}

			$passquery = 'password = :passwd,';
		}

		if( !Security::validateEmail(Misc::data('updateaccount_email', 'post')) )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'The email address you entered does not appear to be valid.'
			);
		}

		$hash = Security::hash(Misc::data('updateaccount_email'));

		if( $this->emailInUse($hash, Security::sanitize(Misc::data('id', 'get'), 'integer')) )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'That email is already in use by a different account.'
			);
		}

		if( !$this->validatePassword(Misc::data('updateaccount_current', 'post'), Session::get('user/id')) )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'The password was invalid, please try again.'
			);
		}

		$data = array(
			'id' => Security::sanitize(Misc::data('id', 'get'), 'integer'),
			'email' => Security::encryptData(Misc::data('updateaccount_email')),
			'hash' => $hash,
			'password' => ''
		);

		if( !empty($passquery) )
		{
			$data['password'] = password_hash(Misc::data('updateaccount_pass'), PASSWORD_BCRYPT, array('cost' => Configure::get('security/hash_cost') ), PDO::PARAM_STR);
		}

		$success = $this->model->updateAccount($data);
		if( $success['affected_rows'] > 0 || empty($success['error'][2]) )
		{
			return array(
				'status' => true,
				'message_type' => 'success',
				'message_title' => 'Account Updated',
				'message_body' => 'The account has been updated successfully!'
			);
		}
		else
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'An error occured while trying to update your password.'
			);
		}
	}

	/**
	 * Get the current accounts details.
	 * 
	 * @param  integer $id The id of the account to get.
	 * @return array
	 */
	public function getAccount( $id )
	{
		$stmt = $this->model->connection->prepare('SELECT a.id, username, avatar, email, expire FROM ' . Configure::get('database/prefix') . 'accounts as a LEFT JOIN ' . Configure::get('database/prefix') . 'sessions as s ON s.id = a.sessid WHERE a.id = :uid');
		$stmt->bindValue(':uid', Security::sanitize($id, 'integer'), PDO::PARAM_INT);
		$stmt->execute();
		$users = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		if( !empty($users) )
		{
			$users['email'] = Security::decryptData($users['email']);
		}

		return $users;
	}

	/**
	 * Checks if the password matches the password of the $userid account
	 * 
	 * @param  string $password Non-hashed password .
	 * @param  integer $userid  The id of the account who's password to check against.
	 * @return boolean          True if the password match, else false.
	 */
	private function validatePassword( $password, $userid )
	{
		$stmt = $this->model->connection->prepare('SELECT password FROM ' . Configure::get('database/prefix') . 'accounts WHERE id = :uid LIMIT 1');
		$stmt->bindValue(':uid', $userid, PDO::PARAM_INT);
		$stmt->execute();
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		if( !password_verify($password, $data['password']) )
		{
			return false;
		}

		return true;
	}

	/**
	 * Checks if the given $email is already in use by anyone else by user $id
	 * 
	 * @param  string $email The email address to check
	 * @param  integer $id   The id of the user who is "allowed" to already have this email
	 * @return boolean       True if the email is in use by another account, else false.
	 */
	private function emailInUse( $email, $id )
	{
		$stmt = $this->model->connection->prepare('SELECT id FROM ' . Configure::get('database/prefix') . 'accounts WHERE email_hash = :email LIMIT 1');
		$stmt->bindValue(':email', $email, PDO::PARAM_STR);
		$stmt->execute();
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		if( !empty($data) && $data['id'] != $id )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}