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

class AppController
{
	/**
	 * Holds the current route.
	 * @var [type]
	 */
	public $route;

	/**
	 * Holds the model object.
	 * @var Object
	 */
    protected $model;

	/**
	 * Holds the action to execute.
	 * @var string
	 */
    protected $action;

	/**
	 * Holds the boolean for whether the user is logged in or not.
	 * @var boolean
	 */
    public $loggedin = false;

	/**
	 * Holds all the output, to be potentially sent back to the View.
	 * @var string
	 */
    public $output;

    /**
     * Holds the account permissions. Grapped on each page load.
     * @var array
     */
   	public $permissions = array();

	/**
	 * Holds the redirection commands to the view
	 * @var string
	 */
    public $redirect;
 
    public function __construct($model, $route)
    {
        $this->model = $model;
		$this->route = str_replace(DS, '/', $route);
        $this->action = Router::getAction();
        $this->checkLoginState();

        $this->preAction();
        $this->action();
        $this->postAction();
    }

    /**
     * This metod runs before any "action", like login, signup, password reset, and so on.
     * The preaction should be either replaced or extended (recommended) by any inhereting controller.
     */
	protected function preAction()
	{
		// To be replaced/extended by the inheriting controller if required. This function will fire before the "action"
		if( $this->model->installed && is_readable(ROOTPATH . DS . 'app' . DS . 'views' . DS . 'users' . DS . 'install.pdt') ||  is_readable(ROOTPATH . DS . 'app' . DS . 'controllers' . DS . 'users_install.pdt') )
		{
			Notifications::set('The install files have not been deleted. Please remove the follwing files: <b>' . ROOTPATH . 'app' . DS . 'views' . DS . 'users' . DS . 'install.pdt</b> AND <b>' . ROOTPATH . 'app' . DS . 'controllers' . DS . 'users_install.php</b>.', 'WARNING!', 'error');
			$this->redirect = 'users/home';
			$this->action = null;
		}
		else
		{
			if( !$this->model->installed && $this->route !== 'users/install' )
			{
				$this->redirect = 'users/install';
			}
		}
	}

	/**
	 * Will execute (if possible) the users "action" request if found and available in the inheriting controller
	 */
	final protected function action()
	{
		// check if we have received any actions we need to perform
		if( empty($this->action['action']) )
		{
			return;
		}

		// Do not allow the user to execute private/protected functions.
		if( !is_callable(array($this, $this->action['action'])) )
		{
			$this->output = array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'Unable to perform that action.'
			);
		}
		else
		{
			$action = $this->action['action'];
			$this->output = $this->$action();
		}
	}

	/**
	 * This method is executed after the action has been processed, taking the output from the action and either returning it to the user as json (ajax) or notifications.
	 */
	public function postAction()
	{
		// To be extended by the inheriting controller if required. This function will fire before the "action"
		if( !empty($this->action['action']) && $this->action['ajax'] )
		{
			// if it's an ajax request, json encode the output, echo it out right away, and stop.
			echo json_encode($this->output);
			exit;
		}
		else if( isset($this->output['message_body']) && isset($this->output['message_title']) && isset($this->output['message_type']) )
		{
			// If its not an json request, and if the message parameters are set, create a notification.
			Notifications::set($this->output['message_body'], $this->output['message_title'], $this->output['message_type']);
			$this->output = null;
		}
	}

	/**
	 * Prepares the $body text by replacing any of the {{keys}} with their associated value.
	 * 
	 * @param  string $body         The body/text you want to prepare.
	 * @param  array  $customvalues If you have any additional values + keys you use in your $body
	 * @return string               The prepared $body text.
	 */
	private function prepareEmail( $body, array $customvalues = array() )
	{
		// These are values which will always be available to use in email templates.
		$values = array(
			'ip_address' => $_SERVER['REMOTE_ADDR'],
			'email_date' => date('M jS Y', time()),
			'email_time' => date('g:iA e', time()),
			'base_url' => $this->makeUrl(),
			'site_name' => Configure::get('core/site_title')
		);

		// However you can add your own as well based on the indidual emails.
		if( !empty( $customvalues ) )
		{
			$values = array_merge($customvalues, $values);
		}
		
		//run through the $text and replace any keys with the data from the array
		foreach( $values as $key => $txt )
		{
			$body = preg_replace('#\{{' . $key . '}}#s', $txt, $body);
		}
		
		return $body;
	}

	/**
	 * Will send an email based on the /app/views/emails/$template file, and send it to $to, with the subject $subject.
	 * 
	 * @param  string $to           The email address to send the email to.
	 * @param  string $subject      The email subject
	 * @param  string $template     the name of the template file (without extension, eg: newpassword)
	 * @param  array  $customvalues Any additional custom keys/values used in the specific template (see method: prepareEmail)
	 * @return array                with "status" set to true/false and with a message about the success or failure.
	 */
	protected function sendEmail($to, $subject, $template, array $customvalues = array() )
	{
		$template = ROOTPATH . 'app' . DS .'views' . DS . 'emails' . DS . $template . '.pdt';

		if( !is_readable($template) )
		{
			return array(
				'status' => false,
				'message' => 'The requested email template was not found.' 
			);
		}

		// Generate the email body/content
		ob_start();
		include($template);
		$body = ob_get_clean();

		if( empty($body) )
		{
			return array(
				'status' => false,
				'message' => 'The requested email template was empty.' 
			); 
		}

		$body = $this->prepareEmail($body, $customvalues);

		// Include the PHPMailer autoloader
		require_once ROOTPATH . 'libs' . DS . 'phpmailer' . DS . 'PHPMailerAutoload.php';

		//Create a new PHPMailer instance
		$mail = new PHPMailer();
		//Set who the message is to be sent from
		$mail->setFrom(Configure::get('core/emails_from'), Configure::get('core/site_title'));
		//Set an alternative reply-to address
		$mail->addReplyTo(Configure::get('core/emails_from'), Configure::get('core/site_title'));
		//Set who the message is to be sent to
		$mail->addAddress($to);
		//Set the subject line
		$mail->Subject = $subject;
		//Set the email body
		$mail->Body = $body;
		// Set the email to be HTML
		$mail->ishtml();

		if( Configure::get('core/smtp_enabled') )
        {
			//Tell PHPMailer to use SMTP
			$mail->isSMTP();
			//Enable SMTP debugging
			// 0 = off (for production use)
			// 1 = client messages
			// 2 = client and server messages
			$mail->SMTPDebug = 0;
			//Ask for HTML-friendly debug output
			$mail->Debugoutput = 'error_log';
			//Set the hostname of the mail server
			$mail->Host = Configure::get('core/smtp_host');
			//Set the SMTP port number - likely to be 25, 465 or 587
			$mail->Port = Configure::get('core/smtp_port');
			//Whether to use SMTP authentication
			$mail->SMTPAuth = true;
			//Username to use for SMTP authentication
			$mail->Username = Configure::get('core/smtp_user');
			//Password to use for SMTP authentication
			$mail->Password = Configure::get('core/smtp_pass');
        }

        //send the message, check for errors
		if( !$mail->send() )
		{
			error_log($mail->ErrorInfo);
		    return array(
				'status' => false,
				'message' => 'An error occured while trying to send the email.' 
			); 
		}

		return array(
			'status' => true,
			'message' => '' 
		);
	}

	/**
	 * Will check the users session to see if their session is bound to an account and if the account session matches the sessions.
	 */
	protected function checkLoginState()
	{
		if( Session::get('user/id') == null )
		{
			return;
		}

		$stmt = $this->model->connection->prepare('SELECT s.uid, a.username, a.sessid, a.permissions, ( SELECT COUNT(id) FROM ' . Configure::get('database/prefix')  . 'mailbox WHERE recipent = s.uid AND isread="0" ) as newmessages FROM ' . Configure::get('database/prefix')  . 'sessions as s LEFT JOIN ' . Configure::get('database/prefix')  . 'accounts as a ON a.id = s.uid WHERE s.id = :id');
		$stmt->bindValue(':id', session_id(), PDO::PARAM_INT);
		$stmt->execute();
		$sesaccount = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		if( empty($sesaccount['uid']) || empty($sesaccount['sessid']) || $sesaccount['uid'] != Session::get('user/id') || $sesaccount['sessid'] != session_id() )
		{
			return;
		}

		Session::set('user/id', $sesaccount['uid']);
		Session::set('user/username', $sesaccount['username']);
		Session::set('mailbox/new', $sesaccount['newmessages']);

		$this->permissions = @json_decode($sesaccount['permissions'], true);
		$this->loggedin = true;
	}

	/**
	 * Will login the current user to the account with id: $id.
	 * IMPORTANT NOTE: Be careful! Do not trust the user, always run checks before logging in the user (see the "users_login" controller, method "action_login").
	 * 
	 * @param  integer $id The id of the account the user will be logged in to.
	 * @return boolean     true/false depending on whether the login was a success or not.
	 */
	protected function forceLogin( $id )
	{
		$stmt = $this->model->connection->prepare('SELECT
														id,
														username,
														avatar
													FROM
														' . Configure::get('database/prefix') . 'accounts
													WHERE
														id = :id');
		$stmt->bindValue(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		if( !empty($data) )
		{
			Session::set('user/id', $data['id']);
			Session::set('user/username', $data['username']);
			Session::set('user/avatar', $data['avatar']);

			// Bind the logged in user to a session
			$stmt = $this->model->connection->prepare('UPDATE
															' . Configure::get('database/prefix') . 'accounts
														SET
															sessid = :sid
														WHERE
															id = :uid');
			$stmt->bindValue(':sid', session_id(), PDO::PARAM_INT);
			$stmt->bindValue(':uid', $id, PDO::PARAM_INT);
			$stmt->execute();
			$stmt->closeCursor();

			// Bind the session to the user id
			$stmt = $this->model->connection->prepare('UPDATE
															' . Configure::get('database/prefix') . 'sessions
														SET
															uid = :user
														WHERE
															id = :sid');
			$stmt->bindValue(':sid', session_id(), PDO::PARAM_INT);
			$stmt->bindValue(':user', $id, PDO::PARAM_INT);
			$stmt->execute();
			$stmt->closeCursor();

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Will generate the full url to the given $route.
	 * 
	 * @param  string $route The route to generate the url to.
	 * @return string        The full url to the given route.
	 */
	public function makeUrl( $route = '' )
	{
		if( $route == '#' )
		{
			$url = '#';
		}
		else
		{
			if( Configure::get('views/base_url') != '' )
			{
				$url = Configure::get('views/protocol') . '://' . Configure::get('views/base_url') . '/';
			}
			else
			{
				$url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
				$url = explode('?', $url);
				$url = $url[0];
			}
			
			if( !empty($route) )
			{
				$url .= '?route=' . $route;
			}
		}
		return $url;
	}

	/**
	 * This function will only return part of the users email. Example:
	 * hello.world@youwebsite.com becomes h**********@youwebsite.com
	 * 
	 * @param  string $email The email address to mask. If none if provided and the user is logged in, the logged in users email will be used.
	 * @return string        The masked email address.
	 */
	public function secureEmail( $email = '' )
	{
		if( empty($email) && $this->loggedin )
		{
			$stmt = $this->model->connection->prepare('SELECT email FROM ' . Configure::get('database/prefix') . 'accounts WHERE id = :uid');
			$stmt->bindValue(':uid', Security::sanitize(Session::get('user/id'), 'integer'), PDO::PARAM_INT);
			$stmt->execute();
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
			$stmt->closeCursor();
			$email = Security::sanitize(Security::decryptData($data['email']), 'purestring');
		}

		if( Security::validateEmail($email) )
		{
			$emailparts = explode('@', $email);
			$email = substr($emailparts[0], 0, 1) . str_repeat('*', strlen($emailparts[0])-1) . '@' . $emailparts[1];
		}
		else
		{
			$email = '';
		}

		return $email;
	}

	/**
	 * Checks whether the users permissions has the $permission (bool) set to true or not.
	 * 
	 * @param  [type]  $has [description]
	 * @return boolean      [description]
	 */
	public function hasPermission( $permission )
	{
		if( (bool)$this->getPermission($permission) )
		{
			return true;
		}

		return false;
	}

	/**
	 * Get the value of the current users $permission.
	 * 
	 * @param  string $permission The permission key to look for.
	 * @return mixed        	  Return null if not found.
	 */
	public function getPermission( $permission )
	{
		$path = explode('/', $permission);
		$data = $this->permissions;

		foreach( $path as $p )
		{
			if( !isset($data[ $p ]) )
			{
				$data = null;
				break;
			}

			$data = $data[ $p ];
		}

		return $data;
	}

	/**
	 * Will return a pagination list (HTML) based on the $items, $perpage and $route parameters.
	 * 
	 * @param  integer  $items   The total number of results (used for calculating the number of pages)
	 * @param  integer  $perpage The number of results per page.
	 * @param  string   $route   The route each page should link to: eg if $route = users/list, the url would become something like:
	 *                           http://domain.com/?route=users/list&page=<the page number>
	 * @return HTML          	 The finished pagination list (Bootstrap 3)
	 */
	public function showPagination( $items, $perpage = 10, $route = '' )
	{
		$page = ( Misc::data('page', 'request') == null ? 1 : intval(Misc::data('page', 'request')) );
		$next = ( $page < ceil($items / $perpage) ? $page + 1 : $page );
		$prev = ( $page > 1 ? $page - 1 : $page );

		$pagination = '<ul class="pagination">
						  <li><a href="' . $this->makeUrl( ( empty($route) ? $this->route : $route ) ) . '&page=' . $prev . '">&laquo;</a></li>';

  		for( $i = 1; $i <= ceil( $items / $perpage ); $i++ )
  		{ 
  			$pagination .=  '<li ' . ( $page == $i ? 'class="active"' : '' ) . '><a href="' . $this->makeUrl( ( empty($route) ? $this->route : $route ) ) . '&page=' . $i . '">' . $i . '</a></li>';
  		}

		$pagination .= '<li><a href="' . $this->makeUrl( ( empty($route) ? $this->route : $route ) ) . '&page=' . $next . '">&raquo;</a></li>
					</ul>';

		return $pagination;
	}
	
	/**
	 * Generates the url to the $avatar if it is found, else it will generate the url for the default fallback image.
	 * 
	 * @param  string $avatar The avatar image to generate the url for, eg: myavatar.png
	 * @return string         The full url to the specific avatar
	 */
	public function avatarurl( $avatar = '' )
    {
        if( !empty($avatar) && file_exists( ROOTPATH . DS . 'uploads' . DS . 'avatars' . DS . $avatar ) )
        {
            $avatar = '/uploads/avatars/' . $avatar;
        }

        return $this->makeUrl() . ( !empty($avatar) ? $avatar : 'assets/images/noavatar.gif' );
    }
}