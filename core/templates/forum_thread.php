<?php
if(!defined('CORE_PATH')){
	exit;
}

if( !$this->permission('loggedin') )
{
	header('Location: ' . $this->__get('base_url') );
	exit;
}

$threads = $this->getThread();
?>
<div class="main">
	<div class="container">
	<?php
		if( !is_array($threads) || empty($threads) ):
	?>

		<div class="col-lg-12">Thread not found.</div>

	<?php
		else:

		$edit = '';
		$delete = '';
		if( $this->permission('admin') || $_SESSION['data']['uid'] == $threads[0]['owner'] )
		{
			$edit = '<button class="btn btn-warning btn-sm pull-right showtooltip" title="Edit Topic" data-action="editthread" data-toggle="modal" data-loadmodal="editthread" data-id="' . $threads['posts'][0]['thread']. '"><i class="glyphicon glyphicon-edit"></i></button>';

			if( $this->permission('admin') )
			{
				$delete = '<button class="btn btn-sm btn-danger pull-right showtooltip" title="Delete Topic" data-placement="left" data-toggle="popover" data-title="<b>Are you sure?</b>" data-content="<button class=\'btn btn-danger btn-xs funcdelete\' data-id=\'' . $threads['posts'][0]['thread'] . '\' data-action=\'deletethread\'>Yes I\'m sure</button> <button class=\'btn btn-xs btn-info closepo\'>No</button>"><i class="glyphicon glyphicon-trash"></i></button>';
			}
		}
		
	?>

		<div class="col-lg-12">
			<legend><?php echo $threads['title']; ?> <button class="btn btn-primary btn-sm pull-right" data-action="addnewpost" data-toggle="modal" data-loadmodal="newpost" data-id="<?php echo $threads['posts'][0]['thread']; ?>">Reply to thread</button><?php echo $edit . '' . $delete; ?></legend>
			<ol class="breadcrumb">
				<li><a href="<?php echo $this->generateURL('forum'); ?>">Forums</a></li>
				<li><a href="<?php echo $this->generateURL('forum_category', array('category' => $threads['posts'][0]['cid'], 'title' => $threads['cattitle'])); ?>"><?php echo $threads['ctitle']; ?></a></li>
				<li><a href="<?php echo $this->generateURL('forum_thread', array('thread' => $threads['posts'][0]['thread'], 'title' => $threads['urltitle'])); ?>"><?php echo $threads['title']; ?></a></li>
			</ol>
		</div>

		<div class="clearfix"></div>
		<?php
		if( $threads['total'] > $this->pagepostlimit ):
		?>
			<div class="col-lg-12">
				<ul class="pager">
					<?php echo ( $threads['page'] > 1 ? '<li class="previous"><a href="' . $this->generateURL('forum_thread', array('thread' => $threads['posts'][0]['thread'], 'page' => ( $threads['page'] - 1 ))) . '">&larr; Older</a></li>' : '' ) ?>
				  	<li>
						<ul class="pagination">
						  	<?php
						  		for( $i = 1; $i <= ceil( $threads['total'] / $this->pagepostlimit ); $i++ )
						  		{ 
						  			echo '<li ' . ( $threads['page'] == $i ? 'class="active"' : '' ) . '><a href="' . $this->generateURL('forum_thread', array('thread' => $threads['posts'][0]['thread'], 'page' => $i, 'title' => $threads['posts'][0]['title'])) . '">' . $i . '</a></li>';
						  		}
						  	?>
						</ul>
					</li>
					<?php echo ( $threads['page'] < ceil( $threads['total'] / $this->pagepostlimit ) ? '<li class="next"><a href="' . $this->generateURL('forum_thread', array('thread' => $threads['posts'][0]['thread'], 'page' => ( $threads['page'] + 1 ))) . '">Newer &rarr;</a></li>' : '' ) ?>
				</ul>
			</div>
		<?php
		endif;
		?>

		<div class="clearfix"></div>

		<?php
			foreach( $threads['posts'] as $thread )
			{
				$edit = '';
				$delete = '';
				if( $this->permission('admin') || $_SESSION['data']['uid'] == $thread['uid'] )
				{
					$edit = '<button class="btn btn-warning btn-sm showtooltip" title="Edit Post" data-action="editpost" data-toggle="modal" data-loadmodal="editpost" data-id="' . $thread['id'] . '"><i class="glyphicon glyphicon-edit"></i></button>';
				}
				if( $this->permission('admin') && $thread['op'] != $thread['id'] )
				{
					$delete = '<button class="btn btn-sm btn-danger showtooltip" title="Delete Post?" data-placement="left" data-toggle="popover" data-title="<b>Are you sure?</b>" data-content="<button class=\'btn btn-danger btn-xs funcdelete\' data-id=\'' . $thread['id'] . '\' data-action=\'deletepost\'>Yes I\'m sure</button> <button class=\'btn btn-xs btn-info closepo\'>No</button>"><i class="glyphicon glyphicon-trash"></i></button>';
				}

				$avatar = $this->avatarurl($thread['avatar']);
				echo '<div id="post' . $thread['id'] . '" class="threadpost col-lg-12">
						<div class="byuser pull-left">
							<img src="' . $avatar . '" alt="track beast" class="threadavatar">
						</div>
						<div class="postdetails pull-left">
							<div class="postpadding">
								<h4><a href="#">' . $thread['username'] . '</a> <small>wrote</small> <small class="pull-right">' . $this->timeSince($thread['date']) . ' ' . $delete . $edit . '<button class="btn btn-default btn-sm showtooltip" data-action="addnewpost" data-toggle="modal" data-loadmodal="newpostquote" data-id="' . $thread['id'] . '" title="Quote Reply"><i class="glyphicon glyphicon-share-alt"></i></button></small></h4>
								<div class="clearfix"></div>
								' . Security::sanitize($thread['body'], 'string') . '
							</div>
						</div>
						<div class="clearfix"></div>
					</div>

					<div class="clearfix"></div>';
			}


		if( $threads['total'] > $this->pagepostlimit ):
		?>
			<div class="col-lg-12">
				<ul class="pager">
					<?php echo ( $threads['page'] > 1 ? '<li class="previous"><a href="' . $this->generateURL('forum_thread', array('thread' => $threads['posts'][0]['thread'], 'page' => ( $threads['page'] - 1 ))) . '">&larr; Older</a></li>' : '' ) ?>
				  	<li>
						<ul class="pagination">
						  	<?php
						  		for( $i = 1; $i <= ceil( $threads['total'] / $this->pagepostlimit ); $i++ )
						  		{ 
						  			echo '<li ' . ( $threads['page'] == $i ? 'class="active"' : '' ) . '><a href="' . $this->generateURL('forum_thread', array('thread' => $threads['posts'][0]['thread'], 'page' => $i, 'title' => $threads['posts'][0]['title'])) . '">' . $i . '</a></li>';
						  		}
						  	?>
						</ul>
					</li>
					<?php echo ( $threads['page'] < ceil( $threads['total'] / $this->pagepostlimit ) ? '<li class="next"><a href="' . $this->generateURL('forum_thread', array('thread' => $threads['posts'][0]['thread'], 'page' => ( $threads['page'] + 1 ))) . '">Newer &rarr;</a></li>' : '' ) ?>
				</ul>
			</div>
		<?php
		endif;

	endif;
	?>

	</div>
</div>