<?php

class ControllersUsersInstall extends AppController
{
	protected function preAction()
	{
		Configure::load('users');
		parent::preAction();
	}

	protected function action_createadmin()
	{
		if( !Misc::receivedFields('install_adminuser,install_adminemail,install_adminpass', 'post') )
		{
			return array(
				'status' => false,
				'message_body' => 'Please fill out all the fields.',
				'message_title' => 'Error',
				'message_type' => 'error'
			);
		}

		if( !Security::validateEmail(Misc::data('install_adminemail', 'post')) )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'The email address you entered does not appear to be valid.'
			);
		}

		if( strlen(Misc::data('install_adminuser')) > Configure::get('users/max_username_length') )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'Your username cannot be longer than ' . Configure::get('users/max_username_length') . ' characters'
			);
		}

		if( Misc::data('install_adminuser') != Security::sanitize(Misc::data('install_adminuser'), 'username') )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'Your username may only consist of the following characters: a-z, 0-9 - and _'
			);
		}

		if( !Security::validateToken('createadmin', true) )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'Security Token invalid, please refresh you page and try again.'
			);
		}

		$password = password_hash(Misc::data('install_adminpass'), PASSWORD_BCRYPT, array('cost' => Configure::get('security/hash_cost') ));
		
		// Make the account an admin account
		$permissions = Configure::get('users/permissions');
		$permissions['admin'] = true;

		$stmt = $this->model->connection->prepare('INSERT INTO ' . Configure::get('database/prefix') . 'accounts SET username = :user, email = :email, email_hash = :emailhash, password = :passwd, permissions = :perms, created = :time');
		$stmt->bindValue(':user', Security::sanitize(Misc::data('install_adminuser'), 'username'), PDO::PARAM_STR);
		$stmt->bindValue(':email', Security::encryptData(Misc::data('install_adminemail')), PDO::PARAM_STR);
		$stmt->bindValue(':emailhash', Security::hash(Misc::data('install_adminemail')), PDO::PARAM_STR);
		$stmt->bindValue(':passwd', $password, PDO::PARAM_STR);
		$stmt->bindValue(':perms', @json_encode($permissions), PDO::PARAM_STR);
		$stmt->bindValue(':time', time(), PDO::PARAM_INT);
		$stmt->execute();
		$stmt->closeCursor();

		if( $stmt->rowCount() > 0 )
		{
			Notifications::set('The system has been installed.', 'Success', 'success');

			if( $this->forceLogin( $this->model->connection->lastInsertId() ) )
			{
				// Delete the installer files if we can
				//@unlink(ROOTPATH . 'app' . DS . 'views' . DS . 'users' . DS . 'install.pdt');
				//@unlink(ROOTPATH . 'app' . DS . 'controllers' . DS . 'users' . DS . 'install.php');
			}

			return array(
				'status' => true,
				'message_type' => '',
				'message_title' => '',
				'message_body' => '',
				'redirect' => $this->makeUrl('users/home')
			);
		}
		else
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'An error occured while trying to create the account. Make sure the database details are correct.'
			);
		}
	}

	public function action_install()
	{
		if( !Misc::receivedFields('install_sqlhost,install_sqlport,install_sqldbname,install_sqluser,install_sqlpass,install_baseurl,install_urlprotocol,install_sitetitle,install_mailsfrom,install_timezone', 'post') )
		{
			return array(
				'status' => false,
				'message_body' => 'Please fill out all the fields.',
				'message_title' => 'Error',
				'message_type' => 'error'
			);
		}

		if( !Security::validateToken('install', true) )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'Security token was invalid, please try again.'
			);
		}

		try {
			$connection = new PDO(
				'mysql:host=' . Misc::data('install_sqlhost', 'post') . ';port=' . Misc::data('install_sqlport', 'post') . ';dbname=' . Misc::data('install_sqldbname', 'post') . ';charset=utf8',
	            Misc::data('install_sqluser', 'post'),
	            Misc::data('install_sqlpass', 'post'),
	            array(
	                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES "utf8"',
	                PDO::ATTR_EMULATE_PREPARES => false
	            )
            );
		}
		catch (PDOException $e)
		{
			return array(
				'status' => false,
				'message_body' => $e->getMessage(),
				'message_title' => 'Error',
				'message_type' => 'error'
			);
		}

		$prefix = trim(Security::sanitize(Misc::data('install_sqlprefix', 'post'), 'purestring'));

		$connection->exec('CREATE TABLE IF NOT EXISTS `' . $prefix . 'accounts` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `username` varchar(25) NOT NULL,
		  `email` blob NOT NULL,
		  `email_hash` char(128) NOT NULL,
		  `password` varchar(80) NOT NULL,
		  `avatar` varchar(20) NOT NULL,
		  `created` int(10) unsigned NOT NULL,
		  `active` varchar(35) NOT NULL,
		  `permissions` text NOT NULL,
		  `resettoken` varchar(255) NOT NULL,
		  `resetexpire` int(10) NOT NULL,
		  `acc_key` varchar(12) NOT NULL,
		  `sessid` varchar(64) NOT NULL,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `username` (`username`),
		  UNIQUE KEY `email_hash` (`email_hash`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

		$connection->exec('CREATE TABLE IF NOT EXISTS `' . $prefix . 'plugins` (
		  `dir` varchar(50) NOT NULL,
		  `install_date` int(10) NOT NULL,
		  `active` enum(\'0\',\'1\') NOT NULL DEFAULT \'0\',
		  `version` varchar(16) NOT NULL,
		  UNIQUE KEY `dir` (`dir`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;');

		$connection->exec('CREATE TABLE IF NOT EXISTS `' . $prefix . 'mailbox` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `recipent` int(10) unsigned NOT NULL,
		  `sender` int(10) unsigned NOT NULL,
		  `message` text NOT NULL,
		  `date` int(10) unsigned NOT NULL,
		  `isread` enum(\'0\',\'1\') NOT NULL DEFAULT \'0\',
		  PRIMARY KEY (`id`),
  		  KEY `recipent` (`recipent`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

		$connection->exec('CREATE TABLE IF NOT EXISTS `' . $prefix . 'sessions` (
		  `id` varchar(32) NOT NULL,
		  `data` text NOT NULL,
		  `expire` int(10) unsigned NOT NULL,
		  `agent` char(40) NOT NULL,
		  `host` char(40) NOT NULL,
		  `ip` char(40) NOT NULL,
		  `lang` char(40) NOT NULL,
		  `uid` int(10) unsigned NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;');

		$connection->exec('ALTER TABLE `' . $prefix . 'mailbox` ADD CONSTRAINT `' . $prefix . 'mailbox_ibfk_1` FOREIGN KEY (`recipent`) REFERENCES `' . $prefix . 'accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;');

		/**
		 * Database config file generation
		 */
		
		$this->saveConfig(
			'database',
			array(
				'port' => ( Misc::data('install_sqlport', 'post') == '' ? 3306 : Misc::data('install_sqlport', 'post') ),
				'host' => ( Misc::data('install_sqlhost', 'post') == '' ? 'localhost' : Misc::data('install_sqlhost', 'post') ),
				'dbname' => Misc::data('install_sqldbname', 'post'),
				'user' => Misc::data('install_sqluser', 'post'),
				'pass' => Misc::data('install_sqlpass', 'post'),
				'prefix' => Misc::data('install_sqlprefix', 'post')
			)
		);

		/**
		 * Security config file generation
		 */
		$this->saveConfig(
			'security',
			array(
				'encryption_key' => Security::randomGenerator(128),
				'encryption_salt' => Security::randomGenerator(128),
				'hash_key' => Security::randomGenerator(128)
			)
		);

		/**
		 * Views config file generation
		 */
		$baseurl = str_replace( Misc::data('install_urlprotocol', 'post') . '://', '', Misc::data('install_baseurl', 'post') );
		if( substr($baseurl, -1) == '/' )
		{
			$baseurl = substr($baseurl, 0 -1);
		}

		$this->saveConfig(
			'views',
			array(
				'protocol' => Misc::data('install_urlprotocol', 'post'),
				'base_url' => $baseurl
			)
		);

		/**
		 * Core config file generation
		 */
		$this->saveConfig(
			'core',
			array(
				'timezone' => Misc::data('install_timezone', 'post'),
				'site_title' => Misc::data('install_sitetitle', 'post'),
				'allowed_ips' => $_SERVER['REMOTE_ADDR'],
				'emails_from' => Misc::data('install_mailsfrom', 'post'),
				'smtp_enabled' => ( Misc::data('install_smtpuser', 'post') != '' ? 'true' : 'false' ),
				'smtp_host' => Misc::data('install_smtphost', 'post'),
				'smtp_port' => Misc::data('install_smtpport', 'post'),
				'smtp_user' => Misc::data('install_smtpuser', 'post'),
				'smtp_pass' => Misc::data('install_smtppass', 'post')
			)
		);

		return array(
			'status' => true,
			'message_body' => '',
			'message_title' => '',
			'message_type' => '',
			'redirect' => $this->makeUrl('users/install&step=3')
		);
	}

	private function saveConfig( $filename, array $settings )
	{
		$path = ROOTPATH . DS . 'config' . DS . $filename . '.php';
		$confdata = Configure::get($filename, true);
		$config = "<?php\n\nreturn array(\n";
		$c = 0;

		foreach( $confdata as $setting => $value )
		{
			$setting_value = ( !isset($settings[ $setting ]) ? $value['value'] : $settings[ $setting ] );

			if( is_bool($setting_value) )
			{
				$new = ( $setting_value ? 'true' : 'false' );
			}
			else
			{
				if( is_integer($setting_value) )
				{
					$new = $setting_value;
				}
				else
				{
					$new = '\'' . $setting_value . '\'';
				}
			}

			$config .= ( !$c ? '' : ",\n\n" ) . "'" . $setting . "' => array(\n'value'=>" . $new . ",\n'description'=>'" . ( !empty($value['description']) ? $value['description'] : '' ) . "'\n)";
			$c++;
		}

		$config .= ');';

		$f = fopen($path, 'w');
		fwrite($f, $config);
		fclose($f);
	}
}