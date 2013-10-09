<?php
/**
 *  Zolid Framework - MIT licensed
 *  https://github.com/MrEliasen/Zolid-Framework
 *
 *  A class which handles sessions and database connection(s), and caching.
 *
 *  @author     Mark Eliasen
 *  @website    www.zolidsolutions.com
 *  @copyright  (c) 2013 - Mark Eliasen
 *  @version    0.1.5
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
    
    /**
     * The core class construct. Loads the config file and initiates, generates the base url, the database connection function.
     */
    public function __construct()
    {
        // Find the page the user requested, if none, it must be the dashboard
        $this->page = Security::sanitize( ( !empty($_GET['p']) ? $_GET['p'] : 'index'), 'page');
        
        
        if( !file_exists(CORE_PATH . '/config.php') || !filesize( CORE_PATH . '/config.php' ) )
        {
            $this->generateBaseUrl();
            $this->config['site_name'] = 'Zolid Framework';
            return false;
        }
        else
    	{
    		require_once( CORE_PATH . '/config.php' );
            $this->config = $config;
            unset($config);
    	}

        // Quick cloudflare user IP fix
        if( !empty($_SERVER["HTTP_CF_CONNECTING_IP"]) )
        {
        	// if the system is hosted on a website protected with CloudFlare, we need to change the way we get the user's
        	// IP address, so we bind the IP cloudflare sends to us to the normal remote_addr variable.
			$_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
		}
        
        $this->installed = true;
        $this->dbConnect();
        $this->getSettings();
        
        // Load the default language file if none specific is set
        if( !empty($_SESSION['local']) && file_exists( CORE_PATH . '/locale/' . Security::sanitize($_SESSION['local'], 'purestring') . '.php' ) )
        {
            $this->lang = include( CORE_PATH . '/locale/' . Security::sanitize($_SESSION['local'], 'purestring') . '.php' );
        }
        else
        {
            $this->lang = include( CORE_PATH . '/locale/default.php' );
        }
    }
    
    /**
     * Generates the base url if non is provided.
     * @param  string $baseUrl the provided (if any) url.
     * @return boolean returns true when the function completes.
     */
    private function generateBaseUrl( $baseUrl = '' )
    {
        if( empty($baseUrl) )
        {
            $https = ( isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : false );
            $baseUrl= ( !$https || strtolower($https) !== 'on' ? 'http://' : 'https://' );
            $baseUrl .= $_SERVER['HTTP_HOST'];
            
            if( $_SERVER['SERVER_PORT'] !== 80 )
            {
                $baseUrl = str_replace(':' . $_SERVER['SERVER_PORT'], '', $baseUrl);
            }
            
            $baseUrl .= ( $_SERVER['SERVER_PORT'] == 80 || ( $https !== false || strtolower($https) == 'on' ) ? '' : ':' . $_SERVER['SERVER_PORT'] );
            $baseUrl .= $_SERVER['PHP_SELF'];
            
            $baseUrl = explode('/', $baseUrl);
            unset( $baseUrl[ count($baseUrl) -1 ] );
            $baseUrl = implode('/', $baseUrl);
        }
        
        // The finished url
        $this->base_url = $baseUrl;
        return true;
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
            array( $this, 'open' ),
            array( $this, 'close' ),
            array( $this, 'read' ),
            array( $this, 'write' ),
            array( $this, 'destroy' ),
            array( $this, 'gc' )
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
            if( $_SESSION['secure_session'] == sha1( session_id() . $this->config['global_salt']) )
            {
                return false;
            }
        }

        // The session was not generated by the system, regenerate a new session ID and tell the system this session is "OK".
        //session_regenerate_id(true);
        $_SESSION['secure_session'] = sha1( session_id() . $this->config['global_salt']);

        return true;
    }

    /**
     * deletes the session from the database
     * @param  string $id session id, if none is supplied, it will use the current user's session id
     * @return boolean     true.
     */
    public function destroy($id = null)
    {
        $destroy = $this->sql->prepare('DELETE FROM sessions WHERE id = ?');
        $destroy->execute( array( Security::sanitize( (!empty($id) ? $id : session_id() ), 'string' ) ) );
        $destroy->closeCursor();
        $this->queries++;

        return true;
    }

    // not used for anything but to keep the session_set_save_handler() function happy
    public function open()
    {
        return true;
    }

    // not used for anything but to keep the session_set_save_handler() function happy
    public function close()
    {
        return true;
    }

    /**
     * reads session data from the database
     * 
     * @param  string $id session id
     * @return string     session data
     */
    public function read( $id )
    {
        $s_data = '';
        $read = $this->sql->prepare('SELECT
                                        `data`
                                    FROM
                                        `sessions`
                                    WHERE
                                        `id` = ?
                                    LIMIT 1');

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
    public function write($id, $data)
    {
        $write = $this->sql->prepare('INSERT INTO
                                                `sessions`
                                                (`id`, `data`, `expire`, `agent`, `ip`, `host`)
                                            VALUES
                                                (?,?,?,?,?,?)
                                            ON DUPLICATE KEY UPDATE
                                                `data` = VALUES(data),
                                                `expire` = VALUES(expire)');

        $write->execute(array(
            $id,
            $data,
            time(),
            $this->session_encryption( ( !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '' ) ),
            $this->session_encryption( $_SERVER['REMOTE_ADDR'] ),
            $this->session_encryption( gethostbyaddr( $_SERVER['REMOTE_ADDR'] ) ),
        ));

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
    public function gc()
    {
        $stmt = $this->sql->prepare('DELETE FROM `sessions` WHERE expire < ?');
        $stmt->execute(array(time() - 600));
        $stmt->closeCursor();
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
        $string = hash_hmac('sha256', $this->config['global_salt'] . $string, $this->config['global_key']);

        return $string;
    }

    /**
     * Fetches and caches the current framework settings. Re-caching will only happen if any changes are made to the settings.
     * @return [type]
     */
    private function getSettings()
    {
        $config = $this->showCache('framework_settings', 22118400); // 1 year, its recached if the user makes any changes.
        
        if( empty($config) )
        {
            $stmt = $this->sql->query('SELECT * FROM settings');
            $stmt->execute();

            $config = array();
            while( $setting = $stmt->fetch(PDO::FETCH_ASSOC) )
            {
                $config[ $setting['key'] ] = $setting['value'];
            }

            $stmt->closeCursor();

            // Cache the settings for another year, or until the user makes changes
            $this->addCache(json_encode($config), 'framework_settings');
        }
        else
        {
            $config = json_decode($config, true);
        }
        
        $this->config = array_merge($config, $this->config);

        $this->generateBaseUrl( $this->config['base_url'] );
        date_default_timezone_set( $this->config['timezone'] );
        setlocale(LC_ALL, 'en_UK.UTF8');
    }

    /**
     * retuns the cached content if it exists and is not expired, else it returns empty.
     * 
     * @param  string $name the unique cache identifier aka name
     * @return string       the cached content.
     */
    public function showCache( $name, $time = 0 )
    {
        if( empty($time) )
        {
            $time = 600;
            if( !empty($this->config['cache_time']) )
            {
                $time = $this->config['cache_time'];
            }
        }
        
        $dir = ( !empty($this->config['cache']) ? $this->config['cache'] : CORE_PATH . '/cache' );
        $from_file = $name . '.cache';
        
        if( file_exists($dir . '/' . $from_file) )
        {
            if( filesize($dir . '/' . $from_file) > 0 )
            {
                if( filemtime($dir . '/' . $from_file) > time() - $time )
                {
                    ob_start();
                    include($dir . '/' . $from_file);
                    $cache = ob_get_clean();

                    return $cache;
                }
            }
        }

        return '';
    }

    /**
     * caches the $output to a .cache file with the name of $name
     * 
     * @param mised $output the output you wish to cache
     * @param string $name   the name of the cache file, used later for retrieval
     */
    public function addCache( $output, $name )
    {
        $dir = ( !empty($this->config['cache']) ? $this->config['cache'] : CORE_PATH . '/cache' );

        if( !is_dir($dir) )
        {
            mkdir($dir, 0755);
        }

        $to_file = $name . '.cache';

        $theFile = fopen($dir . '/' . $to_file, 'w');
        $saved = fwrite($theFile, $output);
        fclose($theFile);

        if( empty($this->config['cache']) )
        {
            chmod($dir . '/' . $to_file, 0750);
        }

        if( $saved )
        {
            return true;
        }

        return false;
    }

    protected function checkVersion()
    {
        $output = array(
            'current' => 'Unknown',
            'latest' => 'Unknown',
            'upgrade' => false,
            'release' => 0,
            'message' => ''
        );

        if( defined('ZF_VERSION') )
        {
            $output['current'] = ZF_VERSION;
            $versiondata = $this->showCache('versioncheck', 300);

            if( empty($versiondata) )
            {
                $ctx = stream_context_create(array( 
                    'http' => array( 
                        'timeout' => 10
                        ) 
                    ) 
                ); 
                $versiondata = file_get_contents('https://raw.github.com/MrEliasen/Zolid-Framework/master/latestversion', null, $ctx);
                $this->addCache($versiondata, 'versioncheck');
            }
            
            if( !empty($versiondata) )
            {
                $versiondata = json_decode($versiondata, true);
                if( version_compare($output['current'], Security::sanitize($versiondata['version'], 'mixedint')) < 0 )
                {
                    $output['upgrade'] = true;
                }

                $output['latest'] = Security::sanitize($versiondata['version'], 'mixedint');
                $output['release'] = Security::sanitize($versiondata['date'], 'integer');
                $output['message'] = Security::sanitize($versiondata['message'], 'string');
            }
            else
            {
                $output['latest'] = 'Failed (timeout)';
            }
        }

        return $output;
    }
}