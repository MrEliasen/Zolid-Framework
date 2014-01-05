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
	
	public function __construct()
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

	            $this->installed = true;
			}
			catch (PDOException $e) {
				if( $this->installed )
				{
					throw new Exception($e->getMessage());
				}
			}
		}
	}
}