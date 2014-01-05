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

class users_mailbox extends AppController
{
	/**
	 * Send a new "mail" to the account specified by the user, if the requirements are met.
	 * 
	 * @return array A status and message for the success/failure
	 */
	public function action_newmail()
	{
		if( !$this->loggedin )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'Your are not logged in.'
			);
		}

		if( !Misc::receivedFields('newmail_msg,newmail_recipent', 'post') )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'Please fill out all the fields'
			);
		}

		if( !Security::validateToken('newmail', true) )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'Security Token invalid, please refresh you page.'
			);
		}

		$stmt = $this->model->connection->prepare('INSERT INTO ' . Configure::get('database/prefix') . 'mailbox 
																(recipent, sender, message, date)
															VALUES
																(( SELECT id FROM ' . Configure::get('database/prefix') . 'accounts WHERE username = :to LIMIT 1), :from, :msg, :date)');
		$stmt->bindValue(':from', Security::sanitize(Session::get('user/id'), 'integer'), PDO::PARAM_INT);
		$stmt->bindValue(':date', time(), PDO::PARAM_INT);
		$stmt->bindValue(':to', Security::sanitize(Misc::data('newmail_recipent', 'post'), 'purestring'), PDO::PARAM_STR);
		$stmt->bindValue(':msg', Security::sanitize(Misc::data('newmail_msg', 'post'), 'string'), PDO::PARAM_STR);
		$stmt->execute();
		$success = $stmt->rowCount();
		$stmt->closeCursor();

		if( $success )
		{
			return array(
				'status' => true,
				'message_type' => 'success',
				'message_title' => '',
				'message_body' => 'Your message was sent!',
				'reset' => true
			);
		}
		else
		{
			return array(
				'status' => false,
				'message_type' => 'warning',
				'message_title' => '',
				'message_body' => 'Unable to deliver message. Make sure the username is correct and try again later.'
			);
		}
	}

	/**
	 * Deletes the user's given "mail" if the requirements are met.
	 * 
	 * @return array A status and message for the success/failure
	 */
	public function action_deletemail()
	{
		if( !$this->loggedin )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'Your are not logged in.'
			);
		}

		if( Misc::data('msgid', 'post') == '' )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'You have not selected a message.'
			);
		}

		if( !Security::validateToken('deletemsg') )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'Security Token invalid, please reload the message.'
			);
		}

		$stmt = $this->model->connection->prepare('DELETE FROM ' . Configure::get('database/prefix') . 'mailbox WHERE id = :mid AND recipent = :uid LIMIT 1');
		$stmt->bindValue(':mid', Security::sanitize(Misc::data('msgid', 'post'), 'integer'), PDO::PARAM_INT);
		$stmt->bindValue(':uid', Security::sanitize(Session::get('user/id'), 'integer'), PDO::PARAM_INT);
		$stmt->execute();
		$success = $stmt->rowCount();
		$stmt->closeCursor();

		if( $success )
		{
			return array(
				'status' => true,
				'message_type' => 'success',
				'message_title' => '',
				'message_body' => 'The message was deleted.'
			);
		}
		else
		{
			return array(
				'status' => false,
				'message_type' => 'warning',
				'message_title' => '',
				'message_body' => 'Unable to delete message. It may already have been deleted.'
			);
		}
	}

	/**
	 * Loads the users given mail.
	 * 
	 * @return array A status and message for the success/failure, or the "mails" data.
	 */
	public function action_getmail()
	{
		if( !$this->loggedin )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'Your are not logged in.'
			);
		}

		if( Misc::data('msgid', 'post') == '' )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'You have not selected a message.'
			);
		}

		$stmt = $this->model->connection->prepare('SELECT m.id, date, message, isread, username, sender FROM ' . Configure::get('database/prefix') . 'mailbox as m LEFT JOIN ' . Configure::get('database/prefix') . 'accounts as a ON a.id = sender WHERE m.id = :mid AND recipent = :uid LIMIT 1');
		$stmt->bindValue(':mid', Security::sanitize(Misc::data('msgid', 'post'), 'integer'), PDO::PARAM_INT);
		$stmt->bindValue(':uid', Security::sanitize(Session::get('user/id'), 'integer'), PDO::PARAM_INT);
		$stmt->execute();
		$mail = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		if( empty($mail) )
		{
			$mail = array(
				'status' => false,
				'message_type' => 'warning',
				'message_title' => '',
				'message_body' => 'This message no longer exist.'
			);
		}
		else
		{
			// Mark the message as read if it is not so already.
			if( !$mail['isread'] )
			{
				$stmt = $this->model->connection->prepare('UPDATE ' . Configure::get('database/prefix') . 'mailbox SET isread = "1" WHERE id = :mid LIMIT 1');
				$stmt->bindValue(':mid', $mail['id'], PDO::PARAM_INT);
				$stmt->execute();
				$stmt->closeCursor();
			}

			// Add line breads etc, where needed.
			$mail['message'] = nl2br($mail['message']);
			$mail['token'] = Security::newToken('deletemsg');
		}
		return $mail;
	}

	/**
	 * Loads the users mailbox.
	 * 
	 * @param  integer $id The id of the user who's mailbox to load.
	 * @return array       The mailbox data.
	 */
	public function getMailbox( $id )
	{
		if( !$this->loggedin )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'Your are not logged in.'
			);
		}
		
		$stmt = $this->model->connection->prepare('SELECT m.id, date, username, LEFT(message, 55) as title, sender, isread FROM ' . Configure::get('database/prefix') . 'mailbox as m LEFT JOIN ' . Configure::get('database/prefix') . 'accounts as u ON u.id = sender WHERE recipent = :uid ORDER BY id DESC');
		$stmt->bindValue(':uid', Security::sanitize($id, 'integer'), PDO::PARAM_INT);
		$stmt->execute();
		$mails = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		$maillist = array(
			'unread' => 0,
			'mails' => array()
		);
		if( !empty($mails) )
		{
			foreach( $mails as $mail )
			{
				$maillist['mails'][ $mail['id'] ] = array(
					'id' => $mail['id'],
					'title' => Security::sanitize( strlen($mail['title']) > 54 ? substr($mail['title'], 0, 48) . ' [...]' : $mail['title'], 'purestring'),
					'username' => ( empty($mail['username']) ? 'Unknown User' : $mail['username'] ),
					'date' => date('H:i:s - d-m-Y', $mail['date']),
					'sender' => $mail['sender'],
					'isread' => (bool)$mail['isread']
				);

				if( !$mail['isread'] )
				{
					$maillist['unread']++;
				}
			}

			unset($mails);
		}

		return $maillist;
	}
}