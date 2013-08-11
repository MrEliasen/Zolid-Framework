<?php

return array(
    'core' => array(
        'classes' => array(
            'core' => array(
                'config_error'=>'Unable to bind config file to variable. Is the config missing?',
                'mysql_error' => 'Unable to establish a connection to the the site. Please try again later.'
            ),
        	'template' => array(
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
				'login_error5' => 'Your account has not been activated yet. Please check your email\'s spam folder in case the email ended up in there.',

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
        )
    )
);