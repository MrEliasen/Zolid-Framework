<?php

class PluginExampleModel extends AppModel
{
	public static function install( $model )
	{
		$model->connection->exec(
		'CREATE TABLE IF NOT EXISTS `example2` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `date` int(10) unsigned NOT NULL,
		  `message` varchar(255) NOT NULL,
		  `by` varchar(100) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;');

		return true;
	}

	public static function uninstall( $model )
	{
		$model->connection->exec('DROP TABLE `example2`;');
		return true;
	}
}