<?php
if(!defined('CORE_PATH')){
	exit;
}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title><?php echo $this->config['site_name']; ?> - v. <?php echo ZF_VERSION; ?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="baseurl" content="<?php echo $this->base_url; ?>"><!-- Used in the zfscripts.js file -->
		<meta name="usertoken" content="<?php echo Security::csrfGenerate('usertoken'); ?>"><!-- Used for misc. CSRF security checks -->

		<!-- Bootstrap CSS -->
		<link href="<?php echo $this->base_url; ?>/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" media="screen">
		
		<!-- Core Css -->
		<link href="<?php echo $this->base_url; ?>/assets/css/zfstyle.css" rel="stylesheet" type="text/css" media="screen">

		<!-- Plugins Css -->
		<link href="<?php echo $this->base_url; ?>/assets/libs/nestable/nestable.css" rel="stylesheet" type="text/css" media="screen">
		
		<!-- jQuery -->
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script type="text/javascript" src="<?php echo $this->base_url; ?>/assets/libs/jquery/jquery-1.9.0.min.js"><\/script>')</script>
	</head>
	<body>
		<div id="alertMessage"></div>
		
		<div class="navbar navbar-default navbar-fixed-top">
			<div class="container">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="<?php echo $this->base_url; ?>"><?php echo $this->config['site_name']; ?></a>
				</div>
				<div class="navbar-collapse collapse">
					<?php
						if( $this->installed )
						{
							if( !$this->permission('loggedin') )
							{
									echo '<ul class="nav navbar-nav">
												<li class="' . $this->activepage('index') . '">
													<a href="' . $this->__get('base_url') . '">Home</a>
												</li>
												<li class="' . $this->activepage('login') . '">
													<a href="' . $this->generateURL('login') . '">Login</a>
												</li>
												<li class="' . $this->activepage('register') . '">
													<a href="' . $this->generateURL('register') . '">Register</a>
												</li>
												<li class="' . $this->activepage('recover') . '">
													<a href="' . $this->generateURL('recover') . '">Forgot Password</a>
												</li>
											</ul>';
							}
							else
							{
									echo '<ul class="nav navbar-nav">
												<li class="' . $this->activepage('dashboard') . '">
													<a href="' . $this->generateURL('dashboard') . '">Dashboard</a>
												</li>
												<li class="' . $this->activepage(array('forum', 'forum_thread', 'forum_category')) . '">
													<a href="' . $this->generateURL('forum') . '">Forum</a>
												</li>
											</ul>';
								
									echo '<ul class="nav navbar-nav navbar-right">
												<li class="divider-vertical"></li>
												<li class="dropdown">
													<a href="#" class="dropdown-toggle" data-toggle="dropdown">
														<img src="'. $this->avatarurl($_SESSION['data']['avatar']) . '" alt="" id="avatarthumb">
														' . $_SESSION['data']['username'] . ' <b class="caret"></b>
													</a>
													<ul class="dropdown-menu">
														<li><a href="' . $this->generateURL('settings') . '">Settings</a></li>
														<li><a href="' . $this->generateURL(null, array('action'=>'logout', 'logout'=>Security::csrfGenerate('logout'))) . '">Logout</a></li>';
												
														if( $this->permission('admin') )
														{
															echo '<li class="divider"></li>
																<li class="dropdown-header">Administration</li>
																<li><a href="' . $this->generateURL('admincp') . '">Admin Panel</a></li>';
														}
														
											echo '</ul>
												</li>
											</ul>';
							}
						}
					?>
				</div>
			</div>
		</div>

		<div id="contentbody">