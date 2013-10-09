<?php
if(!defined('CORE_PATH')){
	exit;
}

if( !$this->permission('admin') )
{
	header('Location: ' . $this->__get('base_url') );
	exit;
}

$version = $this->checkVersion();
?>
<div class="main">
    <div class="container">
        <div class="row">

            <div class="col-lg-2">
                <ul class="nav nav-pills nav-stacked" id="adminTab">
                    <li class="active"><a href="#accounts" data-toggle="tab">Accounts</a></li>
                    <li><a href="#groups" data-toggle="tab">Groups</a></li>
                    <li><a href="#forums" data-toggle="tab">Forum</a></li>
                    <li><a href="#settings" data-toggle="tab">Settings</a></li>
                </ul>
                <div class="hr noline"></div>
                <table class="table">
                    <thead>
                        <tr>
                            <th colspan="2">Framework Version</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Current:</strong></td>
                            <td><?php echo $version['current']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Latest:</strong></td>
                            <td><?php echo $version['latest'] . ( $version['upgrade'] ? ' <i class="glyphicon glyphicon-question-sign" data-toggle="tooltip" title="' . $version['message'] . '"></i>' : '' ); ?></td>
                        </tr>
                        <?php
                        if( $version['upgrade'] )
                        {
                            echo '<tr>
                                    <td><strong>Released:</strong></td>
                                    <td>' . date('d/m/y', $version['release']) . '</td>
                                </tr>';

                            $priority = '';
                            switch( $version['priority'] )
                            {
                                default:
                                case 1:
                                    $priority = '<span class="label label-info">Normal</span>';
                                    break;

                                case 2:
                                    $priority = '<span class="label label-warning">Medium</span>';
                                    break;

                                case 3:
                                    $priority = '<span class="label label-danger">Critial</span>';
                                    break;
                            }
                            echo '<tr>
                                    <td><strong>Priority:</strong></td>
                                    <td>' . $priority . '</td>
                                </tr>';
                        }
                        else
                        {
                            echo '<tr>
                                    <td colspan="2" class="text-center">Up To Date <i class="glyphicon glyphicon-ok"></i></td>
                                </tr>';
                        }
                        ?>
                    </tbody>
                </table>
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
                                    $stmt = $this->sql->prepare('SELECT users.`id`, `username`, `active_key`, `expire`, `title` FROM users LEFT JOIN sessions ON sessions.`id` = users.`session_id` LEFT JOIN groups ON groups.`id` = users.`membergroup`');
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

                    <div class="tab-pane in fade" id="forums">
                        <legend>Forum Categories <button class="btn btn-xs btn-success" data-action="savenewforum" data-toggle="modal" data-loadmodal="addforumcat" >Add New</button><img alt="" src="assets/img/loading-small.gif" id="savestatus-loading"><i id="savestatus-ok" class="glyphicon glyphicon-floppy-saved"></i><i id="savestatus-err" class="glyphicon glyphicon-floppy-remove"></i></legend>
                        <form autocomplete="off" action="#" class="form-horizontal">
                            <fieldset>
                                <div class="dd" id="forumlist">
                                    <ol class="dd-list">
                                        <?php
                                            $stmt = $this->sql->query('SELECT * FROM forum_categories ORDER BY sort ASC');
                                            $stmt->execute();
                                            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                            $stmt->closeCursor();

                                            foreach ($categories as $category)
                                            {
                                                echo '<li id="row_fc' . $category['id'] . '" class="dd-item dd3-item" data-id="' . $category['id'] . '">
                                                        <div class="dd-handle dd3-handle">Drag</div>
                                                        <div class="dd3-content">
                                                            ' . $category['title'] . '
                                                            <button onclick="return false;" class="pull-right btn btn-xs btn-danger" data-placement="left" data-toggle="popover" data-title="<b>All posts in this category will be deleted as well, continue?</b>" data-content="<button onclick=\'return false;\' class=\'btn btn-danger btn-xs funcdelete\' data-id=\'' . $category['id'] . '\' data-action=\'deleteforumcategory\' data-target=\'fc' . $category['id'] . '\'>Yes, delete it</button> <button onclick=\'return false;\' class=\'btn btn-xs btn-info closepo\'>No</button>"><i class="glyphicon glyphicon-trash"></i></button>
                                                            <button class="pull-right btn btn-xs btn-primary" data-action="updatecategory" data-toggle="modal" data-loadmodal="editforumcategory" data-id="' . $category['id'] . '"><i class="glyphicon glyphicon-edit"></i></button>
                                                        </div>
                                                    </li>';
                                            }
                                        ?>
                                    </ol>
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