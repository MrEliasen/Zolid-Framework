<?php
/**
 *  Zolid Framework
 *
 *  A class which handles the displaying and generation of the website pages.
 *
 *  @author     Mark Eliasen
 *  @copyright  (c) 2013 - Mark Eliasen
 *  @version    0.0.1
 */

if( !defined('CORE_PATH') )
{
    die('Direct file access not allowed.');
}

class Template extends User
{
	protected $page;
	private $tmpl_vars;
	private $header = '';
	private $footer = '';
	protected $csrfTokens = array();
	
	/**
	 * The tempalte constructor function. sets the page value and initiates the page rendering or ajax request.
	 */
	public function __construct()
	{
		parent::__construct();

		if( !defined('IN_SYSTEM') )
		{
			// Find the page the user requested, if none, it must be the dashboard
			$this->page = Security::sanitize( ( !empty($_GET['p']) ? $_GET['p'] : 'index'), 'page');
			
			// Check if ´the request was not for a page, but was for some data (like from AJAX) or an action. If not, show page as normal.
			switch( $this->page )
			{
				case 'ajax':
					$this->processGeneralRequest();
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
	 * @return mixed if there are output for the request, it will echo it out.
	 */
	private function processGeneralRequest()
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
                $this->installFramework();
                break;
		}

		if( !empty($output) )
		{
			echo $output;
		}

		exit;
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
			header('Location:' . $this->__get('base_url') . '/install');
			exit;
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
			return ' class="active"';
		}
	}

	/**
	 * generates the page navigation
	 * 
	 * @return html the navigation html
	 */
	public function generateNavigation()
	{
		if( $this->page == 'install' )
		{
			return '';
		}
		
		$output = '';
		
		if( !$this->logged_in )
		{
			$output .= '<ul class="nav">
							<li ' . $this->activepage('index') . '>
								<a href="' . $this->__get('base_url') . '">' . $this->lang['core']['classes']['template']['navigation']['login'] . '</a>
							</li>
							<li ' . $this->activepage('register') . '>
								<a href="' . $this->__get('base_url') . '/register">' . $this->lang['core']['classes']['template']['navigation']['register'] . '</a>
							</li>
							<li ' . $this->activepage('recover') . '>
								<a href="' . $this->__get('base_url') . '/recover">' . $this->lang['core']['classes']['template']['navigation']['forgotpass'] . '</a>
							</li>
						</ul>';
		}
		else
		{
			$output .= '<ul class="nav">
							<li ' . $this->activepage('dashboard') . '>
								<a href="' . $this->__get('base_url') . '/dashboard">' . $this->lang['core']['classes']['template']['navigation']['dashboard'] . '</a>
							</li>
						</ul>';
			
			$output .= '<ul class="nav pull-right">
							<li class="divider-vertical"></li>
							<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown">
									<img src="http://www.gravatar.com/avatar/' . $_SESSION['data']['avatar'] . '?size=22&amp;d=mm&amp;r=pg" alt="">
									' . $_SESSION['data']['username'] . ' <b class="caret"></b>
								</a>
								<ul class="dropdown-menu">
									<li><a href="' . $this->__get('base_url') . '/settings">' . $this->lang['core']['classes']['template']['navigation']['settings'] . '</a></li>
									<li class="divider"></li>
									<li><a href="' . $this->__get('base_url') . '/?action=logout&amp;logout=' . Security::csrfGenerate('logout') . ' ">' . $this->lang['core']['classes']['template']['navigation']['logout'] . '</a></li>
								</ul>
							</li>
						</ul>';
		}
		
		return $output;
	}
    
    private function installFramework()
    {
        if( $this->installed )
        {
             echo json_encode( array('status' => false, 'message' => 'Framework already appears to be installed.') );
            exit;
        }
        
        if( empty($_POST['sqlhost']) || empty($_POST['sqlport']) || empty($_POST['sqldb']) || empty($_POST['sqluser']) || !isset($_POST['sqlpass'])
            || empty($_POST['site_name']) || empty($_POST['site_mail']) || empty($_POST['site_zone']) || empty($_POST['site_lang'])
            || empty($_POST['write_perm'])
          )
        {
            echo json_encode( array('status' => false, 'message' => 'Please fill out all the fields.') );
            exit;
        }
        
        /* check if we can connect to the database with the information the user supplied. */
        try{	
            $sql = new PDO("mysql:host=" . $_POST['sqlhost'] . ";port=" . $_POST['sqlport'] . ";dbname=" . $_POST['sqldb'] . ";charset=utf8", $_POST['sqluser'], $_POST['sqlpass']);
        }
        catch(PDOException $pe)
        {
            /* if not, ERROR! */
            echo json_encode( array('status' => false, 'message' => 'Unable to connect to database.') );
            exit;
        }
        
        /* Change the permissions for the config file is the permissions received is 0755 or 0777 */
        if( $_POST['write_perm'] == '0755' || $_POST['write_perm'] == '0777' )
        {
            chmod( CORE_PATH . '/config.php', octdec( $_POST['write_perm'] ) );
        }
        
        /* Is the config writeable or not now? */
        if( !is_writable( CORE_PATH . '/config.php' ) )
        {
            /* NOPE!.. damn..*/
            echo json_encode( array('status' => false, 'message' => 'Unable write to config file. Try changing the permissions.') );
            exit;
        }
        
        /* Safe the settings to the config file. */ 
        $sitename 		= Security::sanitize( $_POST['site_name'], 'purestring');
        $siteemail 		= Security::sanitize( $_POST['site_mail'], 'purestring');
        $sitetimezone 	= Security::sanitize( $_POST['site_zone'], 'purestring');
        $sitelang 	    = Security::sanitize( $_POST['site_lang'], 'purestring');
        
        $sql_host 		= Security::sanitize( $_POST['sqlhost'], 'purestring');
        $sql_port 		= Security::sanitize( $_POST['sqlport'], 'integer');
        $sql_user 		= Security::sanitize( $_POST['sqluser'], 'purestring');
        $sql_pass 		= Security::sanitize( $_POST['sqlpass'], 'purestring');
        $sql_db 		= Security::sanitize( $_POST['sqldb'], 'purestring');
        
        $smtp_host 		= Security::sanitize( ( !empty($_POST['smtp_host']) ? $_POST['smtp_host'] : '' ), 'purestring');
        $smtp_port 		= Security::sanitize( ( !empty($_POST['smtp_port']) ? $_POST['smtp_port'] : '' ), 'integer');
        $smtp_mail 		= Security::sanitize( ( !empty($_POST['smtp_user']) ? $_POST['smtp_user'] : '' ), 'purestring');
        $smtp_pass 		= Security::sanitize( ( !empty($_POST['smtp_pass']) ? $_POST['smtp_pass'] : '' ), 'purestring');
        
        $config = 
'<?php
$config = array(
    \'site_name\'=>\'' . $sitename . '\',
    \'site_email\'=>\'' . $siteemail . '\',
    \'timezone\'=>\'' . $sitetimezone . '\',
    \'default_lang\'=>\'' . $sitelang . '\',
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
            echo json_encode( array('status' => false, 'message' => 'Unable to write config file, please make sure it is writeable.') );
            exit;
        }
        
        /* add the tables to the database. */
       $sql->exec("
        CREATE TABLE IF NOT EXISTS `sessions` (
          `id` char(64) NOT NULL DEFAULT '',
          `data` text NOT NULL,
          `expire` int(12) NOT NULL DEFAULT '0',
          `agent` char(64) NOT NULL,
          `ip` char(64) NOT NULL,
          `host` char(64) NOT NULL,
          `acc` int(10) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        
        CREATE TABLE IF NOT EXISTS `users` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `username` char(25) NOT NULL,
          `email` blob NOT NULL,
          `email_hash` char(128) NOT NULL,
          `password` char(128) NOT NULL,
          `local` char(5) NOT NULL,
          `mail_admins` tinyint(1) NOT NULL DEFAULT '1',
          `mail_members` tinyint(1) NOT NULL,
          `reset_token` char(64) NOT NULL,
          `reset_time` int(11) NOT NULL,
          `active_key` char(32) NOT NULL,
          `session_id` char(64) NOT NULL,
          `acc_key` char(12) NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `username` (`username`),
          UNIQUE KEY `email_hash` (`email_hash`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
        
        if( !$success )
        {
            echo json_encode( array('status'=>false, 'message'=>'Unable to create the nessary tables in the database.') );
            exit; 
        }
        
        echo json_encode( array('status'=>true, 'message'=>'Installation completed.') );
        exit;
    }
}