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

class ControllersAdminPlugins extends AppController
{
	public function loadPluginList()
	{
		if( !$this->hasPermission('admin') )
		{
			return;
		}

		$installed = $this->model->getInstalledPlugins();
		$plugins = array();

		foreach( scandir(ROOTPATH . 'plugin') as $plugin )
		{
			if( $plugin == '.' || $plugin == '..' )
			{
				continue;
			}

			$controller = 'Plugin' . ucfirst($plugin) . 'Controller';
    		$reflection = new ReflectionClass($controller);

    		if( $reflection->hasProperty('plugin') )
    		{
    			$details = @$reflection->getStaticPropertyValue('plugin');

    			if( empty($details) )
    			{
    				continue;
    			}

				$plugins[ $plugin ] = $details;
				$plugins[ $plugin ]['installed'] = false;
				$plugins[ $plugin ]['active'] = false;

				if( !empty($installed[ $plugin ]) )
				{
					$plugins[ $plugin ]['installed'] = true;
					$plugins[ $plugin ]['active'] = $installed[ $plugin ]['active'];
				}
    		}
		}

		return $plugins;
	}

    protected function action_installplugin()
    {
    	if( !$this->hasPermission('admin') )
		{
			return;
		}
    	
    	if( Misc::data('plugin', 'get') == '' )
    	{
    		return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'Please choose a plugin to install.'
			);
    	}

    	$plugin = Security::sanitize(Misc::data('plugin', 'get'), 'route');
    	if( !is_readable(ROOTPATH . 'plugin' . DS . $plugin . DS . 'controller.php') )
    	{
    		return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'The plugin does not have a Controller file. Unable to install plugin.'
			);
    	}

		// Begin a new transaction, in case we need to rollback any changes
		$this->model->beginTransaction();

    	$plugin_controller = 'Plugin' . ucfirst($plugin) . 'Controller';
    	$reflection = new ReflectionClass($plugin_controller);

    	$success = false;
    	if( $reflection->hasMethod('install') )
    	{
			$install_function = $reflection->getMethod('install');

			// check if the function is static
			if( $install_function->isStatic() )
			{
    			$success = $plugin_controller::install($this->model);
			}
			else
			{
	    		return array(
					'status' => false,
					'message_type' => 'error',
					'message_title' => '',
					'message_body' => 'Install function is not static. Make sure both the Controller and Model install function are static.'
				);
			}
    	}
    	else
    	{
    		// we just expect the plugin does not require "installation" so we basically just add it to the database as "installed"
    		$success = true;
    	}

    	if( $success )
    	{
    		if( $reflection->hasProperty('plugin') )
    		{
    			$plugin_details = @$reflection->getStaticPropertyValue('plugin');
    			$success2 = $this->model->installPlugin($plugin_details, $plugin);
    		}

    		if( !isset($success2) )
    		{
    			$this->model->rollback();
	    		return array(
					'status' => false,
					'message_type' => 'error',
					'message_title' => '',
					'message_body' => 'Unable to install plugin. Cannot retrive plugin details. Make sure the property is static!'
				);
	    	}

	    	if( $success2 )
	    	{
				$this->model->commit();
				$this->redirect = 'admin/plugins';
	    		return array(
					'status' => false,
					'message_type' => 'success',
					'message_title' => '',
					'message_body' => 'The plugin has been successfully installed!'
				);
    		}
    		else
    		{
    			$this->model->rollback();
	    		return array(
					'status' => false,
					'message_type' => 'error',
					'message_title' => '',
					'message_body' => 'Unable to install Plugin. The plugin is already showing as installed.'
				);
    		}
    	}
    	else
    	{
			$this->model->rollback();
    		return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'Unable to install plugin! An error occured while running the plugin installer.'
			);
    	}
    }

    protected function action_uninstallplugin()
    {
    	if( !$this->hasPermission('admin') )
		{
			return;
		}
    	
    	if( Misc::data('plugin', 'get') == '' )
    	{
    		return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'Please choose a plugin to uninstall.'
			);
    	}

    	$plugin = Security::sanitize(Misc::data('plugin', 'get'), 'route');
    	if( !is_readable(ROOTPATH . 'plugin' . DS . $plugin . DS . 'controller.php') )
    	{
    		return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'The plugin does not have a Controller file. Unable to uninstall plugin.'
			);
    	}
    	
		// Begin a new transaction, in case we need to rollback any changes
		$this->model->beginTransaction();

    	$plugin_controller = 'Plugin' . ucfirst($plugin) . 'Controller';
    	$reflection = new ReflectionClass($plugin_controller);

    	$success = false;
    	if( $reflection->hasMethod('uninstall') )
    	{
			$uninstall_function = $reflection->getMethod('uninstall');

			// check if the function is static
			if( $uninstall_function->isStatic() )
			{
    			$success = $plugin_controller::uninstall($this->model);
			}
			else
			{
	    		return array(
					'status' => false,
					'message_type' => 'error',
					'message_title' => '',
					'message_body' => 'Uninstall function is not static. Make sure both the Controller and Model uninstall function are static.'
				);
			}
    	}
    	else
    	{
    		// If the plugin does not have an uninstall function, and it didn't have an install function as well, we just tell the system to remove it from the database.
	    	if( !$reflection->hasMethod('install') )
	    	{
	    		$success = true;
	    	}
    	}

    	if( $success )
    	{
    		$success2 = $this->model->uninstallPlugin($plugin);

    		if( empty($success2) )
    		{
    			$this->model->rollback();
	    		return array(
					'status' => false,
					'message_type' => 'error',
					'message_title' => '',
					'message_body' => 'Unable to uninstall plugin. Cannot retrive plugin details. Make sure the property is static!'
				);
	    	}

	    	if( $success2 )
	    	{
				$this->model->commit();
				$this->redirect = 'admin/plugins';
	    		return array(
					'status' => true,
					'message_type' => 'success',
					'message_title' => 'Succes!',
					'message_body' => 'The plugin has been successfully uninstalled!'
				);
    		}
    		else
    		{
    			$this->model->rollback();
	    		return array(
					'status' => false,
					'message_type' => 'error',
					'message_title' => '',
					'message_body' => 'Unable to uninstall Plugin. The plugin was not found as installed.'
				);
    		}
    	}
    	else
    	{
			$this->model->rollback();
    		return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'Unable to uninstall plugin! An error occured while running the plugin uninstaller.'
			);
    	}
    }

    protected function action_activateplugin()
    {
    	if( !$this->hasPermission('admin') )
		{
			return;
		}
    	
    	if( Misc::data('plugin', 'get') == '' )
    	{
    		return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'Please choose a plugin to activate.'
			);
    	}

    	$plugin = Misc::data('plugin', 'get');
    	$success = $this->model->activtePlugin( Security::sanitize($plugin, 'route'));

    	if( $success )
    	{
			$this->redirect = 'admin/plugins';
    		return array(
				'status' => true,
				'message_type' => 'success',
				'message_title' => 'Succes!',
				'message_body' => 'The plugin has been activated!'
			);
		}
		else
		{
    		return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'Plugin is already active or not installed.'
			);
		}
    }

    protected function action_deactivateplugin()
    {
    	if( !$this->hasPermission('admin') )
		{
			return;
		}
    	
    	if( Misc::data('plugin', 'get') == '' )
    	{
    		return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'Please choose a plugin to deactivate.'
			);
    	}

    	$plugin = Misc::data('plugin', 'get');
    	$success = $this->model->deactivtePlugin( Security::sanitize($plugin, 'route'));

    	if( $success )
    	{
			$this->redirect = 'admin/plugins';
    		return array(
				'status' => true,
				'message_type' => 'success',
				'message_title' => 'Succes!',
				'message_body' => 'The plugin has been deactiaved!'
			);
		}
		else
		{
    		return array(
				'status' => false,
				'message_type' => 'error',
				'message_title' => '',
				'message_body' => 'Plugin is already inactive or not installed.'
			);
		}
    }
}