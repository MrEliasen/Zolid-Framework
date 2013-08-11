<?php
if(!defined('CORE_PATH')){
	exit;
}
?>
<div class="main">
	<div class="container">
		<div class="row">
			<div class="col-lg-12">
				<div class="jumbotron">
					<h1><?php echo $this->config['site_name']; ?></h1>
					<h3><?php echo ZF_VERSION; ?> by <a href="http://twitter.com/markeliasen">@MarkElisen</a></h3>
					<p>This framework is just a "simple" framework on which you can build your own sites. It comes with a build in simple user management system to handle sign ups, logins and so on. There are several security implementations build in as well, to protect against sql injections, XSS, CSRF and several session security features</p>
					<p><a href="<?php echo $this->generateURL('register'); ?>" class="btn btn-primary btn-lg">Try it out - Sign Up! &raquo;</a></p>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-lg-4">
				<h2>Download &amp; License</h2>
				<p>The latest available version is: <label class="label label-info"><?php echo ZF_VERSION; ?></label>, can download it directly from github below. This framework is released under the <a href="http://opensource.org/licenses/mit-license.php">MIT license</a>.</p>
				<p><a class="btn btn-info btn-sm" href="https://github.com/MrEliasen/Zolid-Framework/archive/master.zip">Download Now &raquo;</a></p>
			</div>
			<div class="col-lg-4">
				<h2>Documentation</h2>
				<p>The lastst version of the documentation will always be available on Github. Please consult the documentation before asking for help.</p>
				<p><a class="btn btn-info btn-sm" href="https://github.com/MrEliasen/Zolid-Framework">View Documentation &raquo;</a></p>
			</div>
			<div class="col-lg-4">
				<h2>Credits</h2>
				<p>The Zolid Framework is coded by <a href="http://twitter.com/markeliasen">@MarkElisen</a>, using <a href="http://twitter.github.com/bootstrap/index.html">Twitter Bootstrap</a> for the frontend.<br>More into at GitHub</p>
				<p><a class="btn btn-info btn-sm" href="https://github.com/MrEliasen/Zolid-Framework">GitHub Project Repo &raquo;</a></p>
			</div>
		</div>
	</div>
</div>