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

class ControllersUsersSignup extends AppController
{
	/**
	 * We extend the preAction method as we need to load the "users" config for this controller to work properly.
	 */
	protected function preAction()
	{
		Configure::load('users');
		parent::preAction();
	}

	/**
	 * Will create the users account if all the requirements are met.
	 * 
	 * @return array A status and message for the success/failure.
	 */
	protected function action_signup()
	{
		if( $this->loggedin )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'You already have an account.'
			);
		}

		if( !Misc::receivedFields('signup_username, signup_email, signup_password', 'post') )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'Please fill out all the required fields.'
			);
		}

		if( !Security::validateEmail(Misc::data('signup_email', 'post')) )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'The email address you entered does not appear to be valid.'
			);
		}

		if( strlen(Misc::data('signup_username')) > Configure::get('users/max_username_length') )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'Your username cannot be longer than ' . Configure::get('users/max_username_length') . ' characters'
			);
		}

		if( Misc::data('signup_username') != Security::sanitize(Misc::data('signup_username'), 'username') )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'Your username may only consist of the following characters: a-z, 0-9 - and _'
			);
		}

		if( strlen(Misc::data('signup_email')) > 100 )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'Your email address exceeds the character limit.'
			);
		}

		if( $this->emailInUse(Misc::data('signup_email')) )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'An account is already signed up with that email address.'
			);
		}

		if( $this->usernameInUse(Security::sanitize(Misc::data('signup_username'), 'username')) )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'The selected username is already in use.'
			);
		}

		if( strlen(Misc::data('signup_password')) < Configure::get('security/min_password_length') )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'Please use a password which consist of at least 6 characters.'
			);
		}

		if( !Security::validateToken('signup') )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'Security Token invalid, please refresh you page.'
			);
		}

		$password = password_hash(
			Misc::data('signup_password'),
			PASSWORD_BCRYPT,
			array('cost' => Configure::get('security/hash_cost') )
		);

		$activcode = ( Configure::get('users/email_confirmation') ? Security::randomGenerator(35, true) : '' );

		// Begin the transaction in case we need to roll back the signup if any part of the process fails.
		$this->model->beginTransaction();
		$success = $this->model->signup(array(
			'username' => Security::sanitize(Misc::data('signup_username'), 'username'),
			'email' => Security::encryptData(Misc::data('signup_email')),
			'email_hashed' => Security::hash(Misc::data('signup_email')),
			'password' => $password,
			'permissions' => @json_encode(Configure::get('users/permissions')),
			'activate_code' => $activcode
		));

		if( $success )
		{
			/**
			 *  If the "signup_auto_login" flag is set, we automatically login the user as soon as the account is created, and set the redirect flag to the *
			 *  route we want to redirect them to.
			 */
			if( Configure::get('users/signup_auto_login') && $this->forceLogin( $this->model->lastInsertId() ) )
			{
				$this->redirect = 'users/dashboard';
			}

			/**
			 *  If the "email_confirmation" flag is set, we have to send the user an email with an activation link they must click before they can login.
			 *  NOTE: If you have the "signup_auto_login" flag set, they will be logged in whether their account is active or not.
			 */
			if( Configure::get('users/email_confirmation') )
			{
				$values = array(
					'activate_url' => $this->makeUrl('users/login') . '&action=activate&token=' . $activcode . base64_encode($this->model->lastInsertId()),
					'username' => Security::sanitize(Misc::data('signup_username'), 'username'),
					'activate_token' => $activcode
				);

				$mailsend = $this->sendEmail(Misc::data('signup_email'), 'Welcome to ' . Configure::get('core/site_title') . '!', 'activation', $values);
				
				/**
				 *  If the email was sent successfully, we commit the database changes and give the good news to the user.
				 *  If something went wrong, eg. if the email failed, we rollback any changes so the user may try once again.
				 */
				if( $mailsend['status'] )
				{
					// Commit the changes and we are done!
					$this->model->commit();

					return array(
						'status' => true,
						'message_type' => 'success',
						'message_title' => 'Confirmation Required',
						'message_body' => 'An email containing an activation link has been sent to you. Please check your spam in case it ends up in there.'
					);
				}
				else
				{
					// An error occured so instead of having essentially a "dead" account in the database we rollback so the user can try again.
					$this->model->rollback();

					return array(
						'status' => false,
						'message_type' => 'error',
						'message_title' => 'Signup failed',
						'message_body' => 'An error occured while trying to send you your activation link. Please try again later.'
					);
				}
			}
			else
			{
				return array(
					'status' => true,
					'message_type' => 'success',
					'message_title' => 'Account Created',
					'message_body' => 'Your account has been created!'
				);
			}
		}
		else
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'An error occured while trying to create your account. Please try again later.'
			);
		}
	}

	/**
	 * Checks if the given $email is in use by another user already.
	 * 
	 * @param  string $email The email address to check for.
	 * @return boolean       True if the email is in use, false if not.
	 */
	private function emailInUse( $email )
	{
		$result = $this->model->checkEmail(Security::hash($email));
		if( empty($result) )
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Checks if the given $user name is in use by another user already.
	 * 
	 * @param  string $user The username to check for.
	 * @return boolean      True if the username is in use, false if not.
	 */
	private function usernameInUse( $user )
	{
		$result = $this->model->checkUsername($user);
		if( empty($data) )
		{
			return false;
		}
		else
		{
			return true;
		}
	}
}





