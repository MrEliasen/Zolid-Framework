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

class ModelsUsersSettings extends AppModel
{
	public function updatePassword( array $data )
	{
		$stmt = $this->connection->prepare('UPDATE ' . Configure::get('database/prefix') . 'accounts SET password = :passwd WHERE id = :uid LIMIT 1');
		$stmt->bindValue(':uid', $data['userid'], PDO::PARAM_INT);
		$stmt->bindValue(':passwd', $data['password'], PDO::PARAM_STR);
		$stmt->execute();
		$this->lastError = $stmt->errorInfo();
		$stmt->closeCursor();

		return $stmt->rowCount();
	}

	public function updateEmail( array $data )
	{
		$stmt = $this->connection->prepare('UPDATE ' . Configure::get('database/prefix') . 'accounts SET email = :email, email_hash = :hash WHERE id = :uid LIMIT 1');
		$stmt->bindValue(':uid', $data['userid'], PDO::PARAM_INT);
		$stmt->bindValue(':email', $data['email'], PDO::PARAM_STR);
		$stmt->bindValue(':hash', $data['hash'], PDO::PARAM_STR);
		$stmt->execute();
		$this->lastError = $stmt->errorInfo();
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

	public function getPassword( $userid )
	{
		$stmt = $this->connection->prepare('SELECT password FROM ' . Configure::get('database/prefix') . 'accounts WHERE id = :uid LIMIT 1');
		$stmt->bindValue(':uid', $userid, PDO::PARAM_INT);
		$stmt->execute();
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		return $data;
	}

	public function updateAvatar( array $data )
	{
		$stmt = $this->connection->prepare('SELECT avatar FROM ' . Configure::get('database/prefix') . 'accounts WHERE id = :uid');
    	$stmt->bindValue(':uid', $data['userid'], PDO::PARAM_INT);
    	$stmt->execute();
    	$oldavatar = $stmt->fetch(PDO::FETCH_ASSOC);
    	$stmt->closeCursor();

        // remove old avatar
        if( !empty($oldavatar['avatar']) && file_exists($data['avatarDir'] . $oldavatar['avatar']) )
        {
            @unlink($data['avatarDir'] . $oldavatar['avatar']);
        }

        $stmt = $this->connection->prepare('UPDATE ' . Configure::get('database/prefix') . 'accounts SET avatar = :avatar WHERE id = :uid');
    	$stmt->bindValue(':avatar', $data['file'], PDO::PARAM_STR);
    	$stmt->bindValue(':uid', $data['userid'], PDO::PARAM_INT);
    	$stmt->execute();
    	$stmt->closeCursor();

    	return true;
	}
}