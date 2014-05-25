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

class ModelsUsersLogin extends AppModel
{
	public function activateAccount( $uid, $token )
	{
		$stmt = $this->connection->prepare('UPDATE ' . Configure::get('database/prefix') . 'accounts SET active = "" WHERE id = :uid AND active = :token LIMIT 1');
		$stmt->bindValue(':uid', $uid, PDO::PARAM_INT);
		$stmt->bindValue(':token', $token, PDO::PARAM_STR);
		$stmt->execute();
		$stmt->closeCursor();

		return $stmt->rowCount();
	}

	public function login( $email )
	{
		$stmt = $this->connection->prepare('SELECT id, password, active FROM ' . Configure::get('database/prefix') . 'accounts WHERE email_hash = :email LIMIT 1');
		$stmt->bindValue(':email', $email, PDO::PARAM_STR);
		$stmt->execute();
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		return $data;
	}
}