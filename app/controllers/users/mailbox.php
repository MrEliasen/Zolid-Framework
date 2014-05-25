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

class ControllersUsersMailbox extends AppController
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

		$success = $this->model->sendMail(array(
			'from' => Security::sanitize(Session::get('user/id'), 'integer'),
			'data' => time(),
			'to' => Security::sanitize(Misc::data('newmail_recipent', 'post'), 'purestring'),
			'message' => Security::sanitize(Misc::data('newmail_msg', 'post'), 'string')
		));

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

		$success = $this->model->deleteMail(array(
			'msgid' => Security::sanitize(Misc::data('msgid', 'post'), 'integer'),
			'userid' => Security::sanitize(Session::get('user/id'), 'integer')
		));

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

		$mail = $this->model->getMail(Security::sanitize(Misc::data('msgid', 'post'), 'integer'), Security::sanitize(Session::get('user/id'), 'integer'));

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
				$this->model->markAsRead($mail['id']);
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
		
		$mails = $this->model->getMailboxMails( $id );

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