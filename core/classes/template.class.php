<?php
/**
 *  Zolid Framework - MIT licensed
 *  https://github.com/MrEliasen/Zolid-Framework
 *
 *  A class which handles the displaying and generation of the website pages.
 *
 *  @author     Mark Eliasen
 *  @website    www.zolidweb.com
 *  @copyright  (c) 2013 - Mark Eliasen
 *  @version    0.1.2
 */

if( !defined('CORE_PATH') )
{
    die('Direct file access not allowed.');
}

class Template extends Admin
{
	protected $page;
	
	/**
	 * The tempalte constructor function. sets the page value and initiates the page rendering or ajax request.
	 */
	public function __construct()
	{
		parent::__construct();

		if( !defined('IN_SYSTEM') )
		{
			// Check if ´the request was not for a page, but was for some data (like from AJAX) or an action. If not, show page as normal.
			switch( $this->page )
			{
				case 'ajax':
					$this->processAjaxRequest();
					exit;
					break;

				default:
					$this->showPage();
					exit;
			}
		}
	}

	/**
	 * this will run the requested AJAX request. The request is based on the $_REQUEST['ajax'] value
	 * 
	 * @return mixed, json for all standard request.
	 */
	private function processAjaxRequest()
	{
		// Check if we have receive the data we need to build the query
		if( empty( $_REQUEST['a'] ) )
		{
			return $this->lang['core']['classes']['template']['notfound'];
		}

        // Sanitize the user input
        $_REQUEST['a'] = Security::sanitize( $_REQUEST['a'], 'page');
		
		// Check which type of query we are building
		switch( $_REQUEST['a'] )
		{
			case 'install':
                $output = $this->installFramework();
                break;

            case 'modal_editaccount':
            	$output = $this->getAdminModal('editaccount');
            	break;

            case 'modal_editgroup':
            	$output = $this->getAdminModal('editgroup');
            	break;

            case 'modal_addgroup':
            	$output = $this->getAdminModal('addgroup');
            	break;

            case 'saveaccchanges':
            	$output = $this->adminSaveAccChanges();
            	break;

            case 'savegroupchanges':
            	$output = $this->adminSaveGroupChanges();
            	break;

           	case 'deleteaccount':
            	$output = $this->adminDeleteAccount();
           		break;

           	case 'deletegroup':
            	$output = $this->adminDeleteGroup();
           		break;

           	case 'savesettings':
           		$output = $this->saveSettings();
           		break;
		}

		if( !empty($output) )
		{
			echo $output;
		}

		exit;
	}

	protected function generateURL( $page, $params = array() )
	{
		// Initial idea for basic SEO url generation (later parse the url by / )
		// SEO url generator panel? append htaccess code directly and compare urls with the parsers data? <<
		$url = $this->base_url;

		if( !empty($this->config['seo_urls']) && $this->config['seo_urls'] )
		{
			$url .= '/' . $page;
		}
		else
		{
			$url .= '/?p=' . $page;
		}

		if( !empty($params) && is_array($params) )
		{
			$c = ( !empty($this->config['seo_urls']) && $this->config['seo_urls'] ? 0 : 1 );
			foreach( $params as $key => $value )
			{
				$url .= ( empty($c) ? '?' : '&' ) . $key . '=' . $value;
				$c++; 
			}

			$url = Security::sanitize($url, 'string');
		}

		return $url;
	}
	
	/**
	 * renders the page the user requested, or 404 if not found. If the requested file is not a .php file, it will not show the 404 file but simply return false.
	 * 
	 * @return html the page html output.
	 */
	private function showPage()
	{		
		//check to see if we are requesting a file which are not one of the pages, so we do not redirect the user if not found.
		if( strpos($this->page, '.') !== false && end( explode('.', $this->page ) ) != 'php' )
		{
			return true;
		}
		
		// Check if the system is installed, else redirect them to the installer
		if( !$this->installed && $this->page != 'install' )
		{
			$this->page = 'install';

			// send no cache headers when we are at the installer.
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	        header('Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT');
	        header('Cache-Control: no-store, no-cache, must-revalidate');
	        header('Cache-Control: post-check=0, pre-check=0', false);
	        header('Pragma: no-cache');
		}
        
        // protect certain pages from being accessed
        if( $this->installed && $this->page == 'install' || in_array( $this->page, array('header', 'footer') ))
		{
			$this->page = 'index';
		}
		
		//check if the requested page exists, and if it is not the 404 page
		if( !file_exists(CORE_PATH . '/templates/'. $this->page . '.php') && $this->page != '404' )
		{
			// If the page is not found show the 404 page.
			$this->page = '404';
		}
        
        // fix for the CSRF token invalidation
        if( $this->page == '404' && !empty($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] == '/favicon.ico' )
        {
            return false;
        }
		
		//show the page to the user
		ob_start();
			include(CORE_PATH . '/templates/header.php');
			include(CORE_PATH . '/templates/'. $this->page.'.php');
			include(CORE_PATH . '/templates/footer.php');
		ob_end_flush();
	}
	
	/**
	 * checks if whether the page is the one the user is currently viewing.
	 * 
	 * @param  string $page the page name (without .php)
	 * @return string       returns the active class if true.
	 */
	protected function activepage( $page )
	{
		if( $this->page == $page)
		{
			return 'active';
		}
	}
    
    private function installFramework()
    {
        if( $this->installed )
        {
            return json_encode( array('status' => false, 'message' => 'Framework already appears to be installed.') );
        }
        
        if( empty($_POST['sqlhost']) || empty($_POST['sqlport']) || empty($_POST['sqldb']) || empty($_POST['sqluser'])
            || !isset($_POST['sqlpass']) || empty($_POST['site_name']) || empty($_POST['site_mail']) || empty($_POST['site_zone'])
            || empty($_POST['site_lang']) || empty($_POST['site_url'])
          )
        {
            return json_encode( array('status' => false, 'message' => 'Please fill out all the fields.') );
        }
        
        /* check if we can connect to the database with the information the user supplied. */
        try{	
            $sql = new PDO("mysql:host=" . $_POST['sqlhost'] . ";port=" . $_POST['sqlport'] . ";dbname=" . $_POST['sqldb'] . ";charset=utf8", $_POST['sqluser'], $_POST['sqlpass']);
        }
        catch(PDOException $pe)
        {
            /* if not, ERROR! */
            return json_encode( array('status' => false, 'message' => 'Unable to connect to database.') );
        }
        
        /* Safe the settings to the config file. */ 
        $sitename 		= Security::sanitize( $_POST['site_name'], 'purestring');
        $sitebaseurl    = Security::sanitize( $_POST['site_url'], 'purestring');
        $siteseourl     = ( !empty($_POST['site_seourl']) ? 1 : 0 );
        $siteemail 		= Security::sanitize( $_POST['site_mail'], 'purestring');
        $sitetimezone 	= Security::sanitize( $_POST['site_zone'], 'purestring');
        $sitelang 	    = Security::sanitize( $_POST['site_lang'], 'purestring');
        
        $sql_host 		= Security::sanitize( $_POST['sqlhost'], 'purestring');
        $sql_port 		= Security::sanitize( $_POST['sqlport'], 'integer');
        $sql_user 		= Security::sanitize( $_POST['sqluser'], 'purestring');
        $sql_pass 		= Security::sanitize( $_POST['sqlpass'], 'string');
        $sql_db 		= Security::sanitize( $_POST['sqldb'], 'purestring');
        
        $smtp_host 		= Security::sanitize( ( !empty($_POST['smtp_host']) ? $_POST['smtp_host'] : '' ), 'purestring');
        $smtp_port 		= Security::sanitize( ( !empty($_POST['smtp_port']) ? $_POST['smtp_port'] : '' ), 'integer');
        $smtp_mail 		= Security::sanitize( ( !empty($_POST['smtp_user']) ? $_POST['smtp_user'] : '' ), 'purestring');
        $smtp_pass 		= Security::sanitize( ( !empty($_POST['smtp_pass']) ? $_POST['smtp_pass'] : '' ), 'string');

        $config = 
'<?php
$config = array(
    \'base_url\'=>\'' . $sitebaseurl . '\',
    \'sql\'=>array(
        \'type\'=>\'mysql\',
        \'host\'=>\'' . $sql_host . '\',
        \'port\'=>' . $sql_port . ',
        \'user\'=>\'' . $sql_user . '\',
        \'password\'=>\'' . $sql_pass . '\',
        \'database\'=>\'' . $sql_db . '\',
        \'charset\'=>\'utf8\'
    ),
    \'smtp\'=>array(
        \'host\'=>\'' . $smtp_host . '\',
        \'port\'=>\'' . $smtp_port . '\',
        \'user\'=>\'' . $smtp_mail . '\',
        \'pass\'=>\'' . $smtp_pass . '\'
    ),
    \'global_salt\'=>\'' . Security::randomGenerator(128) . '\',
    \'global_key\'=>\'' . Security::randomGenerator(128) . '\',
    \'AES\'=>array(
        \'salt\'=>\'' . Security::randomGenerator(128) . '\',
        \'level-1\'=>\'' . Security::randomGenerator(128) . '\'
    ),
);';
        
        /* write the config file */
        $theFile = @fopen(CORE_PATH . '/config.php', 'w');
        $success = @fwrite($theFile, $config);
        @fclose($theFile);
        
        /* was the config written successfully? */
        if( !$success )
        {
            /* NOPE!.. damn..*/
            return json_encode( array('status' => false, 'message' => 'Unable to write config file, please make sure it is writeable.') );
        }
        
        /* add the tables to the database. */
       $sql->exec("
        CREATE TABLE IF NOT EXISTS `groups` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `title` varchar(100) NOT NULL,
		  `permissions` text NOT NULL,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `title` (`title`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3;

		CREATE TABLE IF NOT EXISTS `sessions` (
		  `id` varchar(40) NOT NULL,
		  `data` text NOT NULL,
		  `expire` int(12) NOT NULL DEFAULT '0',
		  `agent` char(64) NOT NULL,
		  `ip` char(64) NOT NULL,
		  `host` char(64) NOT NULL,
		  `acc` int(10) unsigned NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;

		CREATE TABLE IF NOT EXISTS `settings` (
		  `key` varchar(25) NOT NULL,
		  `value` varchar(100) NOT NULL,
		  `title` varchar(20) NOT NULL,
		  `type` enum('text','select') NOT NULL DEFAULT 'text',
		  `options` text NOT NULL,
		  PRIMARY KEY (`key`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;

		CREATE TABLE IF NOT EXISTS `users` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `username` varchar(25) NOT NULL,
		  `email` blob NOT NULL,
		  `email_hash` char(128) NOT NULL,
		  `password` char(128) NOT NULL,
		  `local` varchar(5) NOT NULL,
		  `group` int(10) unsigned NOT NULL,
		  `reset_token` char(64) NOT NULL,
		  `reset_time` int(11) NOT NULL,
		  `active_key` char(32) NOT NULL,
		  `session_id` char(64) NOT NULL,
		  `acc_key` char(12) NOT NULL,
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `username` (`username`),
		  UNIQUE KEY `email_hash` (`email_hash`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

		INSERT INTO `groups` (`id`, `title`, `permissions`) VALUES
		(1, 'Member', ''),
		(2, 'Admin', '{\"admin\":1}');");

		// Insert the user settings into the database
        $stmt = $sql->prepare('INSERT INTO 
        								settings (`key`, `value`, `title`, `type`, `options`)
        							VALUES 
        								("site_name", ?, "Site Name", "text", ""),
        								("site_email", ?, "Site Email", "text", ""),
        								("seo_urls", ?, "Friendly URLs", "select", "{\"On\":1, \"Off\":0}"),
        								("timezone", ?, "System Timezone", "select", ?)
        							ON 
        								DUPLICATE KEY 
        							UPDATE 
        								`value` = VALUES(`value`)');
        $success = $stmt->execute(array(
        	$sitename,
	        $siteemail,
	        $siteseourl,
	        $sitetimezone,
	        json_encode( array_flip( Data::getTimezones() ) )
        ));
        $stmt->closeCursor();
        
        // Test the user table where indeed added.
        $test = $sql->query('SELECT * FROM groups');
        $success = $test->execute();
        $test->closeCursor();

        if( !$success )
        {
            return json_encode( array('status'=>false, 'message'=>'The nessesary tables does not appear to have been created in the database.') );
        }
        
        // try to delete the install file, just in case.
        if( file_exists(CORE_PATH . '/templates/install.php') )
        {
            unlink( CORE_PATH . '/templates/install.php');
        }
        
        return json_encode( array('status'=>true, 'message'=>'Installation completed! Redirecting you to your new website in < 3 seconds.', 'redirect' => $sitebaseurl) );
    }
}