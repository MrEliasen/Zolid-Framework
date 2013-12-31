<?php
/*
 * The permissions are the default permissions given to a user when they sign up.
 * You are add as many as you wish. The permissions are converted to JSON and stored with each user in the database.
 */

return array(
	/**
	* the max length of a users username (remember to make sure you accounts table allowes you specified range)
	*/
	'max_username_length' => 25,

	/**
	* Whether the user must click an activation link sent to their email when they sign up or not.
	*/
	'email_confirmation' => true,

	/**
	* Whether to login users automatically once they sign up.
	*/
	'signup_auto_login' => false,

	/**
	* This is how long password reset links (the user receive by email) are valid for. (Default 24 hrs)
	*/
	'reset_timeout' => 86400,

	/**
	* The permissions are the default permissions given to a user when they sign up.
 	* You are add as many as you wish. The permissions are converted to JSON and stored with each user in the database.
	*/
	'permissions' => array(
		'admin' => false
	),

	/**
	* The maximum size (in KB) any uploaded avatar must be.
	*/
	'avatar_max_size' => 250, //kb

	/**
	* Allowed avatar image formats. ( Check any)
	*/
	'avatar_formats' => 'jpeg,jpg,png,gif',

	/**
	* This is the size the avatar is scaled to. it will scale the biggest size so it keeps the aspect ratio.
	*/
	'avatar_width' => 250,
	'avatar_height' => 250
);