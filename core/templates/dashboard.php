<?php
if(!defined('CORE_PATH')){
	exit;
}

if( !$this->permission('loggedin') )
{
	header('Location: ' . $this->__get('base_url') );
	exit;
}
?>
<div class="main">
    <div class="container">
        <div class="row">
    		<div class="col-lg-12">
                <h4>Your are logged in</h4>
                <p>Welcome <?php echo $_SESSION['data']['username']; ?></p>
            </div>
        </div>
    </div>
</div>