<?php
if(!defined('CORE_PATH')){
	exit;
}

	if ( $this->logged_in):
?>
		<div class="row">
			<div class="span12">
				<p>You already have an account.. you are already logged in you know!</p>
			</div>
		</div>

<?php 
	else: 
?>		
		
		<div class="row">
			<div class="span3">
				<h3>Create new account</h3>
				<?php echo Notifications::showNotification('register_1'); ?>					
				<form id="form_signup" autocomplete="off" method="post" action="<?php echo $this->__get('base_url'); ?>/register">
					<fieldset>
						<input name="username" id="username" class="span3" type="text" placeholder="Username">
						<br />
						<input name="email" id="email" class="span3" type="text" placeholder="E-mail">
						<br />
						<input name="password" id="password" class="span3" type="password" placeholder="Password">
						<br />
						<input name="password2" class="span3" type="password" placeholder="Verify Password">
						
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
							I have read and agree to the <a href="#" class="highlight" data-area="#terms_scroller">Terms of Service</a> and the <a href="#" class="highlight" data-area="#privacy_scroller">privacy policy</a>.
						</label>
						
						<button type="submit" class="btn btn-info btn-block">Crate Free Account</button>
					</fieldset>
					<input type="hidden" name="signup" value="<?php echo Security::csrfGenerate('signup'); ?>">
					<input type="hidden" name="action" value="register">
				</form>
				
			</div>
			<div class="offset1 span8">
				<div id="terms_scroller" class="scrollbox-300 highlight-area">
					<?php include('legal/tos.html'); ?>
				</div>
				
				<div class="hr noline"></div>
				
				<div id="privacy_scroller" class="scrollbox-300 highlight-area">
					<?php include('legal/privacy-policy.html'); ?>
				</div>
			</div>
		</div>
<?php 
	endif;
?>	