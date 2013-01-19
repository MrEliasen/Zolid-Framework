<?php
if(!defined('CORE_PATH')){
	exit;
}
?>
<div class="main">
	<div class="container">

		<!-- Feature Headliner -->
		<div class="row">
			<div class="span12 text-center">
				<h2>404</h2>
				<p>
					<?php echo $this->lang['core']['templates']['404']['notfound']; ?> 
					<a href="<?php echo $this->__get('base_url'); ?>">
						<?php echo $this->lang['core']['templates']['404']['goback']; ?>
					</a>
				</p>
			</div>
		</div>

	</div>
</div>