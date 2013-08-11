<?php
if(!defined('CORE_PATH')){
	exit;
}

if( $this->permission('loggedin') )
{
	header('Location: index.php');
	exit;
}
?>
<div class="main">
	<div class="container">
		<div class="row">
			<div class="col-lg-offset-4 col-lg-4">
				<h3>Account Login</h3>
				<?php 
					echo Notifications::showNotification('login_1');
					echo Notifications::showNotification('activate_1'); 
				?>
				<form action="<?php echo $this->generateURL('login'); ?>" method="post">
					<fieldset>
						<div class="form-group">
							<input type="text" name="email" placeholder="Email / Username" class="form-control">
						</div>
						<div class="form-group">
							<input type="password" name="password" placeholder="Password" class="form-control">
						</div>
						<div class="checkbox">
      						<label>
								<input name="remember" type="checkbox"> Remember login information
							</label>
						</div>
						<button type="submit" class="btn btn-primary btn-block">Submit</button>
						<br />
						<small><a href="<?php echo $this->generateURL('recover'); ?>">Forgot your login details?</a></small>
					</fieldset>
					<input type="hidden" name="login" value="<?php echo Security::csrfGenerate('login'); ?>">
					<input type="hidden" name="action" value="login">
				</form>
			</div>
		</div>
	</div>
</div>