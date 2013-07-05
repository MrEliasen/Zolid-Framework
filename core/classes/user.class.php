<?php
/**
 *  Zolid Framework - MIT licensed
 *  https://github.com/MrEliasen/Zolid-Framework
 *
 *  A class which handles all account/user related functions like login and registrations.
 *
 *  @author     Mark Eliasen
 *  @website    www.zolidweb.com
 *  @copyright  (c) 2013 - Mark Eliasen
 *  @version    0.1.2
 */

if( !defined('CORE_PATH') )
{
    die('Direct file access not allowed.');
}

class User extends Core
{
    protected $logged_in = false;

    /**
     * user class construct function. initiates the session check(s) and account action requests
     */
    public function __construct()
    {
		parent::__construct();
		$this->checkSession();

        // Check if a login request was sent to the system
        $cookie = $this->readRememberCookie();
        if( !$this->logged_in && !empty( $cookie ) && empty($_REQUEST['action']) )
        {
			$this->login();
        }

        $this->processAccountRequest();
    }
	
	/**
	 * Handles the account requests (like login, logout, register etc)
	 *
	 * @return mixed depends on the request output.
	 */
	private function processAccountRequest()
	{
		// Check if we have receive the data we need to build the query
		if( empty( $_REQUEST['action'] ) )
		{
			return false;
		}
		
        // Sanitize the user input
        $_REQUEST['action'] = Security::sanitize( $_REQUEST['action'], 'purestring');
		
		// Check which type of query we are building
		switch( $_REQUEST['action'] )
		{
			case 'register':
				$this->register();
				break;

			case 'login':
				$this->login();
				break;

			case 'settings':
				$this->settings();
				break;

			case 'logout':
				$this->logout();
				break;

			case 'activate':
				$this->activateAccount();
				break;

			case 'recover':
				$this->recoverStep1();
				break;

			case 'resetacc':
				$this->recoverStep2();
				break;
		}
	}
	
	/**
	 * account password "recovery", step 1
	 * 
	 * @return boolean ture on success, false on error.
	 */
	private function recoverStep1()
	{
		// Check if we received and email or not. Display error message if not.
		if( empty( $_POST['email'] ) )
		{
			Notifications::setNotification( 'recover_1', $this->lang['core']['classes']['user']['recover_err1'] );
			return false;
		}

		// Sanitize the email address
		$_POST['email'] = Security::sanitize( $_POST['email'], 'purestring' );

		// Check the email if it is valid or not. Display error is not valid.
		if( !$this->validateEmail( $_POST['email'] ) )
		{
			Notifications::setNotification( 'recover_1', $this->lang['core']['classes']['user']['recover_err2'] );
			return false;
		}

		// Create the email hash which we need to check against the database to see if any account is found using that email.
        if( filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) )
        {
            //they try to recover using their email
            $_POST['email'] = hash_hmac('sha512', $this->config['global_salt'] . $_POST['email'], $this->config['global_key'] );
            $query_where = 'email_hash';
        }
        else
        {
            //they try to recover using their username
            $query_where = 'username';
        }

		$stmt = $this->sql->prepare('SELECT email FROM users WHERE ' . $query_where . ' = ?');
		$stmt->bindValue(':email', $_POST['email'], PDO::PARAM_STR);
		$stmt->execute();
		$this->queries++;

		$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		// If no account was found using the email above, display error message.
		if( empty($data) )
		{
			Notifications::setNotification('recover_1', $this->lang['core']['classes']['user']['recover_err3'] );
			return false;
		}

		// Decrypt the email address so we can use it for sending the reset link to.
		$key = Security::pbkdf2($this->config['AES']['level-1'], $this->config['AES']['salt'], 20000, 32);
		$email = Security::decrypt($data[0]['email'], $key);
		unset($key); // Unset the decryption key right after use for security purposes

		// Add the password reset token to the account.
		$token = hash_hmac('sha256', $this->config['global_salt'] . Security::randomGenerator(12) . $email, $this->config['global_key'] );

		$stmt = $this->sql->prepare('UPDATE users SET reset_token = :token, reset_time = :time WHERE email_hash = :email');
		$stmt->bindValue(':token', $token, PDO::PARAM_STR);
		$stmt->bindValue(':time', time() + 86400, PDO::PARAM_INT);
		$stmt->bindValue(':email', $_POST['email'], PDO::PARAM_STR);
		$success = $stmt->execute();
		$this->queries++;
		$stmt->closeCursor();

		// Check if we successfully added the token to the account, because if we don't we shouldn't send them the email, but instead show and error.
		if( !$success || $stmt->rowCount() < 1 )
		{
			Notifications::setNotification('recover_1', $this->lang['core']['classes']['user']['recover_err4'] );
			return false;
		}

		// Create the email we need to send to the user.
		$resetUrl = $this->__get('base_url') . '?action=resetacc&resetacc=' . $token;

		$values = array(
			'now' => date( 'H:i - d/m/Y', time() ),
			'userip' => $_SERVER['REMOTE_ADDR'],
			'resetUrl' => $resetUrl,
			'expire' => date( 'H:i - d/m/Y', time() + 86400 )
		);

		$body = $this->render_email( $this->lang['core']['classes']['user']['recover_mail'], $values );
		$subject = $this->render_email( 'Reset Password - {{sitename}}' );

		if( $this->send_mail( $email, $subject, $body ) )
		{
			Notifications::setNotification('recover_1', $this->lang['core']['classes']['user']['recover_success'], 'Success!', 'success');
			return true;
		}
		else
		{
			Notifications::setNotification('recover_1', $this->lang['core']['classes']['user']['recover_err5'] );
			return false;
		}
	}
	
	/**
	 * account password "recovery" step 2, new password
	 * @return boolean true on success, false on error
	 */
	private function recoverStep2()
	{
		// Check if we received and email or not. Display error message if not.
		if( empty( $_GET['resetacc'] ) )
		{
			Notifications::setNotification( 'recover_1', $this->lang['core']['classes']['user']['invalid'] );
			return false;
		}

		// Sanitize the reset token
		$_GET['resetacc'] = Security::sanitize( $_GET['resetacc'], 'page' );

		// Check the email if it is valid or not. Display error is not valid.
		if( strlen( $_GET['resetacc'] ) != 64)
		{
			Notifications::setNotification( 'recover_1', $this->lang['core']['classes']['user']['invalid'] );
			return false;
		}
		
		// Get the user's email so we can send them their new password.
		$stmt = $this->sql->prepare('SELECT email FROM users WHERE reset_token = :token AND reset_time > :time');
		$stmt->bindValue(':token', $_GET['resetacc'], PDO::PARAM_STR);
		$stmt->bindValue(':time', time(), PDO::PARAM_STR);
		$stmt->execute();
		$this->queries++;
		$userdata = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();
		
		if ( !empty($userdata) )
		{

			// Create the new password and hash for the account
			$newpassword = Security::randomGenerator(12, true);
			$password_hash = hash_hmac('sha512', $this->config['global_salt'] . $newpassword, $this->config['global_key'] );

			// Update the account password to the new one
			$stmt = $this->sql->prepare('UPDATE users SET password = :pass, reset_token = "", reset_time = "" WHERE reset_token = :token AND reset_time > :time');
			$stmt->bindValue(':token', $_GET['resetacc'], PDO::PARAM_STR);
			$stmt->bindValue(':pass', $password_hash, PDO::PARAM_STR);
			$stmt->bindValue(':time', time(), PDO::PARAM_STR);
			$success = $stmt->execute();
			$this->queries++;
			$stmt->closeCursor();

			// If the query failed or if no rows where updated, display error. Keep the errors the same for security.
			if( !$success || $stmt->rowCount() < 1 )
			{
				Notifications::setNotification('recover_1', $this->lang['core']['classes']['user']['invalid'] );
				return false;
			}
		}
		else
		{
			Notifications::setNotification('recover_1', $this->lang['core']['classes']['user']['invalid'] );
			return false;
		}

		// Create the email we need to send to the user¨.
		$values = array(
			'newpassword' => $newpassword
		);

		$body = $this->render_email( $this->lang['core']['classes']['user']['recover2_mail'], $values );
		$subject = $this->render_email( 'Reset Password - {{sitename}}' );
		
		// Decrypt the email address so we can use it for sending the reset link to.
		$key = Security::pbkdf2($this->config['AES']['level-1'], $this->config['AES']['salt'], 20000, 32);
		$email = Security::decrypt($userdata[0]['email'], $key);
		unset($key); // Unset the decryption key right after use for security purposes
		
		if( $this->send_mail( $email, $subject, $body ) )
		{
			Notifications::setNotification( 'recover_1', $this->lang['core']['classes']['user']['recover2_success'], 'Success!', 'success');
			return true;
		}
		else
		{
			Notifications::setNotification( 'recover_1', $this->lang['core']['classes']['user']['recover2_error'] );
			return false;
		}
	}
	
	/**
	 * account activation function.
	 * 
	 * @return boolean true on success, false on error
	 */
	private function activateAccount()
	{
		if( empty($_GET['key']) || strlen($_GET['key']) != 32)
		{
			Notifications::setNotification( 'activate_1', $this->lang['core']['classes']['user']['activate_error1'] );
			return false;
		}
		
		$_GET['key'] = Security::sanitize($_GET['key'], 'page');
		
		$stmt = $this->sql->prepare('UPDATE users SET active_key = "" WHERE active_key = :key');
		$stmt->bindValue(':key', $_GET['key'], PDO::PARAM_STR);
		$stmt->execute();
		$this->queries++;
		$stmt->closeCursor();
		
		if( $stmt->rowCount() == 1)
		{
			Notifications::setNotification( 'activate_1', $this->lang['core']['classes']['user']['activate_success'], null, 'success' );
			return true;
		}
		else
		{
			Notifications::setNotification( 'activate_1', $this->lang['core']['classes']['user']['activate_error2'] );
			return false;
		}
	}

	/**
	 * login function
	 * 
	 * @return boolean true on success, false on error if its a cookie login check, else redirects the user
	 */
    private function login()
    {
    	if( $this->logged_in )
    	{
    		return false;
    	}

        // remember me cookie data - empty if non is found.
        $cookie = $this->readRememberCookie();

        // Check if a login request was sent to the system
        if( empty( $cookie ) && !isset($_REQUEST['login']) )
        {
            return false;
        }

		// Check CSRF token
		if( empty( $cookie ) && !Security::csrfCheck('login') )
		{
			Notifications::setNotification('login_1', $this->lang['core']['classes']['user']['login_error1'], null, 'error');
			return false;
		}

        // Check if the necessary account information is provided, username/password
        if( empty( $cookie ) && ( empty($_REQUEST['email']) || empty($_REQUEST['password'] ) ) )
        {
			Notifications::setNotification('login_1', $this->lang['core']['classes']['user']['login_error2'], null, 'error');
			return false;
        }

		// Cookie login
		if( !empty( $cookie ) )
		{
			$sql_where = 'id = ?';
			$sql_data = array( Security::sanitize( $cookie['id'], 'integer') );
		}
		else
		// form login
		{
            //if the user if trying to login using an email:
            if( filter_var($_REQUEST['email'], FILTER_VALIDATE_EMAIL) )
            {
                $sql_where = 'password = ? AND email_hash = ?';
                $sql_data = array(
                                hash_hmac('sha512', $this->config['global_salt'] . $_REQUEST['password'], $this->config['global_key']),
                                hash_hmac('sha512', $this->config['global_salt'] . strtolower($_REQUEST['email']), $this->config['global_key'] )
                            );
            }
            else
            {
                //else treat it like a username login attempt:
                $sql_where = 'password = ? AND username = ?';
                $sql_data = array(
                                hash_hmac('sha512', $this->config['global_salt'] . $_REQUEST['password'], $this->config['global_key']),
                                Security::sanitize($_REQUEST['email'], 'purestring')
                            );
            }
		}
		
		// Check if the data matches the data we have on the user.
		// Also if it does, we get the data we need to set the session.
		$stmt = $this->sql->prepare('SELECT id, username, email, password, local, acc_key, active_key FROM users WHERE ' . $sql_where . ' LIMIT 1');
		$stmt->execute( $sql_data );
		$this->queries++;
		$userData = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		// check if the login info matched.
		if( $stmt->rowCount() > 0 )
		{
			// Check if the account is activated yet.
			if( !empty($userData[0]['active_key']) )
			{
				Notifications::setNotification('login_1', $this->lang['core']['classes']['user']['login_error5'], null, 'error');
				return false;
			}

			// If it was a "cookie login" check if the data is correct.
			if( !empty( $cookie ) )
			{
				$password = hash_hmac('sha512', 
								$this->config['global_salt'] . 
								$userData[0]['password'] .
								$userData[0]['acc_key'] . 
								$cookie['salt'], 
								$this->config['global_key']
							);

				if($password != $cookie['token'])
				{
					$this->deleteRememberCookie();
					return false;
				}
			}
			
			$key = Security::pbkdf2($this->config['AES']['level-1'], $this->config['AES']['salt'], 20000, 32);
			$decrypted_email = Security::decrypt($userData[0]['email'], $key);
			unset($key);
			
			// it did! set the session
			$_SESSION['local'] = $userData[0]['local'];
			$_SESSION['data']['uid'] = $userData[0]['id'];
			$_SESSION['data']['username'] = $userData[0]['username'];
			$_SESSION['data']['avatar'] = md5( strtolower( $decrypted_email ) ); //We use Gravatar for avatars, so we create and MD5 hash of the email.

			$this->logged_in = true;
            
			// bind the session to the account they logged in with.
			$stmt = $this->sql->prepare('UPDATE users, sessions SET users.session_id = :sesid, sessions.acc = :accid WHERE users.id = :accid2 AND sessions.id = :sesid2');
            $stmt->bindValue(':sesid', session_id(), PDO::PARAM_STR);
            $stmt->bindValue(':sesid2', session_id(), PDO::PARAM_STR);
            $stmt->bindValue(':accid', $userData[0]['id'], PDO::PARAM_INT);
            $stmt->bindValue(':accid2', $userData[0]['id'], PDO::PARAM_INT);
			$stmt->execute();
			$this->queries++;
			$stmt->closeCursor();

			if( !empty($_REQUEST['remember']) )
			{
				$this->generateRememberCookie($userData[0]);
			}
			
			//send them to the members panel
			header('Location: ' . $this->__get('base_url') . '/dashboard' );
			exit;
		}
		else
		{
			if( empty( $cookie ) )
			{
				Notifications::setNotification('login_1', $this->lang['core']['classes']['user']['login_error4'], null, 'error');
			}
			return false;
		}
    }

    /**
     * deletes the user's remember me cookie.
     * 
     * @return boolean true
     */
    private function deleteRememberCookie()
    {
    	setcookie( sha1( $_SERVER['REMOTE_ADDR'] ), '', time()-3600); //1 week
    	return true;
    }

    /**
     * generates the remember me cookie
     * 
     * @param  array $userdata the data we need to create the cookie - see the login function
     * @return boolean           true
     */
    private function generateRememberCookie( $userdata )
    {
    	//generate a random string which will be part of the security.
		$random_string = Security::randomGenerator(64, true);
		$random_string_refined = preg_replace('/[^0-9]/','', $random_string); //then we extract the numbers only, part of security
		
		//the we build the authorisation and security code, from the information we have.
		$auth_code = hash_hmac('sha512', 
								$this->config['global_salt'] . 
								$userdata['password'] .
								$userdata['acc_key'] . 
								$random_string_refined, 
								$this->config['global_key']
					);

		$security_code = hash_hmac('sha512', 
									$this->config['global_salt'] . 
									$_SERVER['REMOTE_ADDR'] . 
									$_SERVER['HTTP_USER_AGENT'] . 
									$_SERVER['HTTP_ACCEPT_LANGUAGE'], 
									$this->config['global_key']
						).$random_string;

		$data = array(
					'id'=>$userdata['id'],
					'check'=>$security_code,
					'token'=>$auth_code
				);
		
		$encoded_data = serialize($data);
		$encoded_data = base64_encode($encoded_data);
		$encoded_data = strrev($encoded_data);

		$encoded_data = $encoded_data . hash_hmac('sha512', $encoded_data, $this->config['global_key']);
		
		//we encode all the data, and add it to the cookie, which we create.
		$cookie_name = sha1($_SERVER['REMOTE_ADDR']);
		setcookie($cookie_name, $encoded_data, time()+604800, null, null, null, true); //1 week
		
		return true;
    }

    /**
     * checks the remember me cookie if it is valid.
     * 
     * @return mised array with the user data if valid, empty string if invalid.
     */
    private function readRememberCookie()
    {
    	if( $this->logged_in || empty( $_COOKIE[ sha1( $_SERVER['REMOTE_ADDR'] ) ] ) )
    	{
			return '';
		}

		try{
			$encoded_data = $_COOKIE[ sha1($_SERVER['REMOTE_ADDR']) ];
			$encoded_data = substr($encoded_data, 0, -64);
			$encoded_data = strrev($encoded_data);
			$encoded_data = base64_decode($encoded_data);
			$data = unserialize($encoded_data);
			
			if( empty($data['id']) || empty($data['check']) || empty($data['token']) )
			{
    			$this->deleteRememberCookie();
				return '';
			}

			$username = Security::sanitize( $data['id'], 'purestring');
			$token = Security::sanitize( $data['token'], 'string');
			$check = Security::sanitize(substr($data['check'], 0, 64), 'string');
			$random_token = preg_replace('/[^0-9]/','', substr($data['check'], 64, 134) );

			// run the checks
			if ($check != hash_hmac('sha512', $this->config['global_salt'] . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'] . $_SERVER['HTTP_ACCEPT_LANGUAGE'], $this->config['global_key'] ) )
			{
    			$this->deleteRememberCookie();
				return '';
			}

			return array('id' => $username, 'token' => $token, 'salt' => $random_token);
		}
		catch(Exeption $e)
		{
    		$this->deleteRememberCookie();
			return '';
		}

    }

    /**
     * logs out the user
     */
    private function logout()
    {
    	// Check if a login request was sent to the system
        if( isset($_REQUEST['logout']) )
        {			
			// Check CSRF token
			if( Security::csrfCheck('logout') )
			{
				unset($_SESSION['data']);
				unset($_SESSION['secure_session']);
				$this->destroy( session_id() );
				$this->deleteRememberCookie();
			}
		}

		// redirect them back to main site
		header('Location: ' . $this->__get('base_url') );
		exit;
    }
	
	/**
	 * account registration
	 * 
	 * @return boolean true on success, false on error
	 */
	private function register()
	{
    	if( $this->logged_in )
    	{
    		return false;
    	}

		// Check if a sign up request was sent to the system
		if( !isset($_REQUEST['signup']) )
		{
			return false;
		}

		// Check CSRF token
		if( Security::csrfCheck('signup') )
		{
			Notifications::setNotification('register_1', $this->lang['core']['classes']['user']['register_error1'], null, 'error');
		}
        
		// Sanitise the username and email values here as we will be using them
		$_REQUEST['email'] = Security::sanitize($_REQUEST['email'], 'email');
		$_REQUEST['username'] = Security::sanitize($_REQUEST['username'], 'purestring');

		// Check if the required information has been passed to the system.
		if( empty($_REQUEST['email']) || empty($_REQUEST['password']) || empty($_REQUEST['password2']) || empty($_REQUEST['username']) )
		{
			Notifications::setNotification('register_1', $this->lang['core']['classes']['user']['register_error2'], null, 'error');
			return false;
		}

		// Check if the user accepted the terms and policies
		if( empty( $_REQUEST['terms'] ) )
		{
			Notifications::setNotification('register_1', $this->lang['core']['classes']['user']['register_error3'], null, 'error');
			return false;
		}

		// Check if the user accepted the terms and policies
		if( $_REQUEST['password'] != $_REQUEST['password2'] )
		{
			Notifications::setNotification('register_1', $this->lang['core']['classes']['user']['register_error4'], null, 'error');
			return false;
		}

		// Check if the email is valid
		if( !$this->validateEmail( $_REQUEST['email'] ) )
		{
			Notifications::setNotification('register_1', $this->lang['core']['classes']['user']['register_error5'], null, 'error');
			return false;
		}

		// Check if the email is already in use
		if( !$this->checkEmail( $_REQUEST['email'] ) )
		{
			Notifications::setNotification('register_1', $this->lang['core']['classes']['user']['register_error6'], null, 'error');
			return false;
		}

		// Check if the email is already in use
		if( !$this->checkUsername( $_REQUEST['username'] ) )
		{
			Notifications::setNotification('register_1', $this->lang['core']['classes']['user']['register_error6'], null, 'error');
			return false;
		}
		
		$key = Security::pbkdf2($this->config['AES']['level-1'], $this->config['AES']['salt'], 20000, 32);
		$encrypt_email = Security::encrypt($_REQUEST['email'], $key);
		unset($key);
		
		$activation_key = md5( Security::randomGenerator(12) . $_REQUEST['email']);
		
		$stmt = $this->sql->prepare('INSERT INTO users (email, email_hash, password, username, local, active_key, acc_key) VALUES ( :email, :emailhash, :pass, :username, :local, :actkey, :acckey)');
		$stmt->bindValue(':pass', hash_hmac('sha512', $this->config['global_salt'] . $_REQUEST['password'], $this->config['global_key'] ), PDO::PARAM_STR);
		$stmt->bindValue(':emailhash', hash_hmac('sha512', $this->config['global_salt'] . strtolower($_REQUEST['email']), $this->config['global_key'] ), PDO::PARAM_STR);
		$stmt->bindValue(':email', $encrypt_email, PDO::PARAM_STR);
		$stmt->bindValue(':username', $_REQUEST['username'], PDO::PARAM_STR);
		$stmt->bindValue(':local', Security::sanitize( $this->config['default_lang'], 'purestring'), PDO::PARAM_STR);
		$stmt->bindValue(':actkey', $activation_key, PDO::PARAM_STR);
		$stmt->bindValue(':acckey', Security::randomGenerator(12), PDO::PARAM_STR);
		$stmt->execute();
		$this->queries++;
		$stmt->closeCursor();

		$err = $stmt->errorInfo();

		if( empty($err[2]) )
		{
			// Create the email we need to send to the user¨.
			$values = array(
				'activateurl' => $this->__get('base_url') . '?action=activate&key=' . $activation_key,
				'username' => $_REQUEST['username']
			);

			$body = $this->render_email( $this->lang['core']['classes']['user']['register_mail'], $values );
			$subject = $this->render_email( 'Activate Account - {{sitename}}' );

			if( $this->send_mail( $_REQUEST['email'], $subject, $body ) )
			{
				Notifications::setNotification( 'register_1', $this->lang['core']['classes']['user']['register_success'], null, 'success' );
			}
			else
			{
				Notifications::setNotification( 'register_1', $this->lang['core']['classes']['user']['register_error7'] );
			}
		}
		else
		{
			Error::log(null, $err[2], 'core.class.php');
			Notifications::setNotification( 'register_1', $this->lang['core']['classes']['user']['register_error8'] );
		}

		return true;
	}

	/**
	 * update of user settings.
	 * 
	 * @return boolean true on success, false on error
	 */
	private function settings()
	{
    	if( !$this->logged_in )
    	{
    		return false;
    	}

		// Check CSRF token
		if( !Security::csrfCheck('settings') )
		{
			Notifications::setNotification('settings_1', $this->lang['core']['classes']['user']['settings_error1'], null, 'error');
			return false;
		}

		// Check if the required information has been passed to the system.
		if( empty($_REQUEST['acc_email']) || empty($_REQUEST['acc_local']) || empty($_REQUEST['acc_pwcurrent']) )
		{
			Notifications::setNotification('settings_1', $this->lang['core']['classes']['user']['settings_error2'], null, 'error');
			return false;
		}

		// Check if the email is valid
		if( !$this->validateEmail( $_REQUEST['acc_email'] ) )
		{
			Notifications::setNotification('settings_1', $this->lang['core']['classes']['user']['settings_error3'], null, 'error');
			return false;
		}

		// Check if the email is already in use
		if( !$this->checkEmail( $_REQUEST['acc_email'] ) )
		{
			Notifications::setNotification('settings_1', $this->lang['core']['classes']['user']['settings_error4'], null, 'error');
			return false;
		}
	
		$key = Security::pbkdf2($this->config['AES']['level-1'], $this->config['AES']['salt'], 20000, 32);
		$encrypt_email = Security::encrypt($_REQUEST['acc_email'], $key);
		unset($key);

		if( !empty( $_REQUEST['acc_pass'] ) )
		{
			// Check if the user accepted the terms and policies
			if( strlen( $_REQUEST['acc_pass'] ) < 8 )
			{
				Notifications::setNotification('settings_1', $this->lang['core']['classes']['user']['settings_error5'], null, 'error');
				return false;
			}

			// Check if the user accepted the terms and policies
			if( empty( $_REQUEST['acc_pass2'] ) )
			{
				Notifications::setNotification('settings_1', $this->lang['core']['classes']['user']['settings_error6'], null, 'error');
				return false;
			}

			// Check if the user accepted the terms and policies
			if( $_REQUEST['acc_pass'] != $_REQUEST['acc_pass2'] )
			{
				Notifications::setNotification('settings_1', $this->lang['core']['classes']['user']['settings_error7'], null, 'error');
				return false;
			}

			$addpasword = true;
		}

		$query = 'UPDATE 
						users 
					SET 
						' . ( isset($addpasword) ? 'password = ?,' : '' ) .'
						' . ( !empty($_REQUEST['mail_admins']) ? 'mail_admins = 1,' : 'mail_admins = 0,' ) .'
						' . ( !empty($_REQUEST['mail_users']) ? 'mail_members = 1,' : 'mail_members = 0,' ) .'
						email = ?, 
						email_hash = ?, 
						local = ?
					WHERE 
						password = ?
					AND
						id = ?';

		if( isset($addpasword) )
		{
			$data[] = hash_hmac('sha512', $this->config['global_salt'] . $_REQUEST['acc_pass'], $this->config['global_key'] );
		}

		$data[] = $encrypt_email;
		$data[] = hash_hmac('sha512', $this->config['global_salt'] . $_REQUEST['acc_email'], $this->config['global_key'] );
		$data[] = Security::sanitize($_REQUEST['acc_local'], 'purestring');
		$data[] = hash_hmac('sha512', $this->config['global_salt'] . $_REQUEST['acc_pwcurrent'], $this->config['global_key'] );
		$data[] = $_SESSION['data']['uid'];
        
		$stmt = $this->sql->prepare($query);
		$stmt->execute($data);
		$this->queries++;
		$stmt->closeCursor();
        
		if( $stmt->rowCount() > 0 )
		{
            $_SESSION['local'] = Security::sanitize($_REQUEST['acc_local'], 'purestring');
			Notifications::setNotification('settings_1', $this->lang['core']['classes']['user']['settings_success'], null, 'success');
			return true;
		}
		else
		{
			Notifications::setNotification('settings_1', $this->lang['core']['classes']['user']['settings_error8']);
			return false;
		}

	}

	/**
	 * get the account profile related data for the user.
	 * 
	 * @param  integer $id if no id is specified, return the data for the current user
	 * @return array      users profile data, the array values are empty if not found.
	 */
	public function getAccProfile($id = 0)
	{
		// If no ID is provided, we must assume we are retriving the data for the same account who is viewing.
		if( empty( $id ) && $this->logged_in )
		{
			$id = $_SESSION['data']['uid'];
		}

		$stmt = $this->sql->prepare('SELECT username, email, local, mail_admins, mail_members FROM users WHERE id = :userid');
		$stmt->bindValue(':userid', $id, PDO::PARAM_INT);
		$stmt->execute();
		$this->queries++;
		
		$profile = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		// If no profile/account was found, we need to generate some placeholder as it will save us checking for empty() many times later.
		if( $stmt->rowCount() < 1)
		{
			$profile = array(
				'username' => '',
				'email' => '',
				'name' => '',
				'local' => '',
				'country' => ''
			);
		}
		else
		{
			// decrypt the email address and because the FetchAll returns the result as an array in [0], bind that to the $profile variable so we can return it correctly.
			$profile = $profile[0];
			$key = Security::pbkdf2($this->config['AES']['level-1'], $this->config['AES']['salt'], 20000, 32);
			$profile['email'] = Security::decrypt( $profile['email'], $key );
			unset($key);
			
		}

		return $profile;
	}

	/**
	 * validates the email, please note that is regx might not validate all email types out there, but it should catch most of them.
	 * 
	 * @param  string $email the email address - please sanitize it before you run it through this
	 * @return boolean        true on valid, false on invalid.
	 */
	protected function validateEmail($email)
	{
		if( preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/', strtolower( $email ) ) )
		{
			return true;
		}

		return false;
	}

	/**
	 * check if an e-mail is already in use by another account.
	 * 
	 * @param  string $email the email address
	 * @return boolean        true if not in use (or if its the same as the current logged in user), and false if it's already in use.
	 */
	protected function checkEmail($email)
	{
		$stmt = $this->sql->prepare('SELECT id FROM users WHERE email_hash = :email');
		$stmt->bindValue(':email', hash_hmac('sha512', $this->config['global_salt'] . $email, $this->config['global_key'] ), PDO::PARAM_STR);
		$stmt->execute();
		$this->queries++;
		$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		if( $stmt->rowCount() < 1 )
		{
			return true;
		}
		else if( $stmt->rowCount() > 0 && $this->logged_in )
		{
			if($data[0]['id'] == $_SESSION['data']['uid'])
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * check if ausername is already in use by another account.
	 * 
	 * @param  string $username the username to check
	 * @return boolean        true if not in use, false if it's already in use.
	 */
	protected function checkUsername($username)
	{
		$stmt = $this->sql->prepare('SELECT id FROM users WHERE username = :usrname');
		$stmt->bindValue(':usrname', $username, PDO::PARAM_STR);
		$stmt->execute();
		$this->queries++;
		$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		if( $stmt->rowCount() < 1 )
		{
			return true;
		}

		return false;
	}

	/**
	 * Checks if the session belongs the to correct user, helps prevent session hijacking etc.
	 */
	private function checkSession()
	{
		// Check if the session is even set
		if( empty($_SESSION['data']['uid']) )
		{
			return $this->logged_in = false;
		}

		// Fetch the session data from the database, to crosscheck with the session
		$stmt = $this->sql->prepare('SELECT 
										s.expire, 
										s.agent, 
										s.ip, 
										s.host, 
										s.acc, 
										u.id,
										u.session_id,
										u.username
									FROM 
										sessions as s 
									INNER JOIN 
										users as u 
										ON u.id = s.acc 
									WHERE 
										s.acc = ? 
										AND 
										s.id = ?
									LIMIT 1');

		$stmt->execute(
					array( 
						empty( $_SESSION['data']['uid'] ) ? 0 : Security::sanitize( $_SESSION['data']['uid'], 'integer' ),
						session_id() 
					)
				);
		$session_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();
		$this->queries++;
		
		if( $stmt->rowCount() > 0 )
		{
			if( $session_data[0]['session_id'] != session_id() )
			{
				$this->deleteRememberCookie();
				unset($_SESSION['data']);
				return false;
			}

			$_SESSION['data']['username'] = $session_data[0]['username'];
			$_SESSION['data']['uid'] = $session_data[0]['id'];
			$this->logged_in = true;
		}
		else
		{
			$this->deleteRememberCookie();
			unset($_SESSION['data']);
		}
	}
	
	/**
	 * renders the email subject or body or like, and populates them with the custom values provided.
	 * 
	 * @param  string $text         html, string or like.
	 * @param  array  $customvalues the values to run through and replace array("findme"=>"replace with me"); the "findme" values must be wrapped in double {{findme}}.
	 * @return string               the rendered text/string
	 */
	protected function render_email( $text, array $customvalues = array() )
	{
		// Global values which can be used in all emails
		$values = array(
			'sitelink' => $this->__get('base_url'),
			'sitename' => $this->__get('site_name')
		);

		// Check if any custom values, like password reset link, has been added. If so, add them to the values array.
		if( !empty( $customvalues ) )
		{
			$values = array_merge($values, $customvalues);
		}
		
		//run through the $text and replace any keys with the data from the array
		foreach( $values as $key => $txt )
		{
			$text = preg_replace('#\{{'.$key.'}}#s', $txt, $text );
		}
		
		return $text;
	}

	/**
	 * sends a email via SMTP (adding PHP mail function soon)
	 * 
	 * @param  string $to      email address to send to
	 * @param  string $subject the email sunject
	 * @param  string $body    the email body 
	 * @return boolean         true on success, false on error
	 */
	protected function send_mail($to, $subject, $body)
	{
        if( !empty($this->config['smtp']['user']) )
        {
            require_once "Mail.php";
            
            $headers = array ('MIME-Version'=> '1.0', 
                              'Content-type' => "text/html; charset=utf8", 
                              'From' => $this->config['smtp']['user'],
                              'Reply-To' => $this->config['smtp']['user'],
                              'To' => $to, 
                              'Subject' => $subject
            );
                              
            $smtp = Mail::factory('smtp',
                                  array ('host' => $this->config['smtp']['host'], 
                                         'port' => $this->config['smtp']['port'], 
                                         'auth' => true, 
                                         'username' => $this->config['smtp']['user'], 
                                         'password' => $this->config['smtp']['pass']
                                  )
            );

            $send = $smtp->send( $to, $headers, nl2br($body) );

            if(PEAR::isError($send))
            {
                Error::log( null, print_r($send->getMessage(), true), __FILE__ );
                return false;
            }
            else
            {
                return true;
            }
        }
        else
        {
            // PHP mail fallback
            $headers = "MIME-Version: 1.0\r\n" .
					   "Content-type: text/html; charset=utf8\r\n" .
					   'From: ' . $this->config['site_email'] . "\r\n" .
					   'To: ' . $to . "\r\n" .
					   'Subject: ' . $subject;
            
			if(!@mail($to, $subject, nl2br($body), $headers)){
                Error::log( null, 'Failed to send e-mail using PHP mail', __FILE__ );
				return false;
			}else{
				return true;
			}
        }
	}
}