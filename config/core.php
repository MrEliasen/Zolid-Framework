<?php

return array(
	/**
	 * The session timeout for you users. Set it to something high, because users tend to be annoyed when they have to login evey time.
	 * (because it is not like you are a bank something right?)
	 */
	'session_timeout' => 518400, // 6 months

	/**
	 * Enabling debug mode is not recommended in production. However it will help (if the server allows it) to display errors.
	 */
	'debug' => false,

	/**
	 * See http://php.net/manual/en/timezones.php if you want to change your timezone.
	 */
	'timezone' => 'Europe/Copenhagen',

	/**
	 * This is the title of your site, will be displayed throughout the system.
	 */
	'site_title' => 'Zolid Framework',

	/**
	 * If maintenance is enabled all actions, pages and like will redirect to the maintenance/offline page.
	 * Remember to set the "allowed_ips" value if you want specific users to be allowed past the maintenance page.
	 */
	'maintenance' => false,

	/**
	 * a comma dilimited list of IP addresses for anyone who should continue to use the site even after the maintenance flag is set.
	 * Example: 123.123.123.123, 321.321.321.321, 213.213.213.213
	 */
	'allowed_ips' => '',

	/**
	 * All emails sent out from the system will be shown as from the this email. Remember to match it with your SMTP settings if set.
	 */
	'emails_from' => '',

	/**
	 * If you want to use SMTP to send emails (recommended), then set this flag and fill out the rest of the below SMPT details.
	 */
	'smtp_enabled' => false, // php mail() fallback

	/**
	 * Your SMTP connection details.
	 */
	'smtp_host' => '',
	'smtp_port' => '',
	'smtp_user' => '',
	'smtp_pass' => ''
);