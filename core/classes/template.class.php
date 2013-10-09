<?php
/**
 *  Zolid Framework - MIT licensed
 *  https://github.com/MrEliasen/Zolid-Framework
 *
 *  A class which handles the displaying and generation of the website pages.
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

            case 'modal_addforumcat':
                $output = $this->getAdminModal('addforumcat');
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

            case 'deleteforumcategory':
                $output = $this->adminDeleteForumCategory();
                break;

            case 'savenewforum':
                $output = $this->adminAddNewForum();
                break;

            case 'savesettings':
                $output = $this->saveSettings();
                break;

            /* FORUM */
            case 'modal_newpostquote':
                $output = $this->getUserModal('newpostquote');
                break;

            case 'addnewpost':
                $output = $this->addNewPost();
                break;

            case 'modal_newpost':
                $output = $this->getUserModal('newpost');
                break;

            case 'addnewthread':
                $output = $this->addNewThread();
                break;

            case 'modal_newthread':
                $output = $this->getUserModal('newthread');
                break;

            case 'editthread':
                $output = $this->updateThread();
                break;

            case 'modal_editthread':
                $output = $this->getUserModal('editthread');
                break;

            case 'editpost':
                $output = $this->updatePost();
                break;

            case 'modal_editpost':
                $output = $this->getUserModal('editpost');
                break;

            case 'deletethread':
                $output = $this->deleteThread();
                break;

            case 'deletepost':
                $output = $this->deletePost();
                break;

            case 'modal_editforumcategory':
                $output = $this->getAdminModal('editforumcategory');
                break;

            case 'updatecategory':
                $output = $this->adminEditCategory();
                break;

            case 'saveforumorder':
                $output = $this->adminUpdateForumOrder();
                break;
        }

        if( !empty($output) )
        {
            echo $output;
        }

        exit;
    }

    /**
     * Generate the correct url string, depending if the user has friendly urls enabled or not.
     * @param  string $page
     * @param  array  $params
     * @return string
     */
    protected function generateURL( $page, $params = array() )
    {
        //List of custom rewrites {{parameter-name-here}} will be replace with the param value
        $list = array(
            'forum_category' => 'forum/category/{{category}}/{{title}}/{{page}}',
            'forum_thread' => 'forum/thread/{{thread}}/{{title}}/{{page}}'
        );

        // Initial idea for basic SEO url generation (later parse the url by / maybe?)
        $url = $this->base_url;

        if( !empty($this->config['seo_urls']) )
        {
            if( !empty($list[ $page ]) )
            {
                $url .= '/' . $list[ $page ];
            }
            else
            {
                $url .= '/' . $page;
            }
        }
        else
        {
            $url .= '/?p=' . $page;
        }

        if( !empty($params) && is_array($params) )
        {
            if( empty($list[ $page ]) || !$this->config['seo_urls'] )
            {
                $c = ( !empty($this->config['seo_urls']) && $this->config['seo_urls'] ? 0 : 1 );
                foreach( $params as $key => $value )
                {
                    $url .= ( empty($c) ? '?' : '&' ) . $key . '=' . $this->stringToUrl($value);
                    $c++; 
                }
            }
            else
            {
                foreach( $params as $key => $value )
                {
                    $url = str_replace('{{' . $key . '}}', $this->stringToUrl($value), $url);
                }

                $url = preg_replace('/[\{\{](.*)[\}\}]\/?/', '', $url);
            }
        }

        return Security::sanitize($url, 'string');
    }

    /**
     * Generate a url friendly string (From http://cubiq.org/)
     * @param  string $page
     * @param  array  $params
     * @return string
     */
    protected function stringToUrl( $string, $replace = array('.', '\'') )
    {
        if( !empty($replace) ) {
            $string = str_replace((array)$replace, ' ', $string);
        }

        $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
        $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
        $clean = strtolower(trim($clean, '-'));
        $clean = preg_replace("/[\/_|+ -]+/", '-', $clean);

        return $clean;
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

            // send no cache headers when we are at the installer to try avoid cache redirect to the installer.
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
        if( $this->page == '404' )
        {
            return false;
        }
        
        // fix for the CSRF token invalidation
        if( !empty($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] == '/favicon.ico' )
        {
            $_SERVER['REQUEST_URI'] = Security::sanitize($_SERVER['REQUEST_URI'], 'string');
            if( file_exists($_SERVER['REQUEST_URI']) )
            {
                return $_SERVER['REQUEST_URI'];
            }

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
        if( is_array($page) )
        {
            foreach( $page as $pg )
            {
                if( $this->page == $pg)
                {
                    return 'active';
                }
            }
        }
        else
        {
            if( $this->page == $page)
            {
                return 'active';
            } 
        }
    }

    protected function getUserModal( $modal )
    {
        if( !$this->permission('loggedin') )
        {
            return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['template']['permission_err']));
        }

        switch( $modal )
        {
            case 'newpostquote':
                if( !empty($_GET['id']) )
                {
                    $stmt = $this->sql->prepare('SELECT body, thread, username FROM forum_posts INNER JOIN users on users.id = uid WHERE forum_posts.id = :pid');
                    $stmt->bindValue(':pid', Security::sanitize($_GET['id'], 'integer'), PDO::PARAM_INT);
                    $stmt->execute();
                    $post = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $stmt->closeCursor();
                }

                if( empty($post) || empty($_GET['id']) )
                {
                    $output = '<div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                        <h4 class="modal-title">' . $this->lang['core']['classes']['template']['modal_notfound1'] . '</h4>
                                    </div>
                                </div>
                            </div>';
                }
                else
                {
                    //$post[0]['body'] = Security::sanitize($post[0]['body'], 'purestring');
                    //$post[0]['body'] = ( strlen($post[0]['body']) > 300 ? substr($post[0]['body'], 0, 300) . '[&hellip;]' : $post[0]['body'] );

                    $output = '<div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                        <h4 class="modal-title">' . $this->lang['core']['classes']['template']['modal_title1'] . '</h4>
                                    </div>
                                    <div class="modal-body">
                                        <form id="acceptfill" class="form-inline" action="#" onsubmit="return false;">
                                            <textarea id="wysiwyg" class="form-control" name="body" rows="9"><blockquote>' . $post[0]['body'] . ' <small>' . $this->lang['core']['classes']['template']['modal_quoteby'] . $post[0]['username'] . '</small></blockquote>'  . "\n" . '</textarea>
                                            <p class="help-block">
                                                ' . $this->lang['core']['classes']['template']['modal_allowedhtml'] . '
                                            </p>

                                            <div class="hr"></div>

                                            <input type="hidden" name="tid" value="' . $post[0]['thread'] . '">
                                            <input type="hidden" name="addnewpost" value="' . Security::csrfGenerate('addnewpost') . '">
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">' . $this->lang['core']['classes']['template']['modal_close'] . '</button>
                                        <button type="button" class="btn btn-primary" id="savechanges">' . $this->lang['core']['classes']['template']['modal_save1'] . '</button>
                                    </div>
                                </div>
                            </div>';
                }

                return $output;
                break;

            case 'newpost':
                if( !empty($_GET['id']) )
                {
                    $stmt = $this->sql->prepare('SELECT id, title FROM forum_threads WHERE id = :tid');
                    $stmt->bindValue(':tid', Security::sanitize($_GET['id'], 'integer'), PDO::PARAM_INT);
                    $stmt->execute();
                    $thread = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $stmt->closeCursor();
                }

                if( empty($thread) || empty($_GET['id']) )
                {
                    $output = '<div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                        <h4 class="modal-title">' . $this->lang['core']['classes']['template']['modal_notfound2'] . '</h4>
                                    </div>
                                </div>
                            </div>';
                }
                else
                {
                    $output = '<div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                        <h4 class="modal-title">' . $this->lang['core']['classes']['template']['modal_title2'] . '<i>' . Security::sanitize($thread[0]['title'], 'purestring') . '</i></h4>
                                    </div>
                                    <div class="modal-body">
                                        <form id="acceptfill" class="form-inline" action="#" onsubmit="return false;">
                                            <textarea id="wysiwyg" class="form-control" name="body" rows="6" placeholder="' . $this->lang['core']['classes']['template']['modal_texthere'] . '"></textarea>
                                            <p class="help-block">
                                                ' . $this->lang['core']['classes']['template']['modal_allowedhtml'] . '
                                            </p>

                                            <div class="hr"></div>

                                            <input type="hidden" name="tid" value="' . $thread[0]['id'] . '">
                                            <input type="hidden" name="addnewpost" value="' . Security::csrfGenerate('addnewpost') . '">
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">' . $this->lang['core']['classes']['template']['modal_close'] . '</button>
                                        <button type="button" class="btn btn-primary" id="savechanges">' . $this->lang['core']['classes']['template']['modal_save2'] . '</button>
                                    </div>
                                </div>
                            </div>';
                }

                return $output;
                break;

            case 'newthread':
                if( !empty($_GET['id']) )
                {
                    $stmt = $this->sql->prepare('SELECT id, title FROM forum_categories WHERE id = :cid');
                    $stmt->bindValue(':cid', Security::sanitize($_GET['id'], 'integer'), PDO::PARAM_INT);
                    $stmt->execute();
                    $category = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $stmt->closeCursor();
                }

                if( empty($category) || empty($_GET['id']) )
                {
                    $output = '<div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                        <h4 class="modal-title">' . $this->lang['core']['classes']['template']['modal_notfound3'] . '</h4>
                                    </div>
                                </div>
                            </div>';
                }
                else
                {
                    $output = '<div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                        <h4 class="modal-title">' . $this->lang['core']['classes']['template']['modal_title3'] . '<i>' . Security::sanitize($category[0]['title'], 'purestring') . '</i></h4>
                                    </div>
                                    <div class="modal-body">
                                        <form id="acceptfill" class="form-inline" action="#" onsubmit="return false;">
                                            <input type="text" name="title" class="form-control" placeholder="' . $this->lang['core']['classes']['template']['modal_topictitle'] . '"><br>
                                            <br>
                                            <textarea class="form-control" name="body" rows="6" placeholder="' . $this->lang['core']['classes']['template']['modal_bodyhere'] . '"></textarea>
                                            <p class="help-block">
                                                ' . $this->lang['core']['classes']['template']['modal_allowedhtml'] . '
                                            </p>

                                            <div class="hr"></div>

                                            <input type="hidden" name="cid" value="' . $category[0]['id'] . '">
                                            <input type="hidden" name="addnewthread" value="' . Security::csrfGenerate('addnewthread') . '">
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">' . $this->lang['core']['classes']['template']['modal_close'] . '</button>
                                        <button type="button" class="btn btn-primary" id="savechanges">' . $this->lang['core']['classes']['template']['modal_save3'] . '</button>
                                    </div>
                                </div>
                            </div>';
                }

                return $output;
                break;

            case 'editthread':
                if( !empty($_GET['id']) )
                {
                    $stmt = $this->sql->prepare('SELECT t.id as tid, p.id as pid, title, body
                                                 FROM forum_threads as t
                                                 INNER JOIN forum_posts as p ON p.id = t.op
                                                 WHERE t.id = :tid ' . ( !$this->permission('admin') ? 'AND t.uid = :uid' : '' ));

                    $stmt->bindValue(':tid', Security::sanitize($_GET['id'], 'integer'), PDO::PARAM_INT);
                    if( !$this->permission('admin') )
                    {
                        $stmt->bindValue(':uid', Security::sanitize($_SESSION['data']['uid'], 'integer'), PDO::PARAM_INT);
                    }
                    $stmt->execute();
                    $thread = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $stmt->closeCursor();
                }

                if( empty($thread) || empty($_GET['id']) )
                {
                    $output = '<div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                        <h4 class="modal-title">' . $this->lang['core']['classes']['template']['modal_notfound4'] . '</h4>
                                    </div>
                                </div>
                            </div>';
                }
                else
                {
                    $output = '<div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                        <h4 class="modal-title">' . $this->lang['core']['classes']['template']['modal_title4'] . '<i>' . Security::sanitize($thread[0]['title'], 'purestring') . '</i></h4>
                                    </div>
                                    <div class="modal-body">
                                        <form id="acceptfill" class="form-inline" action="#" onsubmit="return false;">
                                            <input type="text" name="title" class="form-control" placeholder="' . $this->lang['core']['classes']['template']['modal_topictitle'] . '" value="' . Security::sanitize($thread[0]['title'], 'purestring') . '"><br>
                                            <br>
                                            <textarea class="form-control" name="body" rows="6" placeholder="' . $this->lang['core']['classes']['template']['modal_bodyhere'] . '">' . Security::sanitize($thread[0]['body'], 'forumpost') . '</textarea>
                                            <p class="help-block">
                                                ' . $this->lang['core']['classes']['template']['modal_allowedhtml'] . '
                                            </p>

                                            <div class="hr"></div>

                                            <input type="hidden" name="tid" value="' . $thread[0]['tid'] . '">
                                            <input type="hidden" name="pid" value="' . $thread[0]['pid'] . '">
                                            <input type="hidden" name="editthread" value="' . Security::csrfGenerate('editthread') . '">
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">' . $this->lang['core']['classes']['template']['modal_close'] . '</button>
                                        <button type="button" class="btn btn-primary" id="savechanges">' . $this->lang['core']['classes']['template']['modal_save4'] . '</button>
                                    </div>
                                </div>
                            </div>';
                }

                return $output;
                break;

            case 'editpost':
                if( !empty($_GET['id']) )
                {
                    $stmt = $this->sql->prepare('SELECT id, body
                                                 FROM forum_posts
                                                 WHERE id = :pid ' . ( !$this->permission('admin') ? 'AND uid = :uid' : '' ));

                    $stmt->bindValue(':pid', Security::sanitize($_GET['id'], 'integer'), PDO::PARAM_INT);
                    if( !$this->permission('admin') )
                    {
                        $stmt->bindValue(':uid', Security::sanitize($_SESSION['data']['uid'], 'integer'), PDO::PARAM_INT);
                    }
                    $stmt->execute();
                    $post = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $stmt->closeCursor();
                }

                if( empty($post) || empty($_GET['id']) )
                {
                    $output = '<div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                        <h4 class="modal-title">' . $this->lang['core']['classes']['template']['modal_notfound5'] . '</h4>
                                    </div>
                                </div>
                            </div>';
                }
                else
                {
                    $output = '<div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                        <h4 class="modal-title">' . $this->lang['core']['classes']['template']['modal_title5'] . '</i></h4>
                                    </div>
                                    <div class="modal-body">
                                        <form id="acceptfill" class="form-inline" action="#" onsubmit="return false;">
                                            <textarea class="form-control" name="body" rows="6" placeholder="' . $this->lang['core']['classes']['template']['modal_bodyhere'] . '">' . Security::sanitize($post[0]['body'], 'forumpost') . '</textarea>
                                            <p class="help-block">
                                                ' . $this->lang['core']['classes']['template']['modal_allowedhtml'] . '
                                            </p>

                                            <div class="hr"></div>

                                            <input type="hidden" name="pid" value="' . $post[0]['id'] . '">
                                            <input type="hidden" name="editpost" value="' . Security::csrfGenerate('editpost') . '">
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">' . $this->lang['core']['classes']['template']['modal_close'] . '</button>
                                        <button type="button" class="btn btn-primary" id="savechanges">' . $this->lang['core']['classes']['template']['modal_save5'] . '</button>
                                    </div>
                                </div>
                            </div>';
                }

                return $output;
                break;
        }
    }

    public function timezones()
    {
        return array(
            'Pacific/Midway'       => "(GMT-11:00) Midway Island",
            'US/Samoa'             => "(GMT-11:00) Samoa",
            'US/Hawaii'            => "(GMT-10:00) Hawaii",
            'US/Alaska'            => "(GMT-09:00) Alaska",
            'US/Pacific'           => "(GMT-08:00) Pacific Time (US &amp; Canada)",
            'America/Tijuana'      => "(GMT-08:00) Tijuana",
            'US/Arizona'           => "(GMT-07:00) Arizona",
            'US/Mountain'          => "(GMT-07:00) Mountain Time (US &amp; Canada)",
            'America/Chihuahua'    => "(GMT-07:00) Chihuahua",
            'America/Mazatlan'     => "(GMT-07:00) Mazatlan",
            'America/Mexico_City'  => "(GMT-06:00) Mexico City",
            'America/Monterrey'    => "(GMT-06:00) Monterrey",
            'Canada/Saskatchewan'  => "(GMT-06:00) Saskatchewan",
            'US/Central'           => "(GMT-06:00) Central Time (US &amp; Canada)",
            'US/Eastern'           => "(GMT-05:00) Eastern Time (US &amp; Canada)",
            'US/East-Indiana'      => "(GMT-05:00) Indiana (East)",
            'America/Bogota'       => "(GMT-05:00) Bogota",
            'America/Lima'         => "(GMT-05:00) Lima",
            'America/Caracas'      => "(GMT-04:30) Caracas",
            'Canada/Atlantic'      => "(GMT-04:00) Atlantic Time (Canada)",
            'America/La_Paz'       => "(GMT-04:00) La Paz",
            'America/Santiago'     => "(GMT-04:00) Santiago",
            'Canada/Newfoundland'  => "(GMT-03:30) Newfoundland",
            'America/Buenos_Aires' => "(GMT-03:00) Buenos Aires",
            'Greenland'            => "(GMT-03:00) Greenland",
            'Atlantic/Stanley'     => "(GMT-02:00) Stanley",
            'Atlantic/Azores'      => "(GMT-01:00) Azores",
            'Atlantic/Cape_Verde'  => "(GMT-01:00) Cape Verde Is.",
            'Africa/Casablanca'    => "(GMT) Casablanca",
            'Europe/Dublin'        => "(GMT) Dublin",
            'Europe/Lisbon'        => "(GMT) Lisbon",
            'Europe/London'        => "(GMT) London",
            'Africa/Monrovia'      => "(GMT) Monrovia",
            'Europe/Amsterdam'     => "(GMT+01:00) Amsterdam",
            'Europe/Belgrade'      => "(GMT+01:00) Belgrade",
            'Europe/Berlin'        => "(GMT+01:00) Berlin",
            'Europe/Bratislava'    => "(GMT+01:00) Bratislava",
            'Europe/Brussels'      => "(GMT+01:00) Brussels",
            'Europe/Budapest'      => "(GMT+01:00) Budapest",
            'Europe/Copenhagen'    => "(GMT+01:00) Copenhagen",
            'Europe/Ljubljana'     => "(GMT+01:00) Ljubljana",
            'Europe/Madrid'        => "(GMT+01:00) Madrid",
            'Europe/Paris'         => "(GMT+01:00) Paris",
            'Europe/Prague'        => "(GMT+01:00) Prague",
            'Europe/Rome'          => "(GMT+01:00) Rome",
            'Europe/Sarajevo'      => "(GMT+01:00) Sarajevo",
            'Europe/Skopje'        => "(GMT+01:00) Skopje",
            'Europe/Stockholm'     => "(GMT+01:00) Stockholm",
            'Europe/Vienna'        => "(GMT+01:00) Vienna",
            'Europe/Warsaw'        => "(GMT+01:00) Warsaw",
            'Europe/Zagreb'        => "(GMT+01:00) Zagreb",
            'Europe/Athens'        => "(GMT+02:00) Athens",
            'Europe/Bucharest'     => "(GMT+02:00) Bucharest",
            'Africa/Cairo'         => "(GMT+02:00) Cairo",
            'Africa/Harare'        => "(GMT+02:00) Harare",
            'Europe/Helsinki'      => "(GMT+02:00) Helsinki",
            'Europe/Istanbul'      => "(GMT+02:00) Istanbul",
            'Asia/Jerusalem'       => "(GMT+02:00) Jerusalem",
            'Europe/Kiev'          => "(GMT+02:00) Kyiv",
            'Europe/Minsk'         => "(GMT+02:00) Minsk",
            'Europe/Riga'          => "(GMT+02:00) Riga",
            'Europe/Sofia'         => "(GMT+02:00) Sofia",
            'Europe/Tallinn'       => "(GMT+02:00) Tallinn",
            'Europe/Vilnius'       => "(GMT+02:00) Vilnius",
            'Asia/Baghdad'         => "(GMT+03:00) Baghdad",
            'Asia/Kuwait'          => "(GMT+03:00) Kuwait",
            'Africa/Nairobi'       => "(GMT+03:00) Nairobi",
            'Asia/Riyadh'          => "(GMT+03:00) Riyadh",
            'Asia/Tehran'          => "(GMT+03:30) Tehran",
            'Europe/Moscow'        => "(GMT+04:00) Moscow",
            'Asia/Baku'            => "(GMT+04:00) Baku",
            'Europe/Volgograd'     => "(GMT+04:00) Volgograd",
            'Asia/Muscat'          => "(GMT+04:00) Muscat",
            'Asia/Tbilisi'         => "(GMT+04:00) Tbilisi",
            'Asia/Yerevan'         => "(GMT+04:00) Yerevan",
            'Asia/Kabul'           => "(GMT+04:30) Kabul",
            'Asia/Karachi'         => "(GMT+05:00) Karachi",
            'Asia/Tashkent'        => "(GMT+05:00) Tashkent",
            'Asia/Kolkata'         => "(GMT+05:30) Kolkata",
            'Asia/Kathmandu'       => "(GMT+05:45) Kathmandu",
            'Asia/Yekaterinburg'   => "(GMT+06:00) Ekaterinburg",
            'Asia/Almaty'          => "(GMT+06:00) Almaty",
            'Asia/Dhaka'           => "(GMT+06:00) Dhaka",
            'Asia/Novosibirsk'     => "(GMT+07:00) Novosibirsk",
            'Asia/Bangkok'         => "(GMT+07:00) Bangkok",
            'Asia/Jakarta'         => "(GMT+07:00) Jakarta",
            'Asia/Krasnoyarsk'     => "(GMT+08:00) Krasnoyarsk",
            'Asia/Chongqing'       => "(GMT+08:00) Chongqing",
            'Asia/Hong_Kong'       => "(GMT+08:00) Hong Kong",
            'Asia/Kuala_Lumpur'    => "(GMT+08:00) Kuala Lumpur",
            'Australia/Perth'      => "(GMT+08:00) Perth",
            'Asia/Singapore'       => "(GMT+08:00) Singapore",
            'Asia/Taipei'          => "(GMT+08:00) Taipei",
            'Asia/Ulaanbaatar'     => "(GMT+08:00) Ulaan Bataar",
            'Asia/Urumqi'          => "(GMT+08:00) Urumqi",
            'Asia/Irkutsk'         => "(GMT+09:00) Irkutsk",
            'Asia/Seoul'           => "(GMT+09:00) Seoul",
            'Asia/Tokyo'           => "(GMT+09:00) Tokyo",
            'Australia/Adelaide'   => "(GMT+09:30) Adelaide",
            'Australia/Darwin'     => "(GMT+09:30) Darwin",
            'Asia/Yakutsk'         => "(GMT+10:00) Yakutsk",
            'Australia/Brisbane'   => "(GMT+10:00) Brisbane",
            'Australia/Canberra'   => "(GMT+10:00) Canberra",
            'Pacific/Guam'         => "(GMT+10:00) Guam",
            'Australia/Hobart'     => "(GMT+10:00) Hobart",
            'Australia/Melbourne'  => "(GMT+10:00) Melbourne",
            'Pacific/Port_Moresby' => "(GMT+10:00) Port Moresby",
            'Australia/Sydney'     => "(GMT+10:00) Sydney",
            'Asia/Vladivostok'     => "(GMT+11:00) Vladivostok",
            'Asia/Magadan'         => "(GMT+12:00) Magadan",
            'Pacific/Auckland'     => "(GMT+12:00) Auckland",
            'Pacific/Fiji'         => "(GMT+12:00) Fiji",
        );
    }

    /**
     * Installs the framework, by adding the required data to the config and database.
     * @return json
     */
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
            $this->sql = $sql;
        }
        catch(PDOException $pe)
        {
            /* if not, ERROR! */
            return json_encode( array('status' => false, 'message' => 'Unable to connect to database.') );
        }
        
        /* Safe the settings to the config file. */ 
        $sitename       = Security::sanitize( $_POST['site_name'], 'purestring');
        $sitebaseurl    = Security::sanitize( $_POST['site_url'], 'purestring');
        $siteseourl     = ( !empty($_POST['site_seourl']) ? 1 : 0 );
        $siteemail      = Security::sanitize( $_POST['site_mail'], 'purestring');
        $sitetimezone   = Security::sanitize( $_POST['site_zone'], 'purestring');
        $sitelang       = Security::sanitize( $_POST['site_lang'], 'purestring');
        
        $sql_host       = Security::sanitize( $_POST['sqlhost'], 'purestring');
        $sql_port       = Security::sanitize( $_POST['sqlport'], 'integer');
        $sql_user       = Security::sanitize( $_POST['sqluser'], 'purestring');
        $sql_pass       = addslashes( $_POST['sqlpass']);
        $sql_db         = Security::sanitize( $_POST['sqldb'], 'purestring');
        
        $smtp_host      = Security::sanitize( ( !empty($_POST['smtp_host']) ? $_POST['smtp_host'] : '' ), 'purestring');
        $smtp_port      = Security::sanitize( ( !empty($_POST['smtp_port']) ? $_POST['smtp_port'] : '' ), 'integer');
        $smtp_mail      = Security::sanitize( ( !empty($_POST['smtp_user']) ? $_POST['smtp_user'] : '' ), 'purestring');
        $smtp_pass      = Security::sanitize( ( !empty($_POST['smtp_pass']) ? $_POST['smtp_pass'] : '' ), 'string');

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
        \'key\'=>\'' . Security::randomGenerator(128) . '\'
    ),
    \'cache\' => \'\'
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

        require_once( CORE_PATH . '/config.php' );
        $this->config = $config;
        unset($config);
        
        /* add the tables to the database. */
       $sql->exec("
        CREATE TABLE IF NOT EXISTS `forum_categories` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `title` varchar(100) NOT NULL,
          `description` varchar(255) NOT NULL,
          `sort` smallint(3) unsigned NOT NULL,
          `admin` enum('0','1') NOT NULL DEFAULT '0',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

        CREATE TABLE IF NOT EXISTS `forum_posts` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `body` text NOT NULL,
          `date` int(10) unsigned NOT NULL,
          `edit` int(10) unsigned NOT NULL,
          `editby` int(10) unsigned NOT NULL,
          `thread` int(10) unsigned NOT NULL,
          `uid` int(10) unsigned NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

        CREATE TABLE IF NOT EXISTS `forum_threads` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `title` varchar(255) NOT NULL,
          `uid` int(10) unsigned NOT NULL,
          `op` int(10) unsigned NOT NULL,
          `category` int(10) unsigned NOT NULL,
          `views` int(10) unsigned NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

        CREATE TABLE IF NOT EXISTS `groups` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `title` varchar(100) NOT NULL,
          `permissions` text NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `title` (`title`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

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
          `membergroup` int(10) unsigned NOT NULL,
          `reset_token` char(64) NOT NULL,
          `reset_time` int(11) NOT NULL,
          `active_key` char(32) NOT NULL,
          `session_id` char(64) NOT NULL,
          `acc_key` char(12) NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `username` (`username`),
          UNIQUE KEY `email_hash` (`email_hash`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

        INSERT INTO `groups` (`id`, `title`, `permissions`) VALUES
        (1, 'Member', ''),
        (2, 'Admin', '{\"admin\":1}');");

        // Insert the user settings into the database
        $stmt = $sql->prepare('INSERT INTO 
                                        settings (`key`, `value`, `title`, `type`, `options`)
                                    VALUES 
                                        ("site_name", ?, "Site Name", "text", ""),
                                        ("site_email", ?, "Site Email", "text", ""),
                                        ("default_group", 1, "Default Group (id)", "text", ""),
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
            json_encode( array_flip( $this->timezones() ) )
        ));
        $stmt->closeCursor();
        
        // Test the user table where indeed added.
        $test = @$sql->query('SELECT * FROM groups');
        $success = @$test->execute();
        @$test->closeCursor();

        if( !$success )
        {
            return json_encode( array('status'=>false, 'message'=>'The nessesary tables does not appear to have been created in the database.') );
        }
        
        // try to delete the install file, just in case.
        if( file_exists(CORE_PATH . '/templates/install.php') )
        {
            unlink( CORE_PATH . '/templates/install.php');
        }

        $admincreated = $this->register(true);

        if( !$admincreated )
        {
            unlink(CORE_PATH . '/config.php');
            return json_encode( array('status'=>false, 'message'=>$_SESSION['notifications']['register_1']) );
        }
        
        return json_encode( array('status'=>true, 'message'=>'Installation completed! Redirecting you to your new website in < 3 seconds.', 'redirect' => $sitebaseurl) );
    }
}