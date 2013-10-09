<?php
/**
 *  Zolid Framework - MIT licensed
 *  https://github.com/MrEliasen/Zolid-Framework
 *
 *  A class containing all sorts of data used throughout the system
 *
 *  @author     Mark Eliasen
 *  @website    www.zolidsolutions.com
 *  @copyright  (c) 2013 - Mark Eliasen
 *  @version    0.1.5
 */

class Admin extends User
{


    /**
     * Will return the HTML for the requested admin modal.
     * @param  string $modal
     * @return html
     */
    protected function getAdminModal( $modal )
    {
        if( !$this->permission('admin') )
        {
            return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['modal_err1']));
        }

        switch( $modal )
        {
            case 'editaccount':
                if( !empty($_GET['id']) )
                {
                    $stmt = $this->sql->prepare('SELECT users.id as uid, username, email, groups.id as gid, groups.title, active_key FROM users LEFT JOIN groups ON groups.id = users.membergroup WHERE users.id = :uid LIMIT 1');
                    $stmt->bindValue(':uid', Security::sanitize($_GET['id'], 'integer'), PDO::PARAM_INT);
                    $stmt->execute();
                    $user = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $stmt->closeCursor();
                }

                if( empty($user) || empty($_GET['id']) )
                {
                    $output = '<div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                        <h4 class="modal-title">' . $this->lang['core']['classes']['admin']['modal_usrnotfound'] . '</h4>
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
                                        <h4 class="modal-title">' . $this->lang['core']['classes']['admin']['modal_edittitle'] . '</h4>
                                    </div>
                                    <div class="modal-body">
                                        <form class="form-horizontal" action="#" onsubmit="return false;">
                                            <div class="form-group">
                                                <label class="col-lg-4 control-label">' . $this->lang['core']['classes']['admin']['modal_username'] . '</label>
                                                <div class="col-lg-8">
                                                    <input type="text" name="editacc_username" class="form-control" placeholder="' . $this->lang['core']['classes']['admin']['modal_username'] . '" value="' . $user[0]['username'] . '">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-lg-4 control-label">' . $this->lang['core']['classes']['admin']['modal_email'] . '</label>
                                                <div class="col-lg-8">
                                                    <input type="text" name="editacc_email" class="form-control" placeholder="' . $this->lang['core']['classes']['admin']['modal_email'] . '" value="' . $this->decryptData($user[0]['email']) . '">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-lg-4 control-label">' . $this->lang['core']['classes']['admin']['modal_usergroup'] . '</label>
                                                <div class="col-lg-8">
                                                    <select class="form-control" name="editacc_group">';
                                                    
                                                    foreach( $this->sql->query('SELECT * FROM groups') as $group )
                                                    {
                                                        $output .= '<option value="' . $group['id'] . '" ' . ( $group['id'] == $user[0]['gid'] ? 'selected="selected"' : '' ) . '>' . $group['title'] . '</option>';
                                                    }

                                        $output .= '</select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="inputPassword" class="col-lg-4 control-label">' . $this->lang['core']['classes']['admin']['modal_changepass'] . '</label>
                                                <div class="col-lg-8">
                                                    <input type="password" name="editacc_newpass" class="form-control" placeholder="' . $this->lang['core']['classes']['admin']['modal_newpass'] . '">
                                                    <p class="help-block">' . $this->lang['core']['classes']['admin']['modal_help1'] . '</p>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="inputPassword" class="col-lg-4 control-label">' . $this->lang['core']['classes']['admin']['modal_accstatus'] . '</label>
                                                <div class="col-lg-8">
                                                    <p class="form-control-static">';
                                                            
                                                    if( !empty($user[0]['active_key']) )
                                                    {
                                                        $output .= '<span class="label label-warning">' . $this->lang['core']['classes']['admin']['modal_statusinactive'] . '</span>';
                                                    }
                                                    else
                                                    {
                                                        $output .= '<span class="label label-success">' . $this->lang['core']['classes']['admin']['modal_statusactive'] . '</span>';
                                                    }

                                        $output .= '</p>
                                                </div>
                                            </div>';

                                        if( !empty($user[0]['active_key']) )
                                        {
                                            $output .= '<div class="form-group">
                                                            <label for="inputPassword" class="col-lg-4 control-label">' . $this->lang['core']['classes']['admin']['modal_activateacc'] . '</label>
                                                            <div class="col-lg-8">
                                                                <div class="checkbox">
                                                                    <label>
                                                                        <input type="checkbox"  name="editacc_activate"> ' . $this->lang['core']['classes']['admin']['modal_check1'] . '
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>';
                                        }

                                $output .= '<input type="hidden" name="editacc_uid" value="' . $user[0]['uid'] . '">
                                            <input type="hidden" name="editaccount" value="' . Security::csrfGenerate('editaccount') . '">
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">' . $this->lang['core']['classes']['admin']['modal_close'] . '</button>
                                        <button type="button" class="btn btn-primary" id="savechanges">' . $this->lang['core']['classes']['admin']['modal_save'] . '</button>
                                    </div>
                                </div>
                            </div>';
                }
                return $output;
                break;

            case 'editgroup':
            case 'addgroup':
                if( !empty($_GET['id']) )
                {
                    $stmt = $this->sql->prepare('SELECT * FROM groups WHERE id = :gid LIMIT 1');
                    $stmt->bindValue(':gid', Security::sanitize($_GET['id'], 'integer'), PDO::PARAM_INT);
                    $stmt->execute();
                    $group = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $stmt->closeCursor();
                }

                if( ( empty($group) || empty($_GET['id']) ) && $modal !== 'addgroup' )
                {
                    $output = '<div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                        <h4 class="modal-title">' . $this->lang['core']['classes']['admin']['modal_err2'] . '</h4>
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
                                        <h4 class="modal-title">' . $this->lang['core']['classes']['admin']['modal_editgrptitle'] . '</h4>
                                    </div>
                                    <div class="modal-body">
                                        <form class="form-horizontal" action="#" onsubmit="return false;">
                                            <div class="form-group">
                                                <label class="col-lg-4 control-label">' . $this->lang['core']['classes']['admin']['modal_grptitle'] . '</label>
                                                <div class="col-lg-8">
                                                    <input type="text" name="editgroup_title" class="form-control" placeholder="' . $this->lang['core']['classes']['admin']['modal_grptitle2'] . '" value="' . ( !empty($group[0]['title']) ? $group[0]['title'] : '' ) . '">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-lg-4 control-label">' . $this->lang['core']['classes']['admin']['modal_grpperms'] . '</label>
                                                <div class="col-lg-8">
                                                    <textarea name="editgroup_perms" class="form-control" rows="3">' . ( !empty($group[0]['permissions']) ? $group[0]['permissions'] : '' ) . '</textarea>
                                                </div>
                                            </div>
                                            <input type="hidden" name="editgroup_gid" value="' . ( !empty($group[0]['id']) ? $group[0]['id'] : '' ) . '">
                                            <input type="hidden" name="editgroup" value="' . Security::csrfGenerate('editgroup') . '">
                                            ' . ( $modal == 'addgroup' ? '<input type="hidden" name="newgroup" value="1">' : '' ) . '
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">' . $this->lang['core']['classes']['admin']['modal_close'] . '</button>
                                        <button type="button" class="btn btn-primary" id="savechanges">' . ( $modal == 'addgroup' ? $this->lang['core']['classes']['admin']['modal_grpadd'] : $this->lang['core']['classes']['admin']['modal_grpsave'] ) . '</button>
                                    </div>
                                </div>
                            </div>';
                }
                return $output;
                break;

            case 'addforumcat':
                return '<div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                    <h4 class="modal-title">' . $this->lang['core']['classes']['admin']['modal_forumaddtitle'] . '</h4>
                                </div>
                                <div class="modal-body">
                                    <form class="form-horizontal" action="#" onsubmit="return false;">
                                        <div class="form-group">
                                            <label class="col-lg-4 control-label">' . $this->lang['core']['classes']['admin']['modal_forumaddctitle'] . '</label>
                                            <div class="col-lg-8">
                                                <input type="text" name="addforum_title" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-lg-4 control-label">' . $this->lang['core']['classes']['admin']['modal_forumaddclabel'] . '</label>
                                            <div class="col-lg-8">
                                                <input type="text" name="addforum_desc" class="form-control" placeholder="' . $this->lang['core']['classes']['admin']['modal_forumaddcdesc'] . '">
                                            </div>
                                        </div>
                                        <input type="hidden" name="addforum" value="' . Security::csrfGenerate('addforum') . '">
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">' . $this->lang['core']['classes']['admin']['modal_close'] . '</button>
                                    <button type="button" class="btn btn-primary" id="savechanges">' . $this->lang['core']['classes']['admin']['modal_forumaddsave'] . '</button>
                                </div>
                            </div>
                        </div>';
                break;

            case 'editforumcategory':
                if( !empty($_GET['id']) )
                {
                    $stmt = $this->sql->prepare('SELECT * FROM forum_categories WHERE id = :cid LIMIT 1');
                    $stmt->bindValue(':cid', Security::sanitize($_GET['id'], 'integer'), PDO::PARAM_INT);
                    $stmt->execute();
                    $category = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $stmt->closeCursor();
                }

                if( ( empty($category) || empty($_GET['id']) ) && $modal !== 'addgroup' )
                {
                    $output = '<div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                        <h4 class="modal-title">' . $this->lang['core']['classes']['admin']['modal_err3'] . '</h4>
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
                                            <h4 class="modal-title">' . $this->lang['core']['classes']['admin']['modal_forumaddcattitle'] . '</h4>
                                        </div>
                                        <div class="modal-body">
                                            <form class="form-horizontal" action="#" onsubmit="return false;">
                                                <div class="form-group">
                                                    <label class="col-lg-4 control-label">' . $this->lang['core']['classes']['admin']['modal_forumaddcatctitle'] . '</label>
                                                    <div class="col-lg-8">
                                                        <input type="text" name="addforum_title" class="form-control" value="' . $category[0]['title'] . '">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-lg-4 control-label">' . $this->lang['core']['classes']['admin']['modal_forumaddcatcdesc'] . '</label>
                                                    <div class="col-lg-8">
                                                        <input type="text" name="addforum_desc" class="form-control" placeholder="' . $this->lang['core']['classes']['admin']['modal_forumaddcatcdeschelp'] . '" value="' . $category[0]['description'] . '">
                                                    </div>
                                                </div>
                                                <input type="hidden" name="cid" value="' .$category[0]['id'] . '">
                                                <input type="hidden" name="editforum" value="' . Security::csrfGenerate('editforum') . '">
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">' . $this->lang['core']['classes']['admin']['modal_close'] . '</button>
                                            <button type="button" class="btn btn-primary" id="savechanges">' . $this->lang['core']['classes']['admin']['modal_forumaddcatsave'] . '</button>
                                        </div>
                                    </div>
                                </div>';
                }
                return $output;
                break;
        }
    }
    
	/**
	 * Will save any changes to the specified user group.
	 * @return json
	 */
	protected function adminSaveGroupChanges()
	{
		if( !$this->permission('admin') )
		{
			return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['savegroup_err1']));
		}

		if( !Security::csrfCheck('editgroup', true) )
		{
			return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['savegroup_err2']));
		}

		if( empty($_POST['editgroup_gid']) && empty($_POST['newgroup']))
		{
			return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['savegroup_err3']));
		}

		if( empty($_POST['editgroup_title']) )
		{
			return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['savegroup_err4']));
		}

		$gperms = '';
		if( !empty($_POST['editgroup_perms']) )
		{
			$gperms = $_POST['editgroup_perms'];
		}

		if( empty($_POST['newgroup']) )
		{
			$stmt = $this->sql->prepare('UPDATE 
												`groups` 
											SET 
												`title` = :gtitle,
												`permissions` = :gperms
											WHERE
												`id` = :gid');

			$stmt->bindValue(':gid', Security::sanitize($_POST['editgroup_gid'], 'integer'), PDO::PARAM_INT);
			$stmt->bindValue(':gtitle', Security::sanitize($_POST['editgroup_title'], 'string'), PDO::PARAM_STR);
			$stmt->bindValue(':gperms', Security::sanitize($gperms, 'string'), PDO::PARAM_STR);
			$success = $stmt->execute();
			$stmt->closeCursor();

			if( $success )
			{
				return json_encode(array('status'=>true, 'message'=>$this->lang['core']['classes']['admin']['savegroup_success1'] ) );
			}
		}
		else
		{
			$stmt = $this->sql->prepare('INSERT INTO 
												`groups` (`title`, `permissions`)
											VALUES
												(:gtitle, :gperms)');

			$stmt->bindValue(':gtitle', Security::sanitize($_POST['editgroup_title'], 'string'), PDO::PARAM_STR);
			$stmt->bindValue(':gperms', Security::sanitize($gperms, 'string'), PDO::PARAM_STR);
			$success = $stmt->execute();
			$stmt->closeCursor();

			if( $success )
			{
				$newid = $this->sql->lastInsertId();
				return json_encode(array(
					'status'=>true,
					'message'=> $this->lang['core']['classes']['admin']['savegroup_success1'],
					'add' => '<tr id="row_g' . $newid . '">
                                <td>' . $newid . '</td>
                                <td>' . Security::sanitize($_POST['editgroup_title'], 'string') . '</td>
                                <td class="text-center">
                                    <button class="btn btn-xs btn-primary" data-action="savegroupchanges" data-toggle="modal" data-loadmodal="editgroup" data-id="' . $newid . '"><i class="glyphicon glyphicon-edit"></i></button>
                                    <button class="btn btn-xs btn-danger" data-placement="left" data-toggle="popover" data-title="' . $this->lang['core']['classes']['admin']['savegroup_html1'] . '" data-content="<button class=\'btn btn-danger btn-xs funcdelete\' data-id=\'' . $newid . '\' data-action=\'deletegroup\' data-target=\'g' . $newid . '\'>' . $this->lang['core']['classes']['admin']['savegroup_html2'] . '</button> <button class=\'btn btn-xs btn-info closepo\'>' . $this->lang['core']['classes']['admin']['savegroup_html3'] . '</button>"><i class="glyphicon glyphicon-trash"></i></button>
                                </td>
                            </tr>'
                ));
			}
		}

		Security::csrfCheck('editgroup'); //clear the token
		return json_encode(array('status'=>true, 'message'=>$this->lang['core']['classes']['admin']['savegroup_err5']));
	}

	/**
	 * Save all account changes for the specified account.
	 * @return json
	 */
	protected function adminSaveAccChanges()
	{
		if( !$this->permission('admin') )
		{
			return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['saveacc_err1']));
		}

		if( !Security::csrfCheck('editaccount', true) )
		{
			return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['saveacc_err2']));
		}

		if( empty($_POST['editacc_uid']) )
		{
			return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['saveacc_err3']));
		}

		if( empty($_POST['editacc_username']) )
		{
			return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['saveacc_err4']));
		}

		if( !$this->validateEmail($_POST['editacc_email']) )
		{
			return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['saveacc_err5']));
		}

		if( empty($_POST['editacc_group']) )
		{
			return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['saveacc_err6']));
		}

		$passquery = '';
		if( !empty($_POST['editacc_newpass']) )
		{
			$passquery = ', password = :newpass';
		}

		$activatequery = '';
		if( !empty($_POST['editacc_activate']) )
		{
			$activatequery = ', active_key = ""';
		}

		$stmt = $this->sql->prepare('UPDATE 
											`users` 
										SET 
											`username` = :usrname,
											`email` = :usremail,
											`membergroup` = :ugroup,
											`email_hash` = :emailhash
											' . $passquery . $activatequery . '
										WHERE
											`id` = :uid');

		$stmt->bindValue(':uid', Security::sanitize($_POST['editacc_uid'], 'integer'), PDO::PARAM_INT);
		$stmt->bindValue(':usrname', Security::sanitize($_POST['editacc_username'], 'string'), PDO::PARAM_STR);
		$stmt->bindValue(':ugroup', Security::sanitize($_POST['editacc_group'], 'integer'), PDO::PARAM_INT);
		$stmt->bindValue(':usremail', $this->encryptData(Security::sanitize(strtolower($_POST['editacc_email']), 'email')), PDO::PARAM_STR);
		$stmt->bindValue(':emailhash', hash_hmac('sha512', $this->config['global_salt'] . strtolower($_POST['editacc_email']), $this->config['global_key'] ), PDO::PARAM_STR);

		if( !empty($passquery) )
		{
			$stmt->bindValue(':newpass', hash_hmac('sha512', $this->config['global_salt'] . $_POST['editacc_newpass'], $this->config['global_key'] ), PDO::PARAM_STR);
		}

		$success = $stmt->execute();
		$stmt->closeCursor();

		if( $success )
		{
			Security::csrfCheck('editaccount'); // Clear the token
			return json_encode(array('status'=>true, 'message'=>$this->lang['core']['classes']['admin']['saveacc_success']));
		}
		else
		{
			return json_encode(array('status'=>true, 'message'=>$this->lang['core']['classes']['admin']['saveacc_err7']));
		}
	}

	/**
	 * Deletes the specified account
	 * @return json
	 */
	protected function adminDeleteAccount()
	{
		if( !$this->permission('admin') )
		{
			return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['delacc_err1']));
		}

		if( !Security::csrfCheck('usertoken', true) )
		{
			return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['delacc_err2']));
		}

		if( empty($_POST['id']) )
		{
			return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['delacc_err3']));
		}

		if( $_POST['id'] == $_SESSION['data']['uid'] )
		{
			return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['delacc_err4']));
		}

		$stmt = $this->sql->prepare('DELETE FROM users WHERE id = :uid');
		$stmt->bindValue(':uid', Security::sanitize($_POST['id'], 'integer'), PDO::PARAM_INT);
		$success = $stmt->execute();
		$stmt->closeCursor();

		if( $success )
		{
			return json_encode(array('status'=>true, 'message'=>$this->lang['core']['classes']['admin']['delacc_success']));
		}
		else
		{
			return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['delacc_err5']));
		}
	}

	/**
	 * Deletes the specified group
	 * @return json
	 */
	protected function adminDeleteGroup()
	{
		if( !$this->permission('admin') )
		{
			return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['delgrp_err1']));
		}

		if( !Security::csrfCheck('usertoken', true) )
		{
			return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['delgrp_err2']));
		}

		if( empty($_POST['id']) )
		{
			return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['delgrp_err3']));
		}

		if( $_POST['id'] == $_SESSION['data']['groupid'] )
		{
			return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['delgrp_err4']));
		}

		$stmt = $this->sql->prepare('DELETE FROM groups WHERE id = :uid');
		$stmt->bindValue(':uid', Security::sanitize($_POST['id'], 'integer'), PDO::PARAM_INT);
		$success = $stmt->execute();
		$stmt->closeCursor();

		if( $success )
		{
			return json_encode(array('status'=>true, 'message'=>$this->lang['core']['classes']['admin']['delgrp_success']));
		}
		else
		{
			return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['delgrp_err5']));
		}
	}

	/**
	 * Deletes the specified forum category
	 * @return json
	 */
	protected function adminDeleteForumCategory()
	{
		if( !$this->permission('admin') )
		{
			return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['delforumcat_err1']));
		}

		if( !Security::csrfCheck('usertoken', true) )
		{
			return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['delforumcat_err2']));
		}

		if( empty($_POST['id']) )
		{
			return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['delforumcat_err3']));
		}

		$stmt = $this->sql->prepare('DELETE FROM forum_categories WHERE id = :cid');
		$stmt->bindValue(':cid', Security::sanitize($_POST['id'], 'integer'), PDO::PARAM_INT);
		$success = $stmt->execute();
		$stmt->closeCursor();

		$stmt = $this->sql->prepare('DELETE FROM forum_posts WHERE thread IN (SELECT id FROM forum_threads WHERE category = :cid)');
		$stmt->bindValue(':cid', Security::sanitize($_POST['id'], 'integer'), PDO::PARAM_INT);
		$stmt->execute();
		$stmt->closeCursor();

		$stmt = $this->sql->prepare('DELETE FROM forum_threads WHERE category = :cid');
		$stmt->bindValue(':cid', Security::sanitize($_POST['id'], 'integer'), PDO::PARAM_INT);
		$stmt->execute();
		$stmt->closeCursor();

		if( $success )
		{
			return json_encode(array('status'=>true, 'message'=>$this->lang['core']['classes']['admin']['delforumcat_success']));
		}
		else
		{
			return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['delforumcat_err4']));
		}
	}


	/**
	 * Adds forum category to the database
	 * @return json
	 */
	protected function adminAddNewForum()
	{
		if( !$this->permission('admin') )
		{
			return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['addforum_err1']));
		}

		if( !Security::csrfCheck('addforum', true) )
		{
			return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['addforum_err2']));
		}

		if( empty($_POST['addforum_title']) )
		{
			return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['addforum_err3']));
		}

		$stmt = $this->sql->prepare('INSERT INTO forum_categories (`title`, `description`) VALUES (:title, :desc)');
		$stmt->bindValue(':title', Security::sanitize($_POST['addforum_title'], 'purestring'), PDO::PARAM_STR);
		$stmt->bindValue(':desc', Security::sanitize($_POST['addforum_desc'], 'string'), PDO::PARAM_STR);
		$success = $stmt->execute();
		$stmt->closeCursor();

		if( $success )
		{
			$newid = $this->sql->lastInsertId();
			Security::csrfCheck('addforum'); // Clear the token
			return json_encode(array(
				'status'=>true,
				'message'=>$this->lang['core']['classes']['admin']['addforum_success'],
				'addto'=>'.dd-list',
				'add' => '<li id="row_fc' . $newid . '" class="dd-item dd3-item" data-id="' . $newid . '">
                                <div class="dd-handle dd3-handle">Drag</div>
                                <div class="dd3-content">
                                    ' . Security::sanitize($_POST['addforum_title'], 'purestring') . '
                                    <button onclick="return false;" class="pull-right btn btn-xs btn-danger" data-placement="left" data-toggle="popover" data-title="' . $this->lang['core']['classes']['admin']['addforum_html1'] . '" data-content="<button onclick=\'return false;\' class=\'btn btn-danger btn-xs funcdelete\' data-id=\'' . $newid . '\' data-action=\'deleteforumcategory\' data-target=\'fc' . $newid . '\'>' . $this->lang['core']['classes']['admin']['addforum_html2'] . '</button> <button onclick=\'return false;\' class=\'btn btn-xs btn-info closepo\'>' . $this->lang['core']['classes']['admin']['addforum_html3'] . '</button>"><i class="glyphicon glyphicon-trash"></i></button>
                                    <button class="pull-right btn btn-xs btn-primary" data-action="updatecategory" data-toggle="modal" data-loadmodal="editforumcategory" data-id="' . $newid . '"><i class="glyphicon glyphicon-edit"></i></button>
                                </div>
                            </li>'));
		}
		else
		{
			return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['addforum_err4']));
		}
	}


    /**
     * Edits the specified forum category
     * @return json
     */
    protected function adminEditCategory()
    {
        if( !$this->permission('admin') )
        {
            return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['editforumcat_err1']));
        }

        if( empty($_POST['cid']) )
        {
            return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['editforumcat_err2']));
        }

        if( !Security::csrfCheck('editforum', true) )
        {
            return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['editforumcat_err3']));
        }

        if( empty($_POST['addforum_title']) )
        {
            return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['editforumcat_err4']));
        }

        $stmt = $this->sql->prepare('UPDATE forum_categories SET `title` = :title, `description` = :desc WHERE id = :cid');
        $stmt->bindValue(':title', Security::sanitize($_POST['addforum_title'], 'purestring'), PDO::PARAM_STR);
        $stmt->bindValue(':desc', Security::sanitize($_POST['addforum_desc'], 'string'), PDO::PARAM_STR);
        $stmt->bindValue(':cid', Security::sanitize($_POST['cid'], 'integer'), PDO::PARAM_INT);
        $success = $stmt->execute();
        $stmt->closeCursor();

        if( $success )
        {
            $newid = $this->sql->lastInsertId();
            Security::csrfCheck('addforum'); // Clear the token
            return json_encode(array(
                'status'=>true,
                'message'=>$this->lang['core']['classes']['admin']['editforumcat_success'],
                'sendto'=>'force'
            ));
        }
        else
        {
            return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['editforumcat_err5']));
        }
    }

    /**
     * Save the framework settings to the database.
     * @return [type]
     */
    protected function saveSettings()
    {
    	if( !$this->permission('admin') )
		{
			return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['savesetting_err1']));
		}

		if( !Security::csrfCheck('usertoken', true) )
		{
			return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['savesetting_err2']));
		}

		if( empty($_POST['setting']) || !is_array($_POST['setting']) )
		{
			return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['savesetting_err3']));
		}

		$placeholders = '';
		$data = array();
		foreach($_POST['setting'] as $key => $setting )
		{
			$placeholders .= ( !empty($placeholders) ? ',' : '' ) . '(?, ?)';
			$data[] = $key;
			$data[] = $setting;
		}

		$stmt =  $this->sql->prepare('INSERT INTO 
        										settings (`key`, `value`)
		        							VALUES 
		        								' . $placeholders . '
		        							ON 
		        								DUPLICATE KEY 
		        							UPDATE 
		        								`value` = VALUES(`value`)');
		$success = $stmt->execute($data);
		$stmt->closeCursor();

		if( $success )
		{
			// Clear the settings cache file
			if( file_exists(CORE_PATH . '/cache/framework_settings.cache') )
			{
				unlink(CORE_PATH . '/cache/framework_settings.cache');
			}

			return json_encode(array('status'=>true, 'message'=>$this->lang['core']['classes']['admin']['savesetting_success']));
		}
		else
		{
			return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['savesetting_err4']));
		}
	}


    /**
     * Updates the display order of the forum categories
     * @return json
     */
    protected function adminUpdateForumOrder()
    {
        if( !$this->permission('admin') )
        {
            return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['saveforumorder_err1']));
        }

        if( !Security::csrfCheck('usertoken', true) )
        {
            return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['saveforumorder_err2']));
        }

        if( empty($_POST['neworder']) )
        {
            return json_encode(array('status'=>false, 'message'=>$this->lang['core']['classes']['admin']['saveforumorder_err3']));
        }

        $order = json_decode(Security::sanitize($_POST['neworder'], 'string'), true);

        $stmt = $this->sql->prepare('UPDATE forum_categories SET sort = ? WHERE id = ?');

        foreach( $order as $sort => $cat )
        {
            $stmt->execute(array(
                $sort,
                $cat['id']
            ));
        }

        $stmt->closeCursor();

        return json_encode(array('status'=>true, 'message'=>''));
    }
}