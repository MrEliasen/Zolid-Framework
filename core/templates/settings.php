<?php
if(!defined('CORE_PATH')){
	exit;
}

if( !$this->logged_in )
{
	header('Location: ' . $this->__get('base_url') );
	exit;
}

$profile = $this->getAccProfile();
?>
	<form action="<?php echo $this->__get('base_url'); ?>/settings" method="post">

		<div class="row">
			<div class="span3">
				<img src="http://www.gravatar.com/avatar/<?php echo md5( strtolower( $profile['email'] ) ); ?>?size=200&amp;d=mm&amp;r=pg" alt="" class="img-rounded">
				<div class="clearfix"></div>
				<p>Update your avatar at <a href="http://gravatar.com">Gravatar.com</a>.</p>
			</div>
			<div class="span6 form-horizontal">
				<div class="control-group">
					<label class="control-label">Username</label>
					<div class="controls">
						<span class="label label-warning" style="margin-top: 5px;"><?php echo $profile['username']; ?></span>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="acc_email">Email</label>
					<div class="controls">
						<input autocomplete="off" type="text" id="acc_email" name="acc_email" placeholder="Email" value="<?php echo $profile['email']; ?>">
					</div>
				</div>

				<?php echo Notifications::showNotification('settings_1'); ?>
				
				<hr />

				<div class="control-group">
					<label class="control-label" for="acc_pass">New Password</label>
					<div class="controls">
						<input autocomplete="off" type="password" id="acc_pass" name="acc_pass">
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="acc_pass2">Confirm New Password</label>
					<div class="controls">
						<input autocomplete="off" type="password" id="acc_pass2" name="acc_pass2">
						<span class="help-block"><i>Leave the password fields blank if you do not wish to change your password.</i></span>
					</div>
				</div>

				<hr />

				<div class="control-group">
					<label class="control-label" for="acc_pwcurrent">Current Password</label>
					<div class="controls">
						<input autocomplete="off" type="password" id="acc_pwcurrent" name="acc_pwcurrent">
						<span class="help-block"><i>You need to type in your current password for security reasons whenever you make changes to your account.</i></span>
					</div>
				</div>

				<div class="control-group">
					<div class="controls">
						<button type="submit" class="btn btn-success input-large">Save Settings</button>
					</div>
				</div>
			</div>

			<div class="clearfix"></div>

			<div class="span12">
				<hr />
				<div class="row">
					<div class="span6">
						<h4>Mail Settings</h4>
						<label class="checkbox" for="mail_admins">
							<input type="checkbox" id="mail_admins" name="mail_admins" <?php echo ( $profile['mail_admins'] ? 'checked="checked"' : ''); ?>> Administation
							<span class="help-block"><i>Enabling this option will add your email address to the administrators mail list and you will receive any updates sent from <?php echo $this->__get('site_name'); ?>.</i></span>
						</label>
						<label class="checkbox" for="mail_users">
							<input type="checkbox" id="mail_users" name="mail_users" <?php echo ( $profile['mail_members'] ? 'checked="checked"' : ''); ?>> Members
							<span class="help-block"><i>Enabling this option will allow other members to send you emails from your profile. Your e-mail will not be visible to other members.</i></span>
						</label>

					</div>
					<div class="span6">
						<h4>General Settings</h4>

						<label for="local">Language</label>
						<select class="large" name="acc_local">
                            <?php
                                foreach( scandir( CORE_PATH . '/locale') as $lang )
                                {
                                    if( $lang != '.' && $lang != '..' )
                                    {
                                        $lang = str_replace('.php', '', $lang);
                                        echo '<option value="' . $lang . '" ' . ($lang == $_SESSION['local'] ? 'selected="selected"' : '') . '>' . $lang . '</option>';
                                    }
                                }
                            ?>
                        </select>
					</div>
				</div>

			</div>
		</div>

		<input type="hidden" name="settings" value="<?php echo Security::csrfGenerate('settings'); ?>">
		<input type="hidden" name="action" value="settings">
	</form>