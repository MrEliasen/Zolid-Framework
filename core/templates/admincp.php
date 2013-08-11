<?php
if(!defined('CORE_PATH')){
	exit;
}

if( !$this->permission('admin') )
{
	header('Location: ' . $this->__get('base_url') );
	exit;
}
?>
<div class="main">
    <div class="container">
        <div class="row">

            <div class="col-lg-2">
                <ul class="nav nav-pills nav-stacked" id="adminTab">
                    <li class="active"><a href="#accounts" data-toggle="tab">Accounts</a></li>
                    <li><a href="#groups" data-toggle="tab">Groups</a></li>
                    <li><a href="#settings" data-toggle="tab">Settings</a></li>
                </ul>
            </div>

            <div class="col-lg-10">
                <div class="tab-content">

                    <div class="tab-pane active" id="accounts">
                        <legend>Account Management</legend>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Username</th>
                                    <th>Group</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Online</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $stmt = $this->sql->prepare('SELECT users.`id`, `username`, `active_key`, `expire`, `title` FROM users LEFT JOIN sessions ON sessions.`id` = users.`session_id` LEFT JOIN groups ON groups.`id` = users.`group`');
                                    $stmt->execute();
                                    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    $stmt->closeCursor();
                                    
                                    if( !empty($users) )
                                    {
                                        foreach( $users as $user) 
                                        {
                                            echo '<tr id="row_u' . $user['id'] . '">
                                                    <td>' . $user['id'] . '</td>
                                                    <td>' . $user['username'] . '</td>
                                                    <td>' . $user['title'] . '</td>
                                                    <td class="text-center">' . ( empty($user['active_key']) ? '<span class="label label-success">Active</span>' : '<span class="label label-warning">Inactive</span>' ) . '</td>
                                                    <td class="text-center">' . ( !empty($user['expire']) && $user['expire'] > time() - 300 ? '<span class="label label-success">Online</span>' : '<span class="label label-danger">Offline</span>' ) . '</td>
                                                    <td class="text-center">
                                                        <button class="btn btn-xs btn-primary" data-action="saveaccchanges" data-toggle="modal" data-loadmodal="editaccount" data-id="' . $user['id'] . '"><i class="glyphicon glyphicon-edit"></i></button>
                                                        <button class="btn btn-xs btn-danger" data-placement="left" data-toggle="popover" data-title="<b>Are you sure?</b>" data-content="<button class=\'btn btn-danger btn-xs funcdelete\' data-id=\'' . $user['id'] . '\' data-action=\'deleteaccount\' data-target=\'u' . $user['id'] . '\'>Yes I\'m sure</button> <button class=\'btn btn-xs btn-info closepo\'>No</button>"><i class="glyphicon glyphicon-trash"></i></button>
                                                    </td>
                                                </tr>';
                                        }
                                        
                                        unset($key);
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="tab-pane in fade" id="groups">
                        <legend>Member Groups <button class="btn btn-xs btn-success" data-action="savegroupchanges" data-toggle="modal" data-loadmodal="addgroup" >Add New</button></legend>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th class="text-center">actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    foreach( $this->sql->query('SELECT * FROM groups') as $group) 
                                    {
                                        echo '<tr id="row_g' . $group['id'] . '">
                                                <td>' . $group['id'] . '</td>
                                                <td>' . $group['title'] . '</td>
                                                <td class="text-center">
                                                    <button class="btn btn-xs btn-primary" data-action="savegroupchanges" data-toggle="modal" data-loadmodal="editgroup" data-id="' . $group['id'] . '"><i class="glyphicon glyphicon-edit"></i></button>
                                                    <button class="btn btn-xs btn-danger" data-placement="left" data-toggle="popover" data-title="<b>Are you sure?</b>" data-content="<button class=\'btn btn-danger btn-xs funcdelete\' data-id=\'' . $group['id'] . '\' data-action=\'deletegroup\' data-target=\'g' . $group['id'] . '\'>Yes I\'m sure</button> <button class=\'btn btn-xs btn-info closepo\'>No</button>"><i class="glyphicon glyphicon-trash"></i></button>
                                                </td>
                                            </tr>';
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="tab-pane in fade" id="settings">
                        <legend>System Settings</legend>
                        <form autocomplete="off" action="#" class="form-horizontal">
                            <fieldset>
                                <?php
                                    $stmt = $this->sql->query('SELECT * FROM settings');
                                    $stmt->execute();
                                    $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    $stmt->closeCursor();

                                    foreach ($settings as $setting)
                                    {
                                        echo '<div class="form-group">
                                                <label class="col-lg-4 control-label">' . $setting['title'] . '</label>
                                                <div class="col-lg-8">';

                                                    switch ( $setting['type']) {
                                                        case 'text':
                                                            echo '<input name="setting[' . $setting['key'] . ']" class="form-control" type="text" value="' . $setting['value'] . '">';
                                                            break;
                                                        
                                                        case 'check':
                                                            echo '<div class="checkbox">
                                                                    <label>
                                                                        <input type="checkbox" name="setting[' . $setting['key'] . ']" ' . ( $setting['value'] ? 'checked="checked"' : '' ) . '>
                                                                    </label>
                                                                  </div>';
                                                            break;
                                                        
                                                        case 'select':
                                                            echo '<select class="form-control" name="setting[' . $setting['key'] . ']">';

                                                                    if( !empty($setting['options']) )
                                                                    {
                                                                        foreach (json_decode($setting['options']) as $key => $value) {
                                                                            echo '<option value="' . Security::sanitize($value, 'string') . '" ' . ( $value == $setting['value'] ? 'selected="selected"' : '' ) . '>
                                                                                    ' . Security::sanitize($key, 'string') . '
                                                                                </option>';
                                                                        }
                                                                    }

                                                              echo '</select>';
                                                            break;
                                                    }

                                        echo '</div>
                                            </div>';
                                    }
                                ?>
                                <hr>
                                <div class="col-lg-offset-4 col-lg-8">
                                    <p class="help-block"><b>Please Note!</b><br>if you enable Friendly URLS, you have to uncomment the lines specified in the .htaccess file!</p>
                                    <button class="btn btn-primary btn-block" id="saveSettings">Save Settings</button>
                                </div>
                            </fieldset>
                            <input type="hidden" name="savesettings" value="<?php echo Security::csrfGenerate('savesettings'); ?>">
                        </form>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>