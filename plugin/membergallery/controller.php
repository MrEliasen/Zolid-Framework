<?php

class PluginMembergalleryController extends AppController
{
	/* All plugins must have this static property. The required details are:
		directory    The name of the plugin directory 
		title        The plugin's title 
		description  The plugin's description
		author       The plugin's author
		version      The verison of the plugin.
	*/
	public static $plugin = array(
		'directory' => 'membergallery', // This is the name of the directory.
		'title' => 'Member Gallery', // The title of the plugin. Max 50 characters
		'description' => 'This plugin will show a gallery-like list of all the members\' avatars.',
		'author' => 'Zolid Solutions',
		'version' => '0.1.0', // the version of the plugin. Max 16 characters
		'css_includes' => array( // the relative path from this controller file the file is. Must within the plugin's directory.
			'assets/membergallery.css'
		),
		'js_includes' => array( // the relative path from this controller file the file is. Must within the plugin's directory.
			'assets/membergallery.js'
		)
	);
}