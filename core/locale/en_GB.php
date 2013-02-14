<?php

return array(
    'core' => array(
        'classes' => array(
            'core' => array(
                'config_error'=>'Unable to bind config file to variable. Is the config missing?',
                'mysql_error' => 'Unable to establish a connection to the the site. Please try again later.'
            ),
        	'template' => array(
				'navigation' => array(
					'login' => 'Login',
					'register' => 'Register',
					'forgotpass' => 'Forgot Password',
					'myaccount' => 'My Account',
					'settings' => 'My Settings',
					'dashboard' => 'Dashboard',
					'logout' => 'Logout'
				),
				'notfound' => 'Not found.'
			),
			'user' => array(
				'recover_err1' => 'Please type in the email you used when siging up.',
				'recover_err2' => 'The email you entered does not appear to be valid.',
				'recover_err3' => 'Sorry, no account found using that email.',
				'recover_err4' => 'Sorry, something went wrong in the system. Please try again later.',
				'recover_err5' => 'Sorry, something went wrong when trying to send you the reset email. Please try again later.',
				'recover_success' => 'An e-mail has been sent containing the reset link. Please note that the link is only valid for 24 hours from now.',
				'recover_mail' =>  'Hi there,
									
									A request to reset your {{sitename}} account password has been received on {{now}}, by IP: {{userip}}.
									If you did not request to have your password reset, simply delete this email and the link will be invalidated once the request expires in 24 hours.

									To accept the password reset request, either click the link below, or manually copy and past the link into your browser. The link is only valid until: {{expire}}
									<a href="{{resetUrl}}">{{resetUrl}}</a>

									Kind regards,
									<a href="{{sitelink}}">{{sitename}}</a>',
				
				'recover2_invalid' => 'Invalid reset link',
				'recover2_mail' => 'Hi there,

									Your password has been reset for your {{sitename}} account. You may now login using your email and the follow password: {{newpassword}}

									Kind regards,
									<a href="{{sitelink}}">{{sitename}}</a>',
				'recover2_success' => 'An e-mail containing your new password has been sent to your e-mail.',
				'recover2_error' => 'Sorry, something went wrong when trying to send you the reset email. Please try again later.',

				'login_error1' => 'The CSRF protection system triggered, please try again.',
				'login_error2' => 'both an email and password much be provided',
				'login_error3' => 'The email does not appear to be valid, please try a different one.',
				'login_error4' => 'The email and password combination did not match any records.',

				'register_error1' => 'The CSRF protection system triggered, please try again.',
				'register_error2' => 'Please fill out all the fields.',
				'register_error3' => 'You need to accept the terms and policies before you can register and account.',
				'register_error4' => 'The passwords did not match eachother. Make sure the password is the same in both fields.',
				'register_error5' => 'The email does not appear to be valid, please try a different one.',
				'register_error6' => 'An account is already registered with that email. If you have forgotten your password, try the Password Recovery.',
				'register_error7' => 'An error occured while trying to send your activation link to your email. Please contact us to have your account manually activated.',
				'register_error8' => 'An error occured while trying to register your account. A system admin has been notified. Please try again later.',
				'register_success' => 'Your account has been created. An email containing the activation link has been sent to the email address you specified.',
				'register_mail' =>  'Hi {{username}},

									Thank you very much for signing up at {{sitename}}. Before you can login for the first time, we need to make sure the e-mail is genuine.
									To activate your account, please click the link below or copy and paste it into your browser address and hit Enter.
									
									<a href="{{activateurl}}">{{activateurl}}</a>

									Thanks again, and welcome to {{sitename}}.

									Kind regards,
									<a href="{{sitelink}}">{{sitename}}</a>',
									
				'activate_error1' => 'The activation link appears to be invalid. Please double check the link is correct.',
				'activate_error2' => 'The activation link does no appear to be active any longer. Please check if the account is already active.',
				'activate_success' => 'Thank you, your account has been activated. You may now login.',
				
				'settings_error1' => 'The CSRF protection system triggered, please try again.',
				'settings_error2' => 'Please fill out all the fields. Remember to type in your current password as well.',
				'settings_error3' => 'The email does not appear to be valid, please try a different one.',
				'settings_error4' => 'An account is already registered with that email. If you have forgotten your password, try the Password Recovery.',
				'settings_error5' => 'Your new password must be at least 8 characters long.',
				'settings_error6' => 'The two passwords did not match. Please try again.',
				'settings_error7' => 'The passwords did not match eachother. Make sure the password is the same in both fields.',
				'settings_error8' => 'The password you have entered was not correct.',
				'settings_success' => 'Your account settings and profile has been updated.'
			)
        ),
		'templates' => array(
			'404' => array(
				'notfound' => 'The page you requested was not found!',
				'goback' => 'Back to dashboard'
			),
			'index' => array(
				'login_title' => 'Account Login',
				'login_email' => 'E-mail',
				'login_password' => 'Password',
				'login_remember' => 'Remember login information',
				'login_submit' => 'Log me in!',
				'forgotlogin' => 'Forgot your login details?',
				'herobody' => 'This framework is just a "simple" framework on which you can build your own sites. It comes with a build in simple user management system to handle sign ups, logins and so on. There are several security implementations build in as well, to protect against sql injections, XSS, CSRF and several session security features.',
				'herosignup' => 'Try it out - register here!',
				
				'block1_title' => 'Download',
				'block1_body' => 'The Zolid Framework is an open source project released on Github, under the <a href="http://opensource.org/licenses/mit-license.php">MIT license</a>. The latest available version is: <label class="label label-success">' . ZF_VERSION . '</label>',
				'block1_button' => 'Download Now!',
				
				'block2_title' => 'Documentation',
				'block2_body' => 'The lastst version of the documentation will always be available on Github. Please consult the documentation before asking for help.',
				'block2_button' => 'View Documentation',
				
				'block3_title' => 'About',
				'block3_body' => 'The Zolid Framework is coded by <a href="http://twitter.com/markeliasen">@MarkElisen</a>, using the awesome <a href="http://twitter.github.com/bootstrap/index.html">Twitter Bootstrap</a> for the design.'
			),
			'recover' => array(
				'recover_title' => 'Recover Account',
				'recover_text' => 'Did you forget your account password? No problem at all!<br />Just type the e-mail your used to sign up with in the fields below, and a password reset link will be sent to you.',
				'recover_email' => 'E-mail',
				'recover_submit' => 'Reset Password!'
			),
			'register' => array(
				'register_loggedin' => 'You already have an account.. you are already logged in you know!',
				'register_title' => 'Create new account',
				'register_username' => 'Username',
				'register_email' => 'E-mail',
				'register_password' => 'Password',
				'register_verify' => 'Verify Password',
				'register_tos_part1' => 'I have read and agree to the',
				'register_tos_part2' => 'and the',
				'register_tos' => 'Terms of Service',
				'register_pp' => 'Privacy Policy',
				'register_submit' => 'Crate Free Account'
			),
			'settings' => array(
				'settings_avatar1' => 'Update your avatar at',
				'settings_avatar2' => 'Gravatar.com',
				'settings_username' => 'Username',
				'settings_emaul' => 'Email',
				'settings_password' => 'New Password',
				'settings_verify' => 'Confirm New Password',
				'settings_passhelp' => 'Leave the password fields blank if you do not wish to change your password.',
				'settings_curpass' => 'Current Password',
				'settings_curpasshelp' => 'You need to type in your current password for security reasons whenever you make changes to your account.',
				'settings_submit' => 'Save Settings',
				'settings_mailtitle' => 'Mail Settings',
				'settings_admin' => 'Administation',
				'settings_adminhelp' => 'Enabling this option will add your email address to the administrators mail list and you will receive any updates sent from',
				'settings_members' => 'Members',
				'settings_membershelp' => 'Enabling this option will allow other members to send you emails from your profile. Your e-mail will not be visible to other members.',
				'settings_generaltitle' => 'General Settings',
				'settings_language' => 'Language'
			)
		)
    )
);