<?php

return array(
'session_timeout' => array(
'value'=>518400,
'description'=>'The session timeout for you users. Set it to something high, because users tend to be annoyed when they have to login evey time.'
),

'debug' => array(
'value'=>false,
'description'=>'This will enable debug mode and show additional information and enable errors on site.'
),

'timezone' => array(
'value'=>'Europe/Copenhagen',
'description'=>'See http://php.net/manual/en/timezones.php if you want to change your timezone.'
),

'site_title' => array(
'value'=>'',
'description'=>'This is the title of your site, will be displayed throughout the system.'
),

'maintenance' => array(
'value'=>false,
'description'=>'If maintenance is enabled all actions, pages and like will redirect to the maintenance/offline page. Remember to set the "allowed_ips" value if you want specific users to be allowed past the maintenance page.'
),

'allowed_ips' => array(
'value'=>'',
'description'=>'a comma dilimited list of IP addresses for anyone who should continue to use the site even after the maintenance flag is set. Eg: 123.123.123.123, 321.321.321.321, 213.213.213.213'
),

'emails_from' => array(
'value'=>'',
'description'=>'All emails sent out from the system will be shown as from the this email. Remember to match it with your SMTP settings if set.'
),

'smtp_enabled' => array(
'value'=>false,
'description'=>'If you want to use SMTP to send emails (recommended), then set this flag and fill out the rest of the below SMPT details.'
),

'smtp_host' => array(
'value'=>'',
'description'=>'The mail servers IP or hostname.'
),

'smtp_port' => array(
'value'=>'',
'description'=>'The SMTP port for connecting to the mailserver.'
),

'smtp_user' => array(
'value'=>'',
'description'=>'The email/user for the email account.'
),

'smtp_pass' => array(
'value'=>'',
'description'=>'The password for the email account.'
));