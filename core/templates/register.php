<?php
if(!defined('CORE_PATH')){
	exit;
}

	if ( $this->logged_in):
?>
		<div class="row">
			<div class="span12">
				<p><?php echo $this->lang['core']['templates']['register']['register_loggedin']; ?></p>
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
						<input name="username" id="username" class="span3" type="text" placeholder="<?php echo $this->lang['core']['templates']['register']['register_username']; ?>">
						<br />
						<input name="email" id="email" class="span3" type="text" placeholder="<?php echo $this->lang['core']['templates']['register']['register_email']; ?>">
						<br />
						<input name="password" id="password" class="span3" type="password" placeholder="<?php echo $this->lang['core']['templates']['register']['register_password']; ?>">
						<br />
						<input name="password2" class="span3" type="password" placeholder="<?php echo $this->lang['core']['templates']['register']['register_verify']; ?>">
						
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
							<?php echo $this->lang['core']['templates']['register']['register_tos_part1']; ?>
                            <a href="#" class="highlight" data-area="#terms_scroller"><?php echo $this->lang['core']['templates']['register']['register_tos']; ?></a>
                            <?php echo $this->lang['core']['templates']['register']['register_tos_part2']; ?>
                            <a href="#" class="highlight" data-area="#privacy_scroller"><?php echo $this->lang['core']['templates']['register']['register_pp']; ?></a>.
						</label>
						
						<button type="submit" class="btn btn-info btn-block"><?php echo $this->lang['core']['templates']['register']['register_submit']; ?></button>
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