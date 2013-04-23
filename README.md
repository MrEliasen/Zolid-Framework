##Zolid-Framework - 0.1.1
This framework is just a "simple" framework on which you can build your own sites. It comes with a build in simple user management system to handle registration, logins, logout and so on. There are several security features as well which you can use to help protect your site against SQL injections, XSS and CSRF among other things. The emails stored with this system are AES encrypted and the system uses SHA512 hashes of the emails when making a check against emails (like reset password).

**Remember: There is no "admin panel" or like (yet), so right now you would indeed need to be a php dev to use this framework fully.**

##Demo
[Click Here](http://zolidframe.zolidcore.com) to see a live functioning demo.

##Documentation
A copy of the documentation is available with the download (see documentation folder).
The documentation is work in progress, so it is very limited still.

##Requirements
**User**
* It is highly recommended that you know at least basic PHP to use this framework optimally.

**Server**
* PHP 5.3+
* MySQL 5.x
* Apache mod_rewrite module

##Installation
1. Upload to your webhost
2. Navigate to the folder you uploaded it to via your browser
3. Follow the install instructions

##Thanks
* [Twitter Bootstrap](https://github.com/twitter/bootstrap)
* [zxcvbn](https://github.com/lowe/zxcvbn)
* [HTML Purifier](https://github.com/ezyang/htmlpurifier)
* Cryptastic, by Andrew Johnson

##License
* This project is released under the MIT License - http://opensource.org/licenses/mit-license.php

##Contact
* Twitter: http://twitter.com/markeliasen
