<?php

return array(
'max_username_length' => array(
'value'=>25,
'description'=>'the max length of a users username (remember to make sure you accounts table allowes you specified range)'
),

'email_confirmation' => array(
'value'=>true,
'description'=>'Whether the user must click an activation link sent to their email when they sign up or not.'
),

'signup_auto_login' => array(
'value'=>false,
'description'=>'Whether to login users automatically once they sign up.'
),

'reset_timeout' => array(
'value'=>86400,
'description'=>'This is how long password reset links (the user receive by email) are valid for. (Default 24 hrs)'
),

'avatar_max_size' => array(
'value'=>256,
'description'=>'The maximum size (in KB) any uploaded avatar must be.'
),

'avatar_formats' => array(
'value'=>'jpeg,jpg,png,gif',
'description'=>'Allowed avatar image formats.'
),

'avatar_width' => array(
'value'=>250,
'description'=>'This is the width the avatar is scaled to. it will scale the biggest size (height or width) so it keeps the aspect ratio.'
),

'avatar_height' => array(
'value'=>250,
'description'=>'This is the height the avatar is scaled to. it will scale the biggest size (height or width) so it keeps the aspect ratio.'
));