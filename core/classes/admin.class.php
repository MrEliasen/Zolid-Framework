<?php

class Admin extends User
{
	protected function adminSaveGroupChanges()
	{
		if( !$this->permission('admin') )
		{
			return json_encode(array('status'=>false, 'message'=>'You do not have permission to do this'));
		}

		if( !Security::csrfCheck('editgroup') )
		{
			return json_encode(array('status'=>false, 'message'=>'CSRF token was invalid, please reload the form.'));
		}

		if( empty($_POST['editgroup_gid']) && empty($_POST['newgroup']))
		{
			return json_encode(array('status'=>false, 'message'=>'Group not found.'));
		}

		if( empty($_POST['editgroup_title']) )
		{
			return json_encode(array('status'=>false, 'message'=>'Group title cannot be blank.'));
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
				return json_encode(array('status'=>true, 'message'=> 'Changes saved!' ) );
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
					'message'=> 'Group Added!',
					'add' => '<tr id="row_g' . $newid . '">
                                <td>' . $newid . '</td>
                                <td>' . Security::sanitize($_POST['editgroup_title'], 'string') . '</td>
                                <td class="text-center">
                                    <button class="btn btn-xs btn-primary" data-action="savegroupchanges" data-toggle="modal" data-loadmodal="editgroup" data-id="' . $newid . '"><i class="glyphicon glyphicon-edit"></i></button>
                                    <button class="btn btn-xs btn-danger" data-placement="left" data-toggle="popover" data-title="<b>Are you sure?</b>" data-content="<button class=\'btn btn-danger btn-xs funcdelete\' data-id=\'' . $newid . '\' data-action=\'deletegroup\' data-target=\'g' . $newid . '\'>Yes I\'m sure</button> <button class=\'btn btn-xs btn-info closepo\'>No</button>"><i class="glyphicon glyphicon-trash"></i></button>
                                </td>
                            </tr>'
                ));
			}
		}

		return json_encode(array('status'=>true, 'message'=>'Unable to save changes, make sure the title is not already in use.'));
	}

	protected function adminSaveAccChanges()
	{
		if( !$this->permission('admin') )
		{
			return json_encode(array('status'=>false, 'message'=>'You do not have permission to do this'));
		}

		if( !Security::csrfCheck('editaccount') )
		{
			return json_encode(array('status'=>false, 'message'=>'CSRF token was invalid, please reload the form.'));
		}

		if( empty($_POST['editacc_uid']) )
		{
			return json_encode(array('status'=>false, 'message'=>'User not found.'));
		}

		if( empty($_POST['editacc_username']) )
		{
			return json_encode(array('status'=>false, 'message'=>'Username cannot be blank.'));
		}

		if( !$this->validateEmail($_POST['editacc_email']) )
		{
			return json_encode(array('status'=>false, 'message'=>'The email is invalid.'));
		}

		if( empty($_POST['editacc_group']) )
		{
			return json_encode(array('status'=>false, 'message'=>'User group cannot be left blank.'));
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
											`group` = :ugroup,
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
			return json_encode(array('status'=>true, 'message'=>'Changes saved!'));
		}
		else
		{
			return json_encode(array('status'=>true, 'message'=>'Unable to save changes, make sure the username and email is not already in use.'));
		}
	}

	protected function adminDeleteAccount()
	{
		if( !$this->permission('admin') )
		{
			return json_encode(array('status'=>false, 'message'=>'You do not have permission to do this'));
		}

		if( !Security::csrfCheck('usertoken', true) )
		{
			return json_encode(array('status'=>false, 'message'=>'CSRF token was invalid, please reload the form.'));
		}

		if( empty($_POST['id']) )
		{
			return json_encode(array('status'=>false, 'message'=>'User not found.'));
		}

		if( $_POST['id'] == $_SESSION['data']['uid'] )
		{
			return json_encode(array('status'=>false, 'message'=>'You cannot delete your own account.'));
		}

		$stmt = $this->sql->prepare('DELETE FROM users WHERE id = :uid');
		$stmt->bindValue(':uid', Security::sanitize($_POST['id'], 'integer'), PDO::PARAM_INT);
		$success = $stmt->execute();
		$stmt->closeCursor();

		if( $success )
		{
			return json_encode(array('status'=>true, 'message'=>'Account was deleted.'));
		}
		else
		{
			return json_encode(array('status'=>false, 'message'=>'Unable to delete account, maybe it has already been removed?'));
		}
	}

	protected function adminDeleteGroup()
	{
		if( !$this->permission('admin') )
		{
			return json_encode(array('status'=>false, 'message'=>'You do not have permission to do this'));
		}

		if( !Security::csrfCheck('usertoken', true) )
		{
			return json_encode(array('status'=>false, 'message'=>'CSRF token was invalid, please reload the form.'));
		}

		if( empty($_POST['id']) )
		{
			return json_encode(array('status'=>false, 'message'=>'Group not found.'));
		}

		if( $_POST['id'] == $_SESSION['data']['groupid'] )
		{
			return json_encode(array('status'=>false, 'message'=>'You cannot delete your own group.'));
		}

		$stmt = $this->sql->prepare('DELETE FROM groups WHERE id = :uid');
		$stmt->bindValue(':uid', Security::sanitize($_POST['id'], 'integer'), PDO::PARAM_INT);
		$success = $stmt->execute();
		$stmt->closeCursor();

		if( $success )
		{
			return json_encode(array('status'=>true, 'message'=>'Group was deleted.'));
		}
		else
		{
			return json_encode(array('status'=>false, 'message'=>'Unable to delete group, maybe it has already been removed?'));
		}
	}

	protected function getAdminModal( $modal )
    {
    	if( !$this->permission('admin') )
		{
			return json_encode(array('status'=>false, 'message'=>'You do not have permission to do this'));
		}

        switch( $modal )
        {
        	case 'editaccount':
        		if( !empty($_GET['id']) )
        		{
	        		$stmt = $this->sql->prepare('SELECT users.id as uid, username, email, groups.id as gid, groups.title, active_key FROM users LEFT JOIN groups ON groups.id = users.group WHERE users.id = :uid LIMIT 1');
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
										<h4 class="modal-title">User Not Found</h4>
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
										<h4 class="modal-title">Edit Account</h4>
									</div>
									<div class="modal-body">
										<form class="form-horizontal" action="#" onsubmit="return false;">
											<div class="form-group">
												<label class="col-lg-4 control-label">Username</label>
												<div class="col-lg-8">
													<input type="text" name="editacc_username" class="form-control" placeholder="Username" value="' . $user[0]['username'] . '">
												</div>
											</div>
											<div class="form-group">
												<label class="col-lg-4 control-label">Email</label>
												<div class="col-lg-8">
													<input type="text" name="editacc_email" class="form-control" placeholder="Email" value="' . $this->decryptData($user[0]['email']) . '">
												</div>
											</div>
											<div class="form-group">
												<label class="col-lg-4 control-label">User Group</label>
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
												<label for="inputPassword" class="col-lg-4 control-label">Change Password</label>
												<div class="col-lg-8">
													<input type="password" name="editacc_newpass" class="form-control" placeholder="New password">
													<p class="help-block">Leave the password blank to keep current password.</p>
												</div>
											</div>
											<div class="form-group">
												<label for="inputPassword" class="col-lg-4 control-label">Account Status</label>
												<div class="col-lg-8">
													<p class="form-control-static">';
														  	
												  	if( !empty($user[0]['active_key']) )
												  	{
												  		$output .= '<span class="label label-warning">INACTIVE</span>';
												  	}
												  	else
												  	{
												  		$output .= '<span class="label label-success">ACTIVE</span>';
												  	}

										$output .= '</p>
												</div>
											</div>';

										if( !empty($user[0]['active_key']) )
										{
											$output .= '<div class="form-group">
															<label for="inputPassword" class="col-lg-4 control-label">Activate Account</label>
															<div class="col-lg-8">
																<div class="checkbox">
																	<label>
																		<input type="checkbox"  name="editacc_activate"> Ignore email verification and activate account now?
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
								        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	   									<button type="button" class="btn btn-primary" id="saveaccchanges">Save changes</button>
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
										<h4 class="modal-title">Group Not Found</h4>
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
										<h4 class="modal-title">Edit Group</h4>
									</div>
									<div class="modal-body">
										<form class="form-horizontal" action="#" onsubmit="return false;">
											<div class="form-group">
												<label class="col-lg-4 control-label">Title</label>
												<div class="col-lg-8">
													<input type="text" name="editgroup_title" class="form-control" placeholder="Group Title" value="' . ( !empty($group[0]['title']) ? $group[0]['title'] : '' ) . '">
												</div>
											</div>
											<div class="form-group">
												<label class="col-lg-4 control-label">Permissions (json)</label>
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
								        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	   									<button type="button" class="btn btn-primary" id="saveaccchanges">' . ( $modal == 'addgroup' ? 'Add Group' : 'Save changes' ) . '</button>
							        </div>
								</div>
							</div>';
				}
				return $output;
        		break;
        }
    }

    protected function saveSettings()
    {
    	if( !$this->permission('admin') )
		{
			return json_encode(array('status'=>false, 'message'=>'You do not have permission to do this'));
		}

		if( !Security::csrfCheck('usertoken', true) )
		{
			return json_encode(array('status'=>false, 'message'=>'CSRF token was invalid, please reload the form.'));
		}

		if( empty($_POST['setting']) || !is_array($_POST['setting']) )
		{
			return json_encode(array('status'=>false, 'message'=>'No settings where received.'));
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

			return json_encode(array('status'=>true, 'message'=>'System settings has been updated.'));
		}
		else
		{
			return json_encode(array('status'=>false, 'message'=>'An error occured while trying to update the settings.'));
		}
	}
}