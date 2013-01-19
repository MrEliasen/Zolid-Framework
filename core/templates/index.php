<?php
if(!defined('CORE_PATH')){
	exit;
}
?>
		<div class="row">
		<?php 
		//hide the login if the user is logged in
		if( !$this->logged_in ): 
		?>
			<div class="span3">
				<h3><?php echo $this->lang['core']['templates']['index']['login_title']; ?></h3>
				<?php 
					echo Notifications::showNotification('login_1');
					echo Notifications::showNotification('activate_1'); 
				?>
				<form action="<?php echo $this->__get('base_url'); ?>" method="post">
					<fieldset>
						<input type="text" name="email" placeholder="<?php echo $this->lang['core']['templates']['index']['login_email']; ?>" class="span3">
						<br />
						<input type="password" name="password" placeholder="<?php echo $this->lang['core']['templates']['index']['login_password']; ?>" class="span3">
						
						<label class="checkbox">
							<input name="remember" type="checkbox"> <?php echo $this->lang['core']['templates']['index']['login_remember']; ?>
						</label>

						<button type="submit" class="btn btn-success btn-block"><?php echo $this->lang['core']['templates']['index']['login_submit']; ?></button>
						<br />
						<small><a href="<?php echo $this->__get('base_url'); ?>/recover"><?php echo $this->lang['core']['templates']['index']['forgotlogin']; ?></a></small>
					</fieldset>
					<input type="hidden" name="login" value="<?php echo Security::csrfGenerate('login'); ?>">
					<input type="hidden" name="action" value="login">
				</form>
			</div>
		<?php endif; ?>
			<div class="<?php echo ( !$this->logged_in ? 'offset1 span8' : 'span12'); // if the user is logged in, we hide the login form and make the hero unit span full width ?>">
				<div class="hero-unit">
					<h1><?php echo $this->__get('site_name'); ?></h1>
					<h3><?php echo ZF_VERSION; ?> by <a href="http://twitter.com/markeliasen">@MarkElisen</a></h3>
					<p><?php echo $this->lang['core']['templates']['index']['herobody']; ?></p>
					<p><a href="<?php echo $this->__get('base_url'); ?>/register" class="btn btn-primary btn-large"><?php echo $this->lang['core']['templates']['index']['herosignup']; ?> &raquo;</a></p>
				</div>
			</div>

		</div>

		<div class="row">
			<div class="span4">
				<h2><?php echo $this->lang['core']['templates']['index']['block1_title']; ?></h2>
				<p><?php echo $this->lang['core']['templates']['index']['block1_body']; ?></p>
				<p><a class="btn btn-info" href="#"><?php echo $this->lang['core']['templates']['index']['block1_button']; ?> &raquo;</a></p>
			</div>
			<div class="span4">
				<h2><?php echo $this->lang['core']['templates']['index']['block2_title']; ?></h2>
				<p><?php echo $this->lang['core']['templates']['index']['block2_body']; ?></p>
				<p><a class="btn btn-info" href="#"><?php echo $this->lang['core']['templates']['index']['block2_button']; ?> &raquo;</a></p>
			</div>
			<div class="span4">
				<h2><?php echo $this->lang['core']['templates']['index']['block3_title']; ?></h2>
				<p><?php echo $this->lang['core']['templates']['index']['block3_body']; ?></p>
			</div>
		</div>