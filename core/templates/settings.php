<?php
if(!defined('CORE_PATH')){
	exit;
}

if( !$this->permission('loggedin') )
{
	header('Location: ' . $this->__get('base_url') );
	exit;
}

$profile = $this->getAccProfile();
?>
<div class="main">
	<div class="container">
		<div class="row">
			<form action="<?php echo $this->generateURL('settings'); ?>" method="post" enctype="multipart/form-data" onsubmit="return false;" id="uploadavatar">
				<div class="col-lg-offset-1 col-md-3 text-center">
					<img src="<?php echo $this->avatarurl($profile['avatar']); ?>" alt="" class="img-rounded">
					<div class="clearfix"></div>
					<div class="hr noline"></div>
					<input type="file" name="newavatar">
					<div class="hr noline"></div>
					<button class="btn btn-block btn-primary">Upload</button>
					<div class="checkbox">
					<label>
						<strong>Max file size:</strong> <?php echo $this->config['upload_max']; ?> Kb<br>
						<strong>Image Type:</strong> <?php echo $this->config['upload_formats']; ?>
					</label>
					</div>
				</div>
				<input type="hidden" name="avatar" value="<?php echo Security::csrfGenerate('avatar'); ?>">
				<input type="hidden" name="action" value="avatar">
			</form>

			<form action="<?php echo $this->generateURL('settings'); ?>" method="post">
				<div class="col-lg-offset-1 col-lg-4">
					<div class="form-group">
						<label>User Group: <span class="label label-info"><?php echo $profile['title']; ?></span></label>
					</div>
					<div class="form-group">
						<label for="acc_email">Email</label>
						<input autocomplete="off" type="text" id="acc_email" name="acc_email" placeholder="Email" class="form-control" value="<?php echo $profile['email']; ?>">
					</div>

					<div class="form-group">
						<label>System Language</label>
						<select class="form-control" name="acc_local">
	                        <?php
	                            foreach( scandir( CORE_PATH . '/locale') as $lang )
	                            {
	                                if( $lang != '.' && $lang != '..' )
	                                {
	                                    $lang = str_replace('.php', '', $lang);
	                                    echo '<option value="' . $lang . '" ' . ( $lang == $_SESSION['local'] ? 'selected="selected"' : '' ) . '>' . $lang . '</option>';
	                                }
	                            }
	                        ?>
	                    </select>
	                </div>

					<?php echo Notifications::showNotification('settings_1'); ?>
					
					<hr />

					<div class="form-group">
						<label for="acc_pass">New Password</label>
						<input autocomplete="off" type="password" id="acc_pass" name="acc_pass" class="form-control">
					</div>

					<div class="form-group">
						<label for="acc_pass2">Confirm New Password</label>
						<input autocomplete="off" type="password" id="acc_pass2" name="acc_pass2" class="form-control">
						<span class="help-block"><i>Leave the password fields blank if you do not wish to change your password.</i></span>
					</div>

					<hr />

					<div class="form-group">
						<label for="acc_pwcurrent">Current Password</label>
						<input autocomplete="off" type="password" id="acc_pwcurrent" name="acc_pwcurrent" class="form-control">
						<span class="help-block"><i>You need to type in your current password for security reasons whenever you make changes to account settings.</i></span>
					</div>

					<div class="form-group">
						<button type="submit" class="btn btn-block btn-primary input-lg">Save Settings</button>
					</div>
				</div>
				<input type="hidden" name="settings" value="<?php echo Security::csrfGenerate('settings'); ?>">
				<input type="hidden" name="action" value="settings">
			</form>
		</div>
	</div>
</div>