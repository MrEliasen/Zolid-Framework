<?php
if(!defined('CORE_PATH')){
	exit;
}
?>
<div class="main">
	<div class="container">
		<div class="row">
			<div class="col-lg-offset-4 col-lg-4">
				<h3>Recover Account</h3>
				<p>To reset your account password, just type the e-mail or username of your account in the field below.</p>
			</div>

			<div class="col-lg-offset-4 col-lg-4">
				<?php echo Notifications::showNotification('recover_1'); ?>
				<form action="<?php echo $this->generateURL('recover'); ?>" method="post">
					<fieldset>
						<div class="form-group">
							<input type="text" name="email" placeholder="Email or Username" class="form-control">
						</div>
						<button type="submit" class="btn btn-primary btn-block">Reset Password!</button>
					</fieldset>
					<input type="hidden" name="recover" value="<?php echo Security::csrfGenerate('recover'); ?>">
					<input type="hidden" name="action" value="recover">
				</form>
			</div>
		</div>
	</div>
</div>