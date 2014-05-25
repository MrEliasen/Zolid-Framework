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

class ModelsAdminPlugins extends AppModel
{
	public function getInstalledPlugins()
	{
		$stmt = $this->connection->prepare('SELECT * FROM ' . Configure::get('database/prefix') . 'plugins');
		$stmt->execute();
		$plugins = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		$list = array();
		if( !empty($plugins) )
		{
			foreach( $plugins as $plugin )
			{
				$list[ $plugin['dir'] ] = $plugin; 
			}
		}

		return $list;
	}

	public function installPlugin( $plugin, $dir )
	{
		$stmt = $this->connection->prepare('INSERT INTO ' . Configure::get('database/prefix') . 'plugins (dir, install_date, version) VALUES (:dr, :date, :vers) ON DUPLICATE KEY UPDATE dir = dir');
		$stmt->bindValue(':dr', Security::sanitize($dir, 'route'), PDO::PARAM_STR);
		$stmt->bindValue(':date', time(), PDO::PARAM_INT);
		$stmt->bindValue(':vers', Security::sanitize( (empty($plugin['version']) ? 'Unknown' : $plugin['version']), 'purestring'), PDO::PARAM_STR);
		$stmt->execute();
		$stmt->closeCursor();
		
		$success = $stmt->rowCount();

		return (bool)$success;
	}

	public function uninstallPlugin( $plugin )
	{
		$stmt = $this->connection->prepare('DELETE FROM ' . Configure::get('database/prefix') . 'plugins WHERE dir = :dr LIMIT 1');
		$stmt->bindValue(':dr', Security::sanitize($plugin, 'route'), PDO::PARAM_STR);
		$stmt->execute();
		$stmt->closeCursor();

		$success = $stmt->rowCount();

		return (bool)$success;
	}

	public function activtePlugin( $plugin )
	{
		$stmt = $this->connection->prepare('UPDATE ' . Configure::get('database/prefix') . 'plugins SET active = "1" WHERE dir = :dr LIMIT 1');
		$stmt->bindValue(':dr', Security::sanitize($plugin, 'route'), PDO::PARAM_STR);
		$stmt->execute();
		$stmt->closeCursor();
		
		$success = $stmt->rowCount();

		return (bool)$success;
	}

	public function deactivtePlugin( $plugin )
	{
		$stmt = $this->connection->prepare('UPDATE ' . Configure::get('database/prefix') . 'plugins SET active = "0" WHERE dir = :dr LIMIT 1');
		$stmt->bindValue(':dr', Security::sanitize($plugin, 'route'), PDO::PARAM_STR);
		$stmt->execute();
		$stmt->closeCursor();
		
		$success = $stmt->rowCount();

		return (bool)$success;
	}
}