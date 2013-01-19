<?php
if(!defined('CORE_PATH')){
	exit;
}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title><?php echo $this->__get('site_name'); ?> - v. <?php echo ZF_VERSION; ?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">

		<!-- Global CSS -->
		<link href="<?php echo $this->__get('base_url'); ?>/assets/css/bootstrap.min.css?v=222" rel="stylesheet" type="text/css">
		<link href="<?php echo $this->__get('base_url'); ?>/assets/css/bootstrap-responsive.min.css?v=222" rel="stylesheet" type="text/css">
		
		<!-- Core Css -->
		<link href="<?php echo $this->__get('base_url'); ?>/assets/css/zfstyle.css?v=001" rel="stylesheet" type="text/css">
		
		<!-- jQuery -->
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script type="text/javascript" src="<?php echo $this->__get('base_url'); ?>/assets/libs/jquery/jquery-1.9.0.min.js"><\/script>')</script>
	</head>
	<body>
		<div id="alertMessage"></div>
		
		<div class="navbar navbar-fixed-top">
			<div class="navbar-inner">
				<div class="container">
					<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</a>
					<a class="brand" href="<?php echo $this->__get('base_url'); ?>"><?php echo $this->__get('site_name'); ?></a>
					<div class="nav-collapse collapse">

						<?php echo $this->generateNavigation(); ?>

					</div><!--/.nav-collapse -->
				</div>
			</div>
		</div>

		<div class="container">