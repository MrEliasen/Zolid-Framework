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

class ModelsUsersMailbox extends AppModel
{
	/**
	 * Loads any profile related details for the account with id: $id
	 * WARNING! Be careful with what date you pull out from the database and show to the public..
	 * 
	 * @param  integer $id The account id.
	 * @return array       The details gathered from the database.
	 */
	public function sendMail( array $data )
	{
		$stmt = $this->connection->prepare('INSERT INTO ' . Configure::get('database/prefix') . 'mailbox 
																(recipent, sender, message, date)
															VALUES
																(( SELECT id FROM ' . Configure::get('database/prefix') . 'accounts WHERE username = :to LIMIT 1), :from, :msg, :date)');
		$stmt->bindValue(':from', $data['from'], PDO::PARAM_INT);
		$stmt->bindValue(':date', time(), PDO::PARAM_INT);
		$stmt->bindValue(':to', $data['to'], PDO::PARAM_STR);
		$stmt->bindValue(':msg', $data['message'], PDO::PARAM_STR);
		$stmt->execute();
		$stmt->closeCursor();
		
		return $stmt->rowCount();
	}

	public function deleteMail( array $data )
	{
		$stmt = $this->connection->prepare('DELETE FROM ' . Configure::get('database/prefix') . 'mailbox WHERE id = :mid AND recipent = :uid LIMIT 1');
		$stmt->bindValue(':mid', $data['msgid'], PDO::PARAM_INT);
		$stmt->bindValue(':uid', $data['userid'], PDO::PARAM_INT);
		$stmt->execute();
		$stmt->closeCursor();

		return $stmt->rowCount();
	}

	public function getMailboxMails( $id )
	{
		$stmt = $this->connection->prepare('SELECT m.id, date, username, LEFT(message, 55) as title, sender, isread FROM ' . Configure::get('database/prefix') . 'mailbox as m LEFT JOIN ' . Configure::get('database/prefix') . 'accounts as u ON u.id = sender WHERE recipent = :uid ORDER BY id DESC');
		$stmt->bindValue(':uid', $id, PDO::PARAM_INT);
		$stmt->execute();
		$mails = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		return $mails;
	}

	public function getMail( $msgid, $userid )
	{
		$stmt = $this->connection->prepare('SELECT m.id, date, message, isread, username, sender FROM ' . Configure::get('database/prefix') . 'mailbox as m LEFT JOIN ' . Configure::get('database/prefix') . 'accounts as a ON a.id = sender WHERE m.id = :mid AND recipent = :uid LIMIT 1');
		$stmt->bindValue(':mid', $msgid, PDO::PARAM_INT);
		$stmt->bindValue(':uid', $userid, PDO::PARAM_INT);
		$stmt->execute();
		$mail = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		return $mail;
	}

	public function markAsRead( $id )
	{
		$stmt = $this->connection->prepare('UPDATE ' . Configure::get('database/prefix') . 'mailbox SET isread = "1" WHERE id = :mid LIMIT 1');
		$stmt->bindValue(':mid', $id, PDO::PARAM_INT);
		$stmt->execute();
		$stmt->closeCursor();

		return $stmt->rowCount();
	}
}