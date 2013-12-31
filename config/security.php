<?php
/**
 * All your security settings, hash keys and like.
 */
return array(
	/**
	* Enable (recommended) or disable CSRF protection.
	*/
	'csf_enabled' => true,

	/**
	* The minimum password length required for users.
	*/
	'min_password_length' => 6,

	/**
	* This is version of HTML Purifier. If you upload a new version to the libs just make sure you change this to match the new version
	*/
	'purifier_version' => '4.6.0',

	/**
	* The cost can range from 4 to 31. A suggestion is to have it a high as possible while keeping response times resonable. 10-15 is normally a good range but it all depends on your hardware
	* 
	* WARNING: If you change this value, any old hashes will be invalid!
	*/
	'hash_cost' => 10,
	
	/**
	 * This hash key is used for hasing of the users email address (so we have something to check against in the database).
	 *
	 * WARNING: If you change this value, any old hashes will be invalid!
	 */
	'hash_key' => '',

	/**
	* This is the key used to encrypt/decrypt details throughout the system. This is merely to protect certain details from being leaked if your database is compromised.
	* If you want to keep mission critical data safe, you should store any encryption keys separate from your production server.
	* 
	* WARNING: If you change any of these values, you will be unable to decrypt any of you currently encrypted data.
	*/
	'encryption_key' => '',
	'encryption_salt' => '',
	
	/**
	 * Recommended as high as possible while keeping response times resonable. Somewhere between 150000 - 200000 is normally resonable but again all depends on your hardware.
	 *
	 * WARNING: If you change any of these values, you will be unable to decrypt any of you currently encrypted data.
	 */
	'encryption_key_iterations' => 150000,

	/**
	 * This is the list of allowed HTML tags when you sanitize (using "string").
	 * See http://htmlpurifier.org/live/configdoc/plain.html#HTML.Allowed for more info.
	 */
	'allowed_html' => 'a[href|title],b,strong,em,ul,li,ol,pre,code,img[src|title|alt],blockquote,sub,strike,small,br'
);