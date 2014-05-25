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

class ModelsAdminEditaccount extends AppModel
{
	/**
	 * Updates the accounts email and/or password.
	 * 
	 * @return array A status and message for the success/failure
	 */
	public function updateAccount( array $data )
	{
		$passquery = '';
		if( !empty($data['password']) )
		{
			$passquery = 'password = :passwd,';
		}

		$stmt = $this->connection->prepare('UPDATE 
												' . Configure::get('database/prefix') . 'accounts
											SET
												' . $passquery . '
												email = :email,
												email_hash = :hash
											WHERE
												id = :uid');
		$stmt->bindValue(':uid', $data['id'], PDO::PARAM_INT);
		$stmt->bindValue(':email', $data['email'], PDO::PARAM_STR);
		$stmt->bindValue(':hash', $data['hash'], PDO::PARAM_STR);

		if( !empty($data['password']) )
		{
			$stmt->bindValue(':passwd', $data['password'], PDO::PARAM_STR);
		}

		$success = $stmt->execute();
		$affected = $stmt->rowCount();
		$error = $stmt->errorInfo();
		$stmt->closeCursor();

		return array(
			'success' => $success,
			'affected_rows' => $affected,
			'error' => $error
		);
	}

	/**
	 * Get the current accounts details.
	 * 
	 * @param  integer $id The id of the account to get.
	 * @return array
	 */
	public function getAccount( $id )
	{
		$stmt = $this->connection->prepare('SELECT a.id, username, avatar, email, expire FROM ' . Configure::get('database/prefix') . 'accounts as a LEFT JOIN ' . Configure::get('database/prefix') . 'sessions as s ON s.id = a.sessid WHERE a.id = :uid');
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
		$stmt = $this->connection->prepare('SELECT password FROM ' . Configure::get('database/prefix') . 'accounts WHERE id = :uid LIMIT 1');
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
		$stmt = $this->connection->prepare('SELECT id FROM ' . Configure::get('database/prefix') . 'accounts WHERE email_hash = :email LIMIT 1');
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