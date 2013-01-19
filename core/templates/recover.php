<?php
if(!defined('CORE_PATH')){
	exit;
}
?>
		<div class="row">
			<div class="span12">
				<h3>Recover Account</h3>
				<p>Did you forget your account password? No problem at all!<br />
				Just type the e-mail your used to sign up with in the fields below, and a password reset link will be sent to you.</p>
			</div>

			<div class="span3">
				<?php echo Notifications::showNotification('recover_1'); ?>
				<form action="<?php echo $this->__get('base_url'); ?>/recover" method="post">
					<fieldset>
						<input type="text" name="email" placeholder="E-mail" class="span3">
						
						<button type="submit" class="btn btn-success btn-block">Reset Password!</button>
					</fieldset>
					<input type="hidden" name="recover" value="<?php echo Security::csrfGenerate('recover'); ?>">
					<input type="hidden" name="action" value="recover">
				</form>
			</div>
		</div>