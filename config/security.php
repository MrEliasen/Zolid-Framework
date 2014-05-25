<?php

return array(
'csf_enabled' => array(
'value'=>true,
'description'=>'Enable (recommended) or disable CSRF protection.'
),

'min_password_length' => array(
'value'=>6,
'description'=>'The minimum password length required for users.'
),

'hash_cost' => array(
'value'=>13,
'description'=>'WARNING: If you change this value, any old hashes will be invalid! Can range from 4 to 31. Have it as high as possible while keeping response times resonable. 10-15 is normally a good range but depends on your hardware'
),

'encryption_key_iterations' => array(
'value'=>165000,
'description'=>'WARNING: If you change this value you will be unable to decrypt any of you currently encrypted data. Recommended as high as possible while keeping response times resonable. 150000 - 200000 is normally a good range but again all depends on your hardware.'
),

'allowed_html' => array(
'value'=>'a[href|title],b,strong,em,ul,li,ol,pre,code,img[src|title|alt],blockquote,sub,strike,small,br',
'description'=>'This is the list of allowed HTML tags when you sanitize (using "string"). See http://htmlpurifier.org/live/configdoc/plain.html#HTML.Allowed for more info.'
));