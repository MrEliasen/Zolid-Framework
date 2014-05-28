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

class AppModel
{
	public $connection;
	public $session;
	public $installed = false;
	public $lastError;
	
	final public function __construct()
	{
		// Load the database configuration
		Configure::load('database');

		// Connect to the database
		$this->connect();

		// initiate the user session
		$this->session = new Session($this);
		$this->session->start();
	}
	
	/**
	 * Establishes the connection to the database and binds the PDO obeject to the $connection property.
	 */
	private function connect()
	{
		if( Configure::get('database/host') == null )
		{
			return;
		}

		// Only attempt to set up a new connection if none exists
		if( !($this->connection instanceof PDO) )
		{
			try {
				$this->connection = new PDO(
					'mysql:host=' . Configure::get('database/host') . ';port=' . Configure::get('database/port') . ';dbname=' . Configure::get('database/dbname') . ';charset=' . Configure::get('database/charset'),
		            Configure::get('database/user'),
		            Configure::get('database/pass'),
		            array(
		                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES "' . Configure::get('database/charset') . '"',
		                PDO::ATTR_EMULATE_PREPARES => false
		            )
	            );
                
                // This avoids issues on some webservers like WAMP on windows.
                if( Configure::get('database/dbname') != '' && Configure::get('database/user') != '' )
                {
                    $this->installed = true;
                }
            }
			catch (PDOException $e) {
				if( $this->installed )
				{
					throw new Exception($e->getMessage());
				}
			}
		}
	}

	public function beginTransaction()
	{
		return $this->connection->beginTransaction();
	}

	public function lastInsertId()
	{
		return $this->connection->lastInsertId();
	}

	public function commit()
	{
		return $this->connection->commit();
	}

	public function rollback()
	{
		return $this->connection->rollback();
	}

	public function getSessionAccount()
	{
		$stmt = $this->connection->prepare('SELECT s.uid, a.username, a.sessid, a.permissions, ( SELECT COUNT(id) FROM ' . Configure::get('database/prefix')  . 'mailbox WHERE recipent = s.uid AND isread="0" ) as newmessages FROM ' . Configure::get('database/prefix')  . 'sessions as s LEFT JOIN ' . Configure::get('database/prefix')  . 'accounts as a ON a.id = s.uid WHERE s.id = :id');
		$stmt->bindValue(':id', session_id(), PDO::PARAM_INT);
		$stmt->execute();
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		return $data;
	}

	public function getPluginInfo( $plugin )
	{
		$stmt = $this->connection->prepare('SELECT * FROM ' . Configure::get('database/prefix') . 'plugins WHERE dir = :dr LIMIT 1');
		$stmt->bindValue(':dr', Security::sanitize($plugin, 'purestring'), PDO::PARAM_STR);
		$stmt->execute();
		$data = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		return $data;
	}
}