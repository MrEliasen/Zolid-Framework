<?php

return array(
    'core' => array(
        'classes' => array(
            'core' => array(
                'config_error'=>'Unable to bind config file to variable. Is the config missing?',
                'mysql_error' => 'Unable to establish a connection to the the site. Please try again later.'
            ),
        	'template' => array(
				'notfound' => 'Not found.',
				'permission_err' => 'You do not have permission to do this',
				'modal_notfound1'=>'Post not found.',
				'modal_notfound2'=>'Topic not found.',
				'modal_notfound3'=>'Category not found.',
				'modal_notfound4'=>'Topic not found.',
				'modal_notfound5'=>'Post not found.',
				'modal_title1'=>'New Topic Reply',
				'modal_title2'=>'Replying To: ',
				'modal_title3'=>'Starting new topic in: ',
				'modal_title4'=>'Editing topic: ',
				'modal_title5'=>'Editing Post',
				'modal_quoteby'=>'Originally Posted by ',
				'modal_allowedhtml'=>'<b>Allowed HTML:</b>
                                    <ul>
                                        <li><b>&lt;strong>&lt;/strong></b> for bold text</li>
                                        <li><b>&lt;em>&lt;/em></b> to emphasize text</li>
                                        <li><b>&lt;ul>&lt;li></b> or <b>&lt;ol>&lt;li></b> to make lists</li>
                                        <li><b>&lt;h3> or &lt;h4></b> for headlines headings</li>
                                        <li><b>&lt;pre>&lt;/pre></b> for code blocks</li>
                                        <li><b>&lt;code>&lt;/code></b> for few words of code</li>
                                        <li><b>&lt;a>&lt;/a></b> for links</li>
                                        <li><b>&lt;img></b> to add an image</li>
                                        <li><b>&lt;blockquote>&lt;/blockquote></b> to quote some text</li>
                                    </ul>',
				'modal_close'=>'Close',
				'modal_save1'=>'Submit Reply',
				'modal_save2'=>'Submit Reply',
				'modal_save3'=>'Submit New Topic',
				'modal_save4'=>'Save Changes',
				'modal_save5'=>'Save Changes',
				'modal_texthere'=>'Your reply here..',
				'modal_bodyhere'=>'What do you want to write about?',
				'modal_topictitle'=>'Topic Title'
			),
        	'forum' => array(
				'loggedin_err' => 'You need to be logged in to view the forums.',

				'addpost_err1' => 'Thread not found.',
				'addpost_err2' => 'Your reply must consist of at least 10 characters.',
				'addpost_err3' => 'CSRF Security Meassure triggered, please refresh your window (F5 or CMD + R).',
				'addpost_err4' => 'An error occured while trying to reply to the thread, please try again in a few minutes.',
				'addpost_success' => 'Your reply has been added.',

				'addthread_err1' => 'Thread not found.',
				'addthread_err2' => 'Your thread title must be at least 5 characters long.',
				'addthread_err3' => 'Your reply must be at least 10 characters long.',
				'addthread_err4' => 'CSRF Security Meassure triggered, please refresh your window (F5 or CMD + R).',
				'addthread_err5' => 'Only admins are allowed to start new threads in this category. Sorry.',
				'addthread_err6' => 'An error occured while trying to reply to the thread, please try again in a few minutes.',
				'addthread_success' => 'You thread has been created',

				'updatepost_err1' => 'Thread not found.',
				'updatepost_err2' => 'Your reply must consist of at least 10 characters.',
				'updatepost_err3' => 'CSRF Security Meassure triggered, please refresh your window (F5 or CMD + R).',
				'updatepost_err4' => 'An error occured while trying to update the post. Are you sure this is your post?',
				'updatepost_success' => 'Your reply has been added.',

				'updatethread_err1' => 'Thread not found.',
				'updatethread_err2' => 'Your topic title must consist of at least 5 characters.',
				'updatethread_err3' => 'Your topic must consist of at least 10 characters.',
				'updatethread_err4' => 'CSRF Security Meassure triggered, please refresh your window (F5 or CMD + R).',
				'updatethread_err5' => 'An error occured while trying to update the thread. Are you sure this is your thread?',
				'updatethread_success' => 'Your topic title has been updated.',

				'deletepost_err1' => 'Post not found.',
				'deletepost_err1' => 'CSRF Security Meassure triggered, please refresh your window (F5 or CMD + R).',
				'deletepost_err1' => 'An error occured while trying to delete the post. Does it still exist?',
				'deletepost_success' => 'Post was deleted.',

				'deletethread_err1' => 'Thread not found.',
				'deletethread_err2' => 'CSRF Security Meassure triggered, please refresh your window (F5 or CMD + R).',
				'deletethread_err3' => 'An error occured while trying to delete the post. Does it still exist?',
				'deletethread_success' => 'Thread Deleted.'
			),
        	'admin' => array(
				'modal_err1' => 'You do not have permission to do this',
				'modal_usrnotfound' => 'User Not Found',
				'modal_edittitle' => 'Edit Account',
				'modal_username' => 'Username',
				'modal_email' => 'Email',
				'modal_usergroup' => 'User Group',
				'modal_changepass' => 'Change Password',
				'modal_newpass' => 'New Password',
				'modal_help1' => 'Leave the password blank to keep current password.',
				'modal_accstatus' => 'Account Status',
				'modal_statusinactive' => 'INACTIVE',
				'modal_statusactive' => 'ACTIVE',
				'modal_activateacc' => 'Activate Account',
				'modal_check1' => 'Ignore email verification and activate account now?',
				'modal_close' => 'Close',
				'modal_save' => 'Save',
				'modal_err2' => 'Group Not Found',
				'modal_editgrptitle' => 'Edit Group',
				'modal_grptitle' => 'Title',
				'modal_grptitle2' => 'Group Title',
				'modal_grpperms' => 'Permissions (json)',
				'modal_grpadd' => 'Add Group',
				'modal_grpsave' => 'Save Changes',
				'modal_forumaddtitle' => 'Add Forum Category',
				'modal_forumaddctitle' => 'Category Title',
				'modal_forumaddcdesc' => 'This is the category description.',
				'modal_forumaddclabel' => 'Category Description',
				'modal_forumaddsave' => 'Add Category',
				'modal_err3' => 'Category Not Found',
				'modal_forumaddcattitle' => 'Add Forum Category',
				'modal_forumaddcatctitle' => 'Category Title',
				'modal_forumaddcatcdesc' => 'Category Description',
				'modal_forumaddcatcdeschelp' => 'This is the category description.',
				'modal_forumaddcatsave' => 'Edit Category',

				'savegroup_err1' => 'You do not have permission to do this',
				'savegroup_err2' => 'CSRF token was invalid, please reload the form.',
				'savegroup_err3' => 'Group not found.',
				'savegroup_err4' => 'Group title cannot be blank.',
				'savegroup_err5' => 'Unable to save changes, make sure the title is not already in use.',
				'savegroup_success1' => 'Changes saved!',
				'savegroup_success2' => 'Group Added!',
				'savegroup_html1' => '<b>Are you sure?</b>',
				'savegroup_html2' => 'Yes I\'m sure',
				'savegroup_html3' => 'No',

				'saveacc_err1' => 'You do not have permission to do this',
				'saveacc_err2' => 'CSRF token was invalid, please reload the form.',
				'saveacc_err3' => 'User not found.',
				'saveacc_err4' => 'Username cannot be blank.',
				'saveacc_err5' => 'The email is invalid.',
				'saveacc_err6' => 'User group cannot be left blank.',
				'saveacc_err7' => 'Unable to save changes, make sure the username and email is not already in use.',
				'saveacc_success' => 'Changes saved!',

				'delacc_err1' => 'You do not have permission to do this',
				'delacc_err2' => 'CSRF token was invalid, please reload the form.',
				'delacc_err3' => 'User not found.',
				'delacc_err4' => 'You cannot delete your own account.',
				'delacc_err5' => 'Unable to delete account, maybe it has already been removed?',
				'delacc_success' => 'Account was deleted.',

				'delgrp_err1' => 'You do not have permission to do this',
				'delgrp_err2' => 'CSRF token was invalid, please reload the form.',
				'delgrp_err3' => 'Group not found.',
				'delgrp_err4' => 'You cannot delete your own group.',
				'delgrp_err5' => 'Unable to delete group, maybe it has already been removed?',
				'delgrp_success' => 'Group was deleted.',

				'delforumcat_err1' => 'You do not have permission to do this',
				'delforumcat_err2' => 'CSRF token was invalid, please reload the form.',
				'delforumcat_err3' => 'Category not found.',
				'delforumcat_err4' => 'Unable to delete category, maybe it has already been removed?',
				'delforumcat_success' => 'Category and posts successfully deleted.',

				'addforum_err1' => 'You do not have permission to do this',
				'addforum_err2' => 'CSRF token was invalid, please reload the form.',
				'addforum_err3' => 'The forum title cannot be left empty.',
				'addforum_err4' => 'Failed to add category.',
				'addforum_success' => 'The category has been added',
				'addforum_html1' => '<b>All posts in this category will be deleted as well, continue?</b>',
				'addforum_html2' => 'Yes, delete it',
				'addforum_html3' => 'No',

				'editforumcat_err1' => 'You do not have permission to do this',
				'editforumcat_err2' => 'Forum category was not found.',
				'editforumcat_err3' => 'CSRF token was invalid, please reload the form.',
				'editforumcat_err4' => 'The forum title cannot be left empty.',
				'editforumcat_err5' => 'Failed to add category.',
				'editforumcat_success' => 'The category has been updated',

				'savesetting_err1' => 'You do not have permission to do this',
				'savesetting_err2' => 'CSRF token was invalid, please reload the form.',
				'savesetting_err3' => 'No settings where received.',
				'savesetting_err4' => 'An error occured while trying to update the settings.',
				'savesetting_success' => 'System settings has been updated.',

				'saveforumorder_err1' => 'You do not have permission to do this',
				'saveforumorder_err2' => 'CSRF token was invalid, please reload the form.',
				'saveforumorder_err3' => 'No forum structure received.'
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