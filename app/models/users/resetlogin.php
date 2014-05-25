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

class ModelsUsersResetlogin extends AppModel
{
	public function addResetToken( array $data )
	{
		$stmt = $this->connection->prepare('UPDATE ' . Configure::get('database/prefix') . 'accounts SET resettoken = :token, resetexpire = :date WHERE email_hash = :email LIMIT 1');
		$stmt->bindValue(':token', $data['token'], PDO::PARAM_STR);
		$stmt->bindValue(':date', $data['timeout'], PDO::PARAM_INT);
		$stmt->bindValue(':email', $data['email'], PDO::PARAM_STR);
		$stmt->execute();
		$stmt->closeCursor();

		return $stmt->rowCount();
	}

	public function fetchAccountId( $email )
	{
		$stmt = $this->connection->prepare('SELECT id FROM ' . Configure::get('database/prefix') . 'accounts WHERE email_hash = :email LIMIT 1');
		$stmt->bindValue(':email', $email, PDO::PARAM_STR);
		$stmt->execute();
		$user = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		if( empty($user['id']) )
		{
			$user['id'] = 0;
		}

		return $user['id'];
	}
}