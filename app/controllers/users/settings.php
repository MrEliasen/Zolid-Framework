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

class ControllersUsersSettings extends AppController
{
	protected function preAction()
	{
		Configure::load('users');
		Configure::load('security');
		parent::preAction();
	}

	/**
	 * Will update the password for the given account if the requirements are met.
	 * 
	 * @return array A status and message for the success/failure.
	 */
	protected function action_updatepassword()
	{
		if( !$this->loggedin )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'You must be logged in to do this.'
			);
		}

		if( !Misc::receivedFields('newpass_password,newpass_current', 'post') )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'Please fill out all the required fields.'
			);
		}

		if( strlen(Misc::data('newpass_password')) < Configure::get('security/min_password_length') )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'Please use a password which consist of at least ' . Configure::get('security/min_password_length') . ' characters.'
			);
		}

		if( !$this->validatePassword(Misc::data('newpass_current', 'post'), Session::get('user/id')) )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'The password was invalid, please try again.'
			);
		}

		$success = $this->model->updatePassword(array(
			'userid' => Session::get('user/id'),
			'password' => password_hash(Misc::data('newpass_password'), PASSWORD_BCRYPT, array('cost' => Configure::get('security/hash_cost')))
		));

		if( $success )
		{
			return array(
				'status' => true,
				'message_type' => 'success',
				'message_title' => 'Password Updated',
				'message_body' => 'Your account password has been updated successfully!'
			);
		}
		else
		{
			if( empty($this->model->lastError[2]) )
			{
				return array(
					'status' => false,
					'message_type' => 'warning',
					'message_title' => '',
					'message_body' => 'No change was required. The entered password is already the current password.'
				);
			}
			else
			{
				return array(
					'status' => false,
					'message_type' => 'error',
					'message_title' => '',
					'message_body' => 'An error occured while trying to update your password.'
				);
			}
		}
	}

	/**
	 * Will update the accounts email address if the requirements are met.
	 * 
	 * @return array A status and message for the success/failure
	 */
	protected function action_updateemail()
	{
		if( !$this->loggedin )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'You must be logged in to do this.'
			);
		}

		if( !Misc::receivedFields('newemail_password,newemail_email', 'post') )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'Please fill out all the required fields.'
			);
		}

		if( !Security::validateEmail(Misc::data('newemail_email', 'post')) )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'The email address you entered does not appear to be valid.'
			);
		}

		$hash = Security::hash(Misc::data('newemail_email'));
		if( $this->emailInUse($hash) )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'The email you entered is already in use by a different account.'
			);
		}

		if( !$this->validatePassword(Misc::data('newemail_password', 'post'), Session::get('user/id')) )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'The password was incorrect, please try again.'
			);
		}

		$success = $this->model->updateEmail(array(
			'userid' => Session::get('user/id'),
			'email' => Security::encryptData(Misc::data('newemail_email')),
			'hash' => Security::hash($hash)
		));

		if( $success )
		{
			return array(
				'status' => true,
				'message_type' => 'success',
				'message_title' => 'Email Updated Updated',
				'message_body' => 'Your account email has been updated successfully!'
			);
		}
		else
		{
			if( empty($this->model->lastError[2]))
			{
				return array(
					'status' => false,
					'message_type' => 'warning',
					'message_title' => '',
					'message_body' => 'No change was required. This is already your current account email.'
				);
			}
			else
			{
				return array(
					'status' => false,
					'message_type' => 'error',
					'message_title' => '',
					'message_body' => 'An error occured while trying to update your email.'
				);
			}
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
		$result = $this->model->checkEmail($email);
		if( empty($result) )
		{
			return false;
		}
		
		return true;
	}

	/**
	 * Validate if the $password the user entered is the correct password for the account with id $userid.
	 *  
	 * @param  string $password  Non-hashes/processed password the user submitted.
	 * @param  integer $userid   The id of the account which password to check against. 
	 * @return boolean           True if the password is correct, false if not.
	 */
	private function validatePassword( $password, $userid )
	{
		$data = $this->model->getPassword($userid);
		if( empty($data) || !password_verify($password, $data['password']) )
		{
			return false;
		}

		return true;
	}

	/**
	 * Will delete the users current avatar and upload the new one if the requirements are met.
	 * 
	 * @return array A status and message for the success/failure.
	 */
    protected function action_updateavatar()
    {
    	if( !$this->loggedin )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => 'Error',
				'message_body' => 'You must be logged in to update you avatar.'
			);
		}

        if( !empty($_FILES['newavatar_image']['tmp_name']) )
        {
            $newName = uniqid();
            $ext = explode('.', $_FILES['newavatar_image']['name']);
                $ext = end( $ext );
                    $ext = strtolower( $ext );

            $avatarDir = ROOTPATH . DS . 'uploads' . DS . 'avatars' . DS;

            if( !is_dir($avatarDir) )
            {
            	@mkdir($avatarDir, 0755, true);
            }

            // Check the file-size does not exceed the specified size
            if( $_FILES['newavatar_image']['size'] > Configure::get('users/avatar_max_size') * 1024 )
            {
                return array(
					'status' => false,
					'message_type' => 'error',
					'message_title' => 'Error',
					'message_body' => 'An error occured while trying to update your password, please try again later.'
				);
            }

            $formats = array();
            $formatsMime = array();
            foreach( explode(',', Configure::get('users/avatar_formats')) as $format )
            {
            	$format = strtolower(str_replace(' ', '', $format));
            	$formats[] = $format;
            	$formatsMime[] = 'image/' . $format;
            }

            // Get file information
            $imginfo = getimagesize($_FILES['newavatar_image']['tmp_name']);

            if( !in_array( $ext, $formats ) || !in_array( $imginfo['mime'], $formatsMime ) )
            {
                // delete temp. file
                unlink($_FILES['newavatar_image']['tmp_name']);
                return array(
					'status' => false,
					'message_type' => 'error',
					'message_title' => 'Error',
					'message_body' => 'Image format not supported. You may only upload: ' . str_replace(',', ', ', Configure::get('users/avatar_formats'))
				);
            }

            if( extension_loaded('imagick'))
            {
	            // Rebuild the image with imagick to minimize any issues with "evil" code.
	            $img = new Imagick( $_FILES['newavatar_image']['tmp_name'] );

	            // create thumbnail is too big
	            if( $imginfo[0] > Configure::get('users/avatar_width') || $imginfo[1] > Configure::get('users/avatar_height') )
	            {
	                if( $imginfo[0] > $imginfo[1] )
	                {
	                    $thumb_width = Configure::get('users/avatar_width');
	                    $thumb_height = 0;
	                }
	                else
	                {
	                    $thumb_width = 0;
	                    $thumb_height = Configure::get('users/avatar_height');
	                }
	                $img->scaleImage($thumb_width, $thumb_height);
	            }

	            $img->writeImage($avatarDir . $newName . '.' . $ext);
	            $img->destroy();
	        }
	        else
	        {
			    switch ($imginfo[2]) {
			        case IMAGETYPE_GIF:
			            $source = imagecreatefromgif($_FILES['newavatar_image']['tmp_name']);
			            break;

			        case IMAGETYPE_JPEG:
			            $source = imagecreatefromjpeg($_FILES['newavatar_image']['tmp_name']);
			            break;

			        case IMAGETYPE_PNG:
			            $source = imagecreatefrompng($_FILES['newavatar_image']['tmp_name']);
			            break;
			    }

			    $aspect = $imginfo[0] / $imginfo[1];
			    $thumbnail_aspect = Configure::get('users/avatar_width') / Configure::get('users/avatar_height');

			    if( $imginfo[0] <= Configure::get('users/avatar_width') && $imginfo[1] <= Configure::get('users/avatar_height') )
			    {
			        $thumbnail_width = $imginfo[0];
			        $thumbnail_height = $imginfo[1];
			    }
			    elseif ($thumbnail_aspect > $aspect)
			    {
			        $thumbnail_width = Configure::get('users/avatar_height') * $aspect;
			        $thumbnail_height = Configure::get('users/avatar_height');
			    }
			    else
			    {
			        $thumbnail_width = Configure::get('users/avatar_width');
			        $thumbnail_height = Configure::get('users/avatar_width') / $aspect;
			    }

			    $thumbnail = imagecreatetruecolor($thumbnail_width, $thumbnail_height);
			    imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $thumbnail_width, $thumbnail_height, $imginfo[0], $imginfo[1]);
			    
			    switch ($imginfo[2]) {
			        case IMAGETYPE_GIF:
			            imagegif($thumbnail, $avatarDir . $newName . '.' . $ext);
			            break;

			        case IMAGETYPE_JPEG:
			            imagejpeg($thumbnail, $avatarDir . $newName . '.' . $ext, 85);
			            break;

			        case IMAGETYPE_PNG:
			            imagepng($thumbnail, $avatarDir . $newName . '.' . $ext);
			            break;
			    }

			    imagedestroy($source);
			    imagedestroy($thumbnail);
	        }

            if( file_exists($avatarDir . $newName . '.' . $ext) )
            {
            	$this->model->updateAvatar(array(
            		'userid' => Security::sanitize(Session::get('user/id'), 'integer'),
            		'file' => $newName . '.' . $ext,
            		'avatarDir' => $avatarDir

            	));
                Session::set('user/avatar', $newName . '.' . $ext);

                return array(
					'status' => true,
					'message_type' => 'success',
					'message_title' => 'Avatar Updated',
					'message_body' => 'Your avatar has been updated.'
				);
            }
            else
            {
                return array(
					'status' => false,
					'message_type' => 'error',
					'message_title' => 'Error',
					'message_body' => 'An error occured while trying to update your avatar.'
				);
            }
        }
        else
        {
        	return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'You have not selected any image to upload.'
			);
        }
    }
}