<?php
if(!defined('CORE_PATH')){
	exit;
}

if ( $this->permission('loggedin') )
{
	header('Location: ' . $this->base_url);
	exit;
}
?>
<div class="main">
	<div class="container">
		<div class="row">
			<div class="col-lg-3">
				<h3>Create new account</h3>
				<?php echo Notifications::showNotification('register_1'); ?>					
				<form id="form_signup" autocomplete="off" method="post" action="<?php echo $this->generateURL('register'); ?>">
					<fieldset>
						
						<div class="form-group">
							<input name="username" id="username" class="form-control" type="text" placeholder="Username">
						</div>
						<div class="form-group">
							<input name="email" id="email" class="form-control" type="text" placeholder="E-mail">
						</div>
						<div class="form-group">
							<input name="password" id="password" class="form-control" type="password" placeholder="Password">
						</div>
						<div class="form-group">
							<input name="password2" class="form-control" type="password" placeholder="Verify Password">
						</div>
						<div class="password_strength_container">
							<div class="password_strength_wrapper">
								<div class="password_strength"></div>
								<div class="password_strength_separator white" style="left: 25%;"></div>
								<div class="password_strength_separator white" style="left: 50%;"></div>
								<div class="password_strength_separator white" style="left: 75%;"></div>
							</div>
							<div class="password_strength_desc"></div>
						</div>

						<hr />

						<label class="checkbox">
							<input name="terms" type="checkbox" class="switch icons">
							I have read and agree to the
                            <a href="#" class="highlight" data-area="#terms_scroller">Terms of Service</a>
                            and the
                            <a href="#" class="highlight" data-area="#privacy_scroller">Privacy Policy</a>.
						</label>
						
						<button type="submit" class="btn btn-primary btn-block">Create Account</button>
					</fieldset>
					<input type="hidden" name="signup" value="<?php echo Security::csrfGenerate('signup'); ?>">
					<input type="hidden" name="action" value="register">
				</form>
				
			</div>
			<div class="col-lg-offset-1 col-lg-8">
				<div id="terms_scroller" class="scrollbox-300 highlight-area">
					<?php include('legal/tos.html'); ?>
				</div>
				
				<div class="hr noline"></div>
				
				<div id="privacy_scroller" class="scrollbox-300 highlight-area">
					<?php include('legal/privacy-policy.html'); ?>
				</div>
			</div>
		</div>
	</div>
</div>