<?php
if(!defined('CORE_PATH')){
	exit;
}

if( !$this->permission('loggedin') )
{
	header('Location: ' . $this->generateURL('denied'));
	exit;
}
?>
<div class="main">
	<div class="container">
		<div class="col-lg-12">
			<legend>Forum Categories</legend>
		</div>

		<div class="clearfix"></div>

		<?php

		foreach( $this->loadForumCategories() as $cat )
		{
			if( empty($cat['title']) )
			{
				continue;
			}

			$caturl = $this->generateURL('forum_category', array('category' => $cat['id'], 'title' => $cat['title']));
			$posturl = $this->generateURL('forum_thread', array('thread' => $cat['tid'], 'page' => $cat['lastpage'], 'title' => $cat['threadtitle']));

			echo '<div class="col-md-6">
					<div class="forum-category">
						<h3><a href="' . $caturl . '">' . $cat['title'] . '</a> <small class="pull-right">' . $cat['threads'] . ' threads, ' . ( $cat['posts'] - $cat['threads'] ) . ' replies</small></h3>
						<p class="forumdesc">' . $cat['description'] . '</p>
						<div class="hr"></div>
						<div class="latestpost">
								' . ( empty($cat['threadtitle']) ? 'No threads in this category yet.' : '<small>Latest post by ' . Security::sanitize($cat['username'], 'purestring') . ', ' . $this->timeSince($cat['date']) . ' in thread: <a href="' . $posturl . '">' . Security::sanitize($cat['threadtitle'], 'purestring') . '</a></small>' ) . '
						</div>
					</div>
				</div>';
		}

		?>

	</div>
</div>