<?php

class PluginExampleController extends AppController
{
	public static $plugin = array(
		'directory' => 'example', // This is the name of the directory.
		'title' => 'Example Plugin', // The title of the plugin. Max 50 characters
		'description' => 'This is just an example plugin to demonstrate the plugin structure and functionality.',
		'author' => 'Zolid Solutions',
		'version' => '0.1.0' // the version of the plugin. Max 16 characters
	);
	
	public function thisPlugin()
	{
		return self::$plugin;
	}

	public static function install( $model )
	{
		// make any nessesary database changes
		return PluginExampleModel::install($model);
	}

	public static function uninstall( $model )
	{
		// make any nessesary database changes
		return PluginExampleModel::uninstall($model);
	}
}