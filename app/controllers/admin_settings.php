<?php
/**
 *  Zolid Framework
 *  https://github.com/MrEliasen/Zolid-Framework
 *  
 *  @author 	Mark Eliasen (mark.eliasen@zolidsolutions.com)
 *  @copyright 	Copyright (c) 2013, Mark Eliasen
 *  @version    0.1.6.0
 *  @license 	http://opensource.org/licenses/MIT MIT License
 */

class admin_settings extends AppController
{
	/**
	 * Loads some of the less sensitive configuration files's content.
	 * 
	 * @return array The content of each config file.
	 */
	public function getConfigs()
	{
		if( !$this->hasPermission('admin') )
		{
			return;
		}

		$configs = array();
		foreach( scandir(ROOTPATH . DS . 'config') as $config )
		{
			$ignore = array(
				'.',
				'..',
				'autoloader.php',
				'database.php'
			);

			if( in_array($config, $ignore) || substr($config, -4) !== '.php' )
			{
				continue;
			}

			$conf = include( ROOTPATH . DS . 'config' . DS . $config );

			if( $config == 'security.php' )
			{
				unset($conf['encryption_key']);
				unset($conf['encryption_salt']);
				unset($conf['hash_key']);
			}

			$configs[ str_replace('.php', '', $config) ] = $conf;
		}

		return $configs;
	}

	/**
	 * Saves the changes to the secified config file.
	 * 
	 * @return array A status and message for the success/failure
	 */
	public function action_saveconfig()
	{
		if( !$this->hasPermission('admin') )
		{
			return;
		}

		if( !Misc::receivedFields('settings, settings_file') )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'No settings received.'
			);
		}

		switch ( Misc::data('settings_file', 'post') )
		{
			case 'core':
				$file = 'core.php';
				break;

			case 'security':
				$file = 'security.php';
				break;

			case 'users':
				$file = 'users.php';
				break;

			case 'views':
				$file = 'views.php';
				break;
			
			default:
				return array(
					'status' => false,
					'message_type' => 'error',
					'message_title' => '',
					'message_body' => 'Invalid config file.'
				);
				break;
		}

		if( !is_writable(ROOTPATH . DS . 'config' . DS . $file) )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'The config file(s) are not writeable.'
			);
		}

		$data = file_get_contents(ROOTPATH . DS . 'config' . DS . $file);
		$conf = str_replace('.php', '', $file);
		foreach( Misc::data('settings', 'post') as $setting => $value )
		{
			configure::load($conf);
			
			if( is_bool(Configure::get($conf . '.' . $setting)) )
			{
				$current = ( Configure::get($conf . '.' . $setting) ? 'true' : 'false' );
				$new = ( (bool)$value ? 'true' : 'false' );
			}
			else
			{
				if( is_integer(Configure::get($conf . '.' . $setting)) )
				{
					$current = Configure::get($conf . '.' . $setting);
					$new = $value;
				}
				else
				{
					$current = '\'' . Configure::get($conf . '.' . $setting) . '\'';
					$new = '\'' . $value . '\'';
				}
			}

			$data = str_replace(
				"'" . $setting . "' => " . $current,
				"'" . $setting . "' => " . $new,
				$data
			);
		}

		$f = fopen(ROOTPATH . DS . 'config' . DS . $file, 'w');
		fwrite($f, $data);
		fclose($f);

		return array(
			'status' => true,
			'message_type' => 'success',
			'message_title' => '',
			'message_body' => 'Configuration updated!'
		);
	}
}