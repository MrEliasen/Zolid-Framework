##Zolid-Framework - 0.1.4
This simple framework makes it possible to very quickly get a website, wich require a user system, up and running in practually minutes.
It comes with a build in user management system to handle registration, logins, logout and so on. There are several security features as well which you can use to help protect your site against SQL injections, XSS and CSRF among other things. The emails stored with this system are AES encrypted and you can manage all your users, groups and settings from the admin panel

##New in 0.1.4
* Updated email validation function from REGEX to Filter_var.
* Updated the installer to reflect the new changes.
* Updated the way the framework settings are stored.
* Updated the language files to only have the internals of the system and not the pages.
* Updated Bootstrap to 3.0 RC1.
* Fixed a bug with the installer, it now properly redirect the user to the front page.
* Fixed a bug with the session handlingm, causing session data to not get written to the database.
* Added the ability to reuse CSRF tokens.
* Added user groups and permissions.
* Added an admin control panel for users, groups and system settings.
* Added the ability to toggle friendly urls on and off.
* Optimised the SQL tables.
* Optimised the way you encrypt and decrypt data, you can just use 2 functions now.

##Demo
[Click Here](http://zolidframe.zolidcore.com) to see a "vanialla" installation of the system.
You can either just create an account or login using the dummy account below.
However note that only one user can be online on an account at the same time (part of the framework's security)

**Username:** admin
**Password:** password

##Documentation
A copy of the documentation is available with the download (see documentation folder).
The documentation is work in progress, so it is still very limited.

##Server Requirements
* PHP 5.3+
* 1 MySQL database
When you try to install the system it will run a system check to see if your server is compatible.

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
