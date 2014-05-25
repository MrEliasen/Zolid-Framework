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

class ModelsUsersSignup extends AppModel
{
	public function signup( array $data )
	{
		$stmt = $this->connection->prepare('INSERT INTO ' . Configure::get('database/prefix') . 'accounts SET username = :user, email = :email, email_hash = :emailhash, password = :passwd, permissions = :perms, active = :active');
		$stmt->bindValue(':user', $data['username'], PDO::PARAM_STR);
		$stmt->bindValue(':email', $data['email'], PDO::PARAM_STR);
		$stmt->bindValue(':emailhash', $data['email_hashed'], PDO::PARAM_STR);
		$stmt->bindValue(':passwd', $data['password'], PDO::PARAM_STR);
		$stmt->bindValue(':perms', $data['permissions'], PDO::PARAM_STR);
		$stmt->bindValue(':active', $data['activate_code'], PDO::PARAM_INT);
		$stmt->execute();
		$stmt->closeCursor();

		return $stmt->rowCount();
	}

	public function checkEmail( $email )
	{
		$stmt = $this->connection->prepare('SELECT id FROM ' . Configure::get('database/prefix') . 'accounts WHERE email_hash = :email LIMIT 1');
		$stmt->bindValue(':email', $email, PDO::PARAM_STR);
		$stmt->execute();
		$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		return count($data);
	}

	public function checkUsername( $username )
	{
		$stmt = $this->connection->prepare('SELECT id FROM ' . Configure::get('database/prefix') . 'accounts WHERE username = :user LIMIT 1');
		$stmt->bindValue(':user', $username, PDO::PARAM_STR);
		$stmt->execute();
		$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		return count($data);
	}
}