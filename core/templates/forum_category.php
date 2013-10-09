<?php
if(!defined('CORE_PATH')){
	exit;
}

if( !$this->permission('loggedin') )
{
	header('Location: ' . $this->generateURL('denied'));
	exit;
}

$threads = $this->loadForumThreads();
?>
<div class="main">
	<div class="container">
		<div class="col-lg-12">
			<legend>
				<h3 class="forumheader">
					<?php echo $threads['title']; ?>
					<small><?php echo $threads['description']; ?></small>
					<button class="btn btn-primary btn-sm pull-right <?php echo ( $threads['threads'][0]['admin'] && !$this->permission('admin') ? 'hide' : '' ); ?>" data-action="addnewthread" data-toggle="modal" data-loadmodal="newthread" data-id="<?php echo $threads['threads'][0]['id']; ?>">Star new thread</button>
				</h3>
			</legend>
			<ol class="breadcrumb">
				<li><a href="<?php echo $this->generateURL('forum'); ?>">Forums</a></li>
				<li class="active"><a href="<?php echo $this->generateURL('forum_category', array('category' => $threads['threads'][0]['id'], 'title' => $threads['title'])); ?>"><?php echo $threads['title']; ?></a></li>
			</ol>
		</div>

		<div class="clearfix"></div>

		<?php
		if( !is_array($threads) || empty($threads['total']) ):
		?>
				<div class="col-lg-12">No threads in this category yet.</div>
		<?php
		else:
			if( $threads['total'] > $this->pagethreadlimit ):
		?>
					<div class="clearfix"></div>
					<div class="col-lg-12">
						<ul class="pager">
							<?php echo ( $threads['page'] > 1 ? '<li class="previous"><a href="' . $this->generateURL('forum_category', array('category' => $threads['threads'][0]['id'], 'page' => ( $threads['page'] - 1 ))) . '">&larr; Older</a></li>' : '' ) ?>
						  	<li>
								<ul class="pagination">
								  	<?php
								  		for( $i = 1; $i <= ceil( $threads['total'] / $this->pagethreadlimit ); $i++ )
								  		{ 
								  			echo '<li ' . ( $threads['page'] == $i ? 'class="active"' : '' ) . '><a href="' . $this->generateURL('forum_category', array('category' => $threads['threads'][0]['id'], 'page' => $i, 'title' => $threads['threads'][0]['title'])) . '">' . $i . '</a></li>';
								  		}
								  	?>
								</ul>
							</li>
							<?php echo ( $threads['page'] < ceil( $threads['total'] / $this->pagethreadlimit ) ? '<li class="next"><a href="' . $this->generateURL('forum_category', array('category' => $threads['threads'][0]['id'], 'page' => ( $threads['page'] + 1 ))) . '">Newer &rarr;</a></li>' : '' ) ?>
						</ul>
					</div>
			<?php
			endif;
			?>

			<div class="clearfix"></div>

			<?php
			foreach( $threads['threads'] as $thread )
			{
				if( empty($thread['threadtitle']) )
				{
					continue;
				}

				$tempbody = Security::sanitize($thread['body'], 'purestring');

				echo '<div class="col-md-6">
						<div class="forum-category">
							<h4><a href="' . $this->generateURL('forum_thread', array('thread' => $thread['tid'], 'title'=>$thread['threadtitle'])) . '">' . $thread['threadtitle'] . '</a> <small class="pull-right">' . $thread['views'] . ' views, ' . ( $thread['postcount'] - 1 ). ' replies</small></h4>
							<p class="forumdesc">' . ( strlen($tempbody) > 60 ? substr($tempbody, 0, 60) . ' [&hellip;]' : $tempbody ) . '</p>
							<div class="hr"></div>
							<div class="latestpost">
								<small class="pull-left">' . ( empty($thread['replyby']) ? 'No replies yet.' : 'Latest post by ' . $thread['replyby'] . ', ' . $this->timeSince($thread['replydate']) ) . '</small>
								<small class="pull-right">By ' . $thread['username'] . ', ' . $this->timeSince($thread['date']) . '</small>
								<div class="clearfix"></div>
							</div>
						</div>
					</div>';
			}


			if( $threads['total'] > $this->pagethreadlimit ):
			?>
				<div class="clearfix"></div>
				<div class="col-lg-12">
					<ul class="pager">
						<?php echo ( $threads['page'] > 1 ? '<li class="previous"><a href="' . $this->generateURL('forum_category', array('category' => $threads['threads'][0]['id'], 'page' => ( $threads['page'] - 1 ))) . '">&larr; Older</a></li>' : '' ) ?>
					  	<li>
							<ul class="pagination">
							  	<?php
							  		for( $i = 1; $i <= ceil( $threads['total'] / $this->pagethreadlimit ); $i++ )
							  		{ 
							  			echo '<li ' . ( $threads['page'] == $i ? 'class="active"' : '' ) . '><a href="' . $this->generateURL('forum_category', array('category' => $threads['threads'][0]['id'], 'page' => $i, 'title' => $threads['threads'][0]['title'])) . '">' . $i . '</a></li>';
							  		}
							  	?>
							</ul>
						</li>
						<?php echo ( $threads['page'] < ceil( $threads['total'] / $this->pagethreadlimit ) ? '<li class="next"><a href="' . $this->generateURL('forum_category', array('category' => $threads['threads'][0]['id'], 'page' => ( $threads['page'] + 1 ))) . '">Newer &rarr;</a></li>' : '' ) ?>
					</ul>
				</div>
		<?php
			endif;
		endif;
		?>
	</div>
</div>