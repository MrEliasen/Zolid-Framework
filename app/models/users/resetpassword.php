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

class ModelsUsersResetpassword extends AppModel
{
	public function resetpassword( array $data )
	{
		$stmt = $this->connection->prepare('UPDATE ' . Configure::get('database/prefix') . 'accounts SET password = :passwd, resettoken = "", resetexpire = "" WHERE id = :uid AND resettoken = :token AND resetexpire >= :date');
		$stmt->bindValue(':uid', $data['userid'], PDO::PARAM_INT);
		$stmt->bindValue(':token', $data['token'], PDO::PARAM_STR);
		$stmt->bindValue(':passwd', $data['password'], PDO::PARAM_STR);
		$stmt->bindValue(':date', $data['time'], PDO::PARAM_INT);
		$stmt->execute();
		$stmt->closeCursor();

		return $stmt->rowCount();
	}
}