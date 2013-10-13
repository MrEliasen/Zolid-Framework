<div class="main">
	<div class="container">
		<div class="row">
			<?php
			if( empty($_GET['step']) || $_GET['step'] == 1):
				$continue = true;
			?>

			<div class="col-lg-offset-3 col-lg-6">                        
				<legend>System Check</legend>
			</div>

			<div class="col-lg-offset-3 col-lg-6">                        
				<table class="table table-striped">
					<tr>
						<td>PHP Version (5.3.0+)</td>
						<td style="text-align: right;">
							<?php
								if( version_compare(PHP_VERSION, '5.3.0') < 0 )
								{
									$continue = false;
									echo '<span class="label label-danger"><i class="glyphicon glyphicon-thumbs-down"></i></span>';
								}
								else
								{
									echo '<span class="label label-success"><i class="glyphicon glyphicon-thumbs-up"></i></span>';
								}
							?>
						</td>
					</tr>
					<tr>
						<td>Required Extension 'mysql'</td>
						<td style="text-align: right;">
							<?php
								if( !extension_loaded('mysql') )
								{
									$continue = false;
									echo '<span class="label label-danger"><i class="glyphicon glyphicon-thumbs-down"></i></span>';
								}
								else
								{
									echo '<span class="label label-success"><i class="glyphicon glyphicon-thumbs-up"></i></span>';
								}
							?>
						</td>
					</tr>
					<tr>
						<td>Required Extension 'json'</td>
						<td style="text-align: right;">
							<?php
								if( !extension_loaded('json') )
								{
									$continue = false;
									echo '<span class="label label-danger"><i class="glyphicon glyphicon-thumbs-down"></i></span>';
								}
								else
								{
									echo '<span class="label label-success"><i class="glyphicon glyphicon-thumbs-up"></i></span>';
								}
							?>
						</td>
					</tr>
					<tr>
						<td>Required Extension 'pdo'</td>
						<td style="text-align: right;">
							<?php
								if( !extension_loaded('pdo') )
								{
									$continue = false;
									echo '<span class="label label-danger"><i class="glyphicon glyphicon-thumbs-down"></i></span>';
								}
								else
								{
									echo '<span class="label label-success"><i class="glyphicon glyphicon-thumbs-up"></i></span>';
								}
							?>
						</td>
					</tr>
					<tr>
						<td>Required Extension 'session'</td>
						<td style="text-align: right;">
							<?php
								if( !extension_loaded('session') )
								{
									$continue = false;
									echo '<span class="label label-danger"><i class="glyphicon glyphicon-thumbs-down"></i></span>';
								}
								else
								{
									echo '<span class="label label-success"><i class="glyphicon glyphicon-thumbs-up"></i></span>';
								}
							?>
						</td>
					</tr>
					<tr>
						<td>Required Functions 'fopen', 'fclose' &amp; 'fwrite'</td>
						<td style="text-align: right;">
							<?php
								if( !function_exists('fopen') || !function_exists('fclose') || !function_exists('fwrite') )
								{
									$continue = false;
									echo '<span class="label label-danger"><i class="glyphicon glyphicon-thumbs-down"></i></span>';
								}
								else
								{
									echo '<span class="label label-success"><i class="glyphicon glyphicon-thumbs-up"></i></span>';
								}
							?>
						</td>
					</tr>
					<tr>
						<td>Required Extension 'Imagick' or 'GD'</td>
						<td style="text-align: right;">
							<?php
								if( !extension_loaded('imagick') && !extension_loaded('gd') )
								{
									$continue = false;
									echo '<span class="label label-danger"><i class="glyphicon glyphicon-thumbs-down"></i></span>';
								}
								else
								{
									echo '<span class="label label-success"><i class="glyphicon glyphicon-thumbs-up"></i></span>';
								}
							?>
						</td>
					</tr>
					<tr>
						<td>Recommended Mod 'mod_rewrite'</td>
						<td style="text-align: right;">
							<?php
								if( !array_key_exists('HTTP_MOD_REWRITE', $_SERVER) )
								{
									echo '<span class="label label-danger"><i class="glyphicon glyphicon-thumbs-down"></i></span>';
								}
								else
								{
									echo '<span class="label label-success"><i class="glyphicon glyphicon-thumbs-up"></i></span>';
								}
							?>
						</td>
					</tr>
					<tr>
						<td>Write Permissions: /</td>
						<td style="text-align: right;">
							<?php
								if( !is_writable(BASE_PATH) )
								{
									$continue = false;
									echo '<span class="label label-danger"><i class="glyphicon glyphicon-thumbs-down"></i></span>';
								}
								else
								{
									echo '<span class="label label-success"><i class="glyphicon glyphicon-thumbs-up"></i></span>';
								}
							?>
						</td>
					</tr>
					<tr>
						<td>Write Permissions: /core</td>
						<td style="text-align: right;">
							<?php
								if( !is_writable(CORE_PATH) )
								{
									$continue = false;
									echo '<span class="label label-danger"><i class="glyphicon glyphicon-thumbs-down"></i></span>';
								}
								else
								{
									echo '<span class="label label-success"><i class="glyphicon glyphicon-thumbs-up"></i></span>';
								}
							?>
						</td>
					</tr>
				</table>
				<hr>
				<?php
					if( $continue )
					{
						echo '<a href="?step=2" class="btn btn-primary btn-block">Continue Installation</a>';
					}
					else
					{
						echo '<a href="?step=1" class="btn btn-warning btn-block">Test Again</a>';
					}
				?>
			</div>

			<?php
			elseif( $_GET['step'] == 2):
			?>
			<form id="frameworkinstall" class="form-horizontal" method="post" action="#" autocomplete="off">
				<div class="col-lg-offset-2 col-lg-6">
					<legend>Database Settings</legend>
					<div class="form-group">
						<label class="col-lg-4 control-label" for="sqlhost">Host</label>
						<div class="col-lg-8">
							<input class="form-control" type="text" id="sqlhost" name="sqlhost" placeholder="Host" value="localhost">
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-4 control-label" for="sqlport">Port</label>
						<div class="col-lg-8">
							<input class="form-control" type="text" id="sqlport" name="sqlport" placeholder="Port" value="3306">
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-4 control-label" for="sqldb">Database Name</label>
						<div class="col-lg-8">
							<input class="form-control" type="text" id="sqldb" name="sqldb" placeholder="Database Name" value="">
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-4 control-label" for="sqluser">User</label>
						<div class="col-lg-8">
							<input class="form-control" type="text" id="sqluser" name="sqluser" placeholder="User">
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-4 control-label" for="sqlpass">Password</label>
						<div class="col-lg-8">
							<input class="form-control" type="password" id="sqlpass" name="sqlpass" placeholder="Password">
						</div>
					</div>
                    
					<legend>Site Settings</legend>
					<div class="form-group">
						<label class="col-lg-4 control-label" for="site_url">Base Url</label>
						<div class="col-lg-8">
							<input class="form-control" type="text" id="site_url" name="site_url" value="<?php echo $this->__get('base_url'); ?>">
                            <span class="help-block">This is the full url to where the system root is (the index.php).<br>
                            <strong>Important!</strong>: the url should <strong>NOT</strong> end with a trailing slash ("/" without the quotes).</span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-4 control-label" for="site_name">Site Name</label>
						<div class="col-lg-8">
							<input class="form-control" type="text" id="site_name" name="site_name" placeholder="eg. Zolid Framework">
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-4 control-label" for="site_mail">Site Email</label>
						<div class="col-lg-8">
							<input class="form-control" type="text" id="site_mail" name="site_mail" placeholder="eg. hello@domain.com">
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-4 control-label" for="site_zone">Site Timezone</label>
						<div class="col-lg-8">
							<select id="site_zone" class="form-control" name="site_zone">
							<?php
								foreach( array_flip( $this->timezones() ) as $key => $value )
								{
									echo '<option value="' . $value . '" ' . ($value == 'Europe/Copenhagen' ? 'selected="selected"' : '') . '>' . $key . '</option>';
								}
							?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-4 control-label" for="site_lang">Default Language</label>
						<div class="col-lg-8">
                            <select class="form-control" name="site_lang">
                                <?php
                                    foreach( scandir( CORE_PATH . '/locale') as $lang )
                                    {
                                        if( $lang != '.' && $lang != '..' )
                                        {
                                            $lang = str_replace('.php', '', $lang);
                                            echo '<option value="' . $lang . '">' . $lang . '</option>';
                                        }
                                    }
                                ?>
                            </select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-4 control-label" for="site_mail">Use Friendly Urls?</label>
						<div class="col-lg-8">
							<div class="checkbox">
								<label>
									<input type="checkbox" id="site_seourl" name="site_seourl" value="1"> Rememer to uncomment the required lines in your .htaccess file <b>after installing</b>!
								</label>
							</div>
						</div>
					</div>
                    
					<legend>SMTP Settings</legend>
                    <p>You can leave this blank to use PHP mail instead.</p>
					<div class="form-group">
						<label class="col-lg-4 control-label" for="smtp_host">Host</label>
						<div class="col-lg-8">
							<input class="form-control" type="text" id="smtp_host" name="smtp_host" placeholder="eg. mail.domain.com">
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-4 control-label" for="smtp_port">Port</label>
						<div class="col-lg-8">
							<input class="form-control" type="text" id="smtp_port" name="smtp_port" placeholder="25 is default">
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-4 control-label" for="smtp_user">User</label>
						<div class="col-lg-8">
							<input class="form-control" type="text" id="smtp_user" name="smtp_user" placeholder="">
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-4 control-label" for="smtp_pass">Password</label>
						<div class="col-lg-8">
							<input class="form-control" type="text" id="smtp_pass" name="smtp_pass" placeholder="">
						</div>
					</div>
                    
					<legend>Admin Account</legend>
					<div class="form-group">
						<label class="col-lg-4 control-label" for="username">Username</label>
						<div class="col-lg-8">
							<input class="form-control" type="text" id="username" name="username">
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-4 control-label" for="email">Email</label>
						<div class="col-lg-8">
							<input class="form-control" type="text" id="email" name="email">
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-4 control-label" for="password">Password</label>
						<div class="col-lg-8">
							<input class="form-control" type="password" id="password" name="password">
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-4 control-label" for="password2">Confirm Password</label>
						<div class="col-lg-8">
							<input class="form-control" type="password" id="password2" name="password2">
						</div>
					</div>
				</div>
			</form>
            
            <div class="clearfix"></div>
            <hr>

            <div class="col-lg-offset-2 col-lg-6 control-label">
                <button id="installframework" type="submit" class="btn btn-lg btn-block btn-primary">Install Framework</button>
                <div class="hr noline"></div>
            </div>

            <?php
			endif;
			?>

		</div>
	</div>
</div>

<!-- Additional Assets -->
<script>
	$('.nav-collapse').hide();

	$(document).ready(function () {
		$("#installframework").click(function (e) {
			hideNotification();
			setLoading('Installing, this might take a few seconds seconds..',true);
			$.ajax({
				url: '<?php echo str_replace('&amp;', '&', $this->generateURL('ajax', array('a' => 'install')) ); ?>',
				type: 'POST',
				data: $('#frameworkinstall').serialize(),
				dataType: 'json',
				success: function(data) {
					removeLoading();
					if (data.status) {
						$("#testconnection button").html('Installed Successfully!').addClass('btn-success').attr('disabled', 'true');
						showNotification(data.message, 'Success', 'success');
						setTimeout((function(){location.href="/";}), 3000);
					} else {
						showNotification(data.message, 'Error', 'error');
					}
				}
			});
			return false;
			e.preventDefault();
		});
	});
</script>