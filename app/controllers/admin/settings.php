<?php
/**
 *  Zolid Framework
 *  https://github.com/MrEliasen/Zolid-Framework
 *  
 *  @author 	Mark Eliasen (mark.eliasen@zolidsolutions.com)
 *  @copyright 	Copyright (c) 2014, Mark Eliasen
 *  @version    0.1.6.1
 *  @license 	http://opensource.org/licenses/MIT MIT License
 */

class ControllersAdminSettings extends AppController
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
				'database.php',
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

		$file = Security::sanitize(Misc::data('settings_file', 'post') . '.php', 'purestring');
		if( !is_writable(ROOTPATH . DS . 'config' . DS . $file) )
		{
			return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'The config file "' . $file . '" is not writeable.'
			);
		}


		$config = "<?php\n\nreturn array(\n";
		$conffile = str_replace('.php', '', $file);
		$c = 0;
		foreach( Misc::data('settings', 'post') as $setting => $value )
		{
			configure::load($conffile);
			$cur = Configure::get($conffile . '/' . $setting, true);

			if( is_bool($cur['value']) )
			{
				$new = ( $value == 'true' ? 'true' : 'false' );
			}
			else
			{
				if( is_integer($cur['value']) )
				{
					$new = $value;
				}
				else
				{
					$new = '\'' . $value . '\'';
				}
			}

			$config .= ( !$c ? '' : ",\n\n" ) . "'" . $setting . "' => array(\n'value'=>" . $new . ",\n'description'=>'" . ( !empty($cur['description']) ? $cur['description'] : '' ) . "'\n)";
			$c++;
		}

		$config .= ');';

		$f = fopen(ROOTPATH . DS . 'config' . DS . $file, 'w');
		fwrite($f, $config);
		fclose($f);

		return array(
			'status' => true,
			'message_type' => 'success',
			'message_title' => '',
			'message_body' => 'Configuration updated!'
		);
	}
}