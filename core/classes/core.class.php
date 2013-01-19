<?php
/**
 *  Zolid Framework
 *
 *  A class which handles sessions and database connection(s).
 *
 *  @author     Mark Eliasen
 *  @copyright  (c) 2013 - Mark Eliasen
 *  @version    0.0.1
 */

if( !defined('CORE_PATH') )
{
    die('Direct file access not allowed.');
}

class Core
{
	protected $page;
	protected $installed = false;
    protected $system = array();
	protected $sql = null;
    protected $lang = array();
    protected $queries = 0;
	protected $config = array();
    protected $base_url = '';
    protected $site_name = '';
	
    /**
     * The core class construct. Loads the config file and initiates, generates the base url, the database connection function.
     */
	public function __construct()
	{
		// Find the page the user requested, if none, it must be the dashboard
		$this->page = Security::sanitize( ( !empty($_GET['p']) ? $_GET['p'] : 'index'), 'page');
			
		/*
		 * Build base url to the system
		 * * * * * * * * * * * * * * * */
		// Are we using https
		$url_scheme = ( ( isset( $_SERVER['HTTPS'] ) && strtolower( $_SERVER['HTTPS'] ) == 'on') || $_SERVER['SERVER_PORT'] == 443 ? 'https://' : 'http://' );
		$baseurl = $_SERVER['HTTP_HOST'];
		if( $_SERVER['SERVER_PORT'] != 80 ) {
            $baseurl = str_replace(':' . $_SERVER['SERVER_PORT'], '', $baseurl); // remove port from HTTP_HOST if https
        }
		
		// is it installed or running from a sub directory
		$path = dirname(__FILE__);
		$subdir = str_replace( '/core/classes', '', $path );
		
		// Plesk, cPanel or maybe Windows server ?
		switch( true )
		{
			// Plesk
			case ( strpos($subdir, 'httpdocs') !== false ):
				$subdir = substr( $subdir, strpos( $subdir, '/httpdocs' ) + 9 );
				break;
				
			// cPanel
			case ( strpos($subdir, 'public_html') !== false ):
				$subdir = substr( $subdir, strpos( $subdir, '/public_html' ) + 12 );
				break;
				
			// Windows
			case ( strpos($subdir, 'inetpub') !== false ):
				$subdir = substr( $subdir, strpos( $subdir, '\httpdocs' ) + 9 );
				break;
		}
		
		// The finished url
		$this->base_url = $url_scheme . $baseurl . $subdir;
		

		if( !filesize( CORE_PATH . '/config.php' ) )
		{
			$this->site_name = 'Zolid Framework';
			return false;
		}
		
		try
		{			
			require_once( CORE_PATH . '/config.php' );
			$this->config = $config;
			$this->site_name = ( !empty($config['site_name']) ? $config['site_name'] : 'Zolid Framework' );
			date_default_timezone_set( $config['timezone'] );
			
			unset($config);
		}
		catch( Exception $e )
		{
			Error::log( 47, print_r($e), 'core.class.php' );
			die( $this->lang['core']['classes']['core']['config_error'] );
		}
		
		$this->installed = true;
		$this->dbConnect();
        
        // Load the default language file if none specific is set
        if( !empty($_SESSION['local']) && file_exists( CORE_PATH . '/locale/' . Security::sanitize($_SESSION['local'], 'purestring') . '.php' ) )
        {
            $this->lang = include( CORE_PATH . '/locale/' . Security::sanitize($_SESSION['local'], 'purestring') . '.php' );
        }
        else
        {
            $this->lang = include( CORE_PATH . '/locale/' . Security::sanitize($this->config['default_lang'], 'purestring') . '.php' );
        }
	}
	
    /**
     * the Getter functions
     * @param  string $property the property
     * @return mixed           the property value or empty string if not found.
     */
	public function __get($property)
	{
		if(property_exists($this, $property))
		{
			return $this->$property;
		}
        else
        {
            return '';
        }
	}
	
    /**
     * Setter function
     * 
     * @param string $property the property
     * @param mixed $value    the value
     */
	public function __set($property, $value)
	{
		if(property_exists($this, $property))
		{
			$this->$property = $value;
		}
	}

    /**
     * connects to the database using the details in the config file.
     * 
     * @return boolean ture on success, else throw error.
     */
	private function dbConnect()
	{
		try{
			$sql = new PDO(
				$this->config['sql']['type'] . ':' .
                'host=' . $this->config['sql']['host'] .
                ';port=' . $this->config['sql']['port'] .
                ';dbname=' . $this->config['sql']['database'] .
                ';charset=' . $this->config['sql']['charset'],
                $this->config['sql']['user'],
                $this->config['sql']['password'],
                array(
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES "utf8"',
					PDO::ATTR_EMULATE_PREPARES => false
                )
            );
			
            $this->sql = $sql;			
			$this->loadSession();

            return true;
        }
		catch(PDOException $pe)
        {
			Error::log(94, print_r($pe), 'core.class.php');
            die($this->lang['core']['classes']['core']['mysql_error']);
		}
	}

    /**
     * Sets the sessions to be stored in the database, and attach the functions to do the session management with.
     * 
     * @return boolean true, to let us know the function has run.
     */
	protected function loadSession()
	{
        session_set_save_handler(
            array( $this, "open" ),
            array( $this, "close" ),
            array( $this, "read" ),
            array( $this, "write"),
            array( $this, "destroy"),
            array( $this, "gc" )
        );
        
        session_start();
        $this->secureSession();
	}

    /**
     * regenerates the session id if we can see it has not been generated from this script.
     * 
     * @param  boolean $force forcefully regenerate the session id, no matter what.
     * @return boolean         ture if the session id was regenerated, false if not/not required.
     */
    protected function secureSession( $force = false )
    {
        if(!$force && !empty($_SESSION['secure_session']) )
        {
            if( $_SESSION['secure_session'] == hash_hmac('sha256', session_id().$this->config['global_salt'], $this->config['global_key']) )
            {
                return false;
            }
        }

        // The session was not generated by the system, regenerate a new session ID and tell the system this session is "OK".
        session_regenerate_id(true);
        $_SESSION['secure_session'] = hash_hmac('sha256', session_id().$this->config['global_salt'], $this->config['global_key']);

        return true;
    }

    /**
     * deletes the session from the database
     * @param  string $id session id, if none is supplied, it will use the current user's session id
     * @return boolean     true.
     */
    protected function destroy($id = null)
    {
        $destroy = $this->sql->prepare('DELETE FROM sessions WHERE id = ?');
        $destroy->execute( array( Security::sanitize( (!empty($id) ? $id : session_id() ), 'string' ) ) );
        $destroy->closeCursor();
        $this->queries++;

        return true;
    }

    /* not used for anything by to keep the session_set_save_handler() function happy */
    protected function open()
    {
        return true;
    }

    /* not used for anything by to keep the session_set_save_handler() function happy */
    protected function close()
    {
        return true;
    }

    /**
     * reads session data from the database
     * 
     * @param  string $id session id
     * @return string     session data
     */
    protected function read( $id )
    {
        $s_data = '';
        $read = $this->sql->prepare('SELECT
										`data`
									FROM
										`sessions`
									WHERE
										`id` = ?');

        $read->execute( array( $id ) );
        $this->queries++;

        if( $read->rowCount() > 0 )
        {
            while( $row = $read->fetch(PDO::FETCH_ASSOC) )
            {
                $s_data = $row['data'];
            }
        }

        $read->closeCursor();

        return $s_data;
    }

    /**
     * writes the session data to the database
     * @param  string $id   session id
     * @param  string $data session data
     * @return boolean       true on succes, false on error
     */
    protected function write($id, $data)
    {
        $write = $this->sql->prepare('INSERT INTO
												`sessions`
												(`id`, `data`, `expire`, `agent`, `ip`, `host`)
											VALUES
												(?,?,?,?,?,?)
											ON DUPLICATE KEY UPDATE
												`data` = VALUES(data),
												`expire` = VALUES(expire)');

        $write->execute(
					array(
						$id,
						$data,
						time(),
						$this->session_encryption( $_SERVER['HTTP_USER_AGENT'] ),
						$this->session_encryption( $_SERVER['REMOTE_ADDR'] ),
						$this->session_encryption( gethostbyaddr( $_SERVER['REMOTE_ADDR'] ) ),
					)
				);

        $this->queries++;

        $write->closeCursor();
        $err = $write->errorInfo();

        if(!empty($err[2]))
        {
            Error::log(197, print_r( $write->errorInfo() ), 'core.class.php');
            return false;
        }

        return true;
    }
	
    /**
     * Session garbage collection
     * @return boolean true
     */
	protected function gc()
	{	
		$gc_qry = $this->sql->prepare('DELETE FROM `sessions` WHERE expire < ?');
		$q_data = array(time()-600);
		$gc_qry->execute($q_data);
		$gc_qry->closeCursor();
		$this->queries++;
		
		return true;
	}

    /**
     * function hashes the session data the same way every time so we can use this to check if hashes match
     * 
     * @param  string $string the string/data to hash
     * @return string         the hashed string/data
     */
    protected function session_encryption($string)
    {
        $string = hash_hmac('sha256', $this->config['global_salt'].$string, $this->config['global_key']);

        return $string;
    }
}