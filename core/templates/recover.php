<?php
if(!defined('CORE_PATH')){
	exit;
}
?>
		<div class="row">
			<div class="span12">
				<h3><?php echo $this->lang['core']['templates']['recover']['recover_title']; ?></h3>
				<p><?php echo $this->lang['core']['templates']['recover']['recover_text']; ?></p>
			</div>

			<div class="span3">
				<?php echo Notifications::showNotification('recover_1'); ?>
				<form action="<?php echo $this->__get('base_url'); ?>/recover" method="post">
					<fieldset>
						<input type="text" name="email" placeholder="<?php echo $this->lang['core']['templates']['recover']['recover_email']; ?>" class="span3">
						
						<button type="submit" class="btn btn-success btn-block"><?php echo $this->lang['core']['templates']['recover']['recover_submit']; ?></button>
					</fieldset>
					<input type="hidden" name="recover" value="<?php echo Security::csrfGenerate('recover'); ?>">
					<input type="hidden" name="action" value="recover">
				</form>
			</div>
		</div>