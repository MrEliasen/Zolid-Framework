<?php
/**
 *  Zolid Framework - MIT licensed
 *  https://github.com/MrEliasen/Zolid-Framework
 *
 *  A class which handles the forum bits
 *
 *  @author     Mark Eliasen
 *  @website    www.zolidsolutions.com
 *  @copyright  (c) 2013 - Mark Eliasen
 *  @version    0.1.5
 */

if( !defined('CORE_PATH') )
{
    die('Direct file access not allowed.');
}

class Forum extends Template
{
	protected $pagepostlimit = 8;
	protected $pagethreadlimit = 10;

	protected function loadForumCategories()
	{
		if( !$this->permission('loggedin') )
		{
			return array(
				'status' => false,
				'message' => $this->lang['core']['classes']['forum']['loggedin_err']
			);
		}

		$stmt = $this->sql->query('SELECT 
										c.*,
										t.title as threadtitle,
										t.id as tid,
										p.body,
										p.date,
										u.username,
										( SELECT COUNT(*) FROM forum_threads WHERE category = c.id ) as threads,
										( SELECT COUNT(*) FROM forum_posts WHERE thread IN ( SELECT id FROM forum_threads WHERE category = c.id ) ) as posts,
										( SELECT COUNT(*) FROM forum_posts WHERE thread = t.id ) as lastpage
									FROM
										forum_categories as c
									LEFT JOIN
										forum_threads as t
									ON
										t.id = ( SELECT id FROM forum_threads WHERE category = c.id ORDER BY id DESC LIMIT 1 )
									LEFT JOIN
										forum_posts as p 
									ON
										p.id = ( SELECT id FROM forum_posts WHERE thread = t.id ORDER BY id DESC LIMIT 1 )
									LEFT JOIN
										users as u
									ON
										u.id = p.uid
									ORDER BY
										c.sort
									ASC');
		$stmt->execute();
		$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		$list = array();
		if( !empty($categories) )
		{
			foreach( $categories as $key => $value )
			{
				$value['lastpage'] = ceil($value['lastpage'] / $this->pagepostlimit);
				$list[ $key ] = $value;
			}
		}

		return $list;
	}

	protected function getThread()
	{
		if( !$this->permission('loggedin') )
		{
			return array(
				'status' => false,
				'message' => $this->lang['core']['classes']['forum']['loggedin_err']
			);
		}

		if( empty($_GET['thread']) )
		{
			return '';
		}

		$page = 1;
		if( !empty($_GET['page']) )
		{
			$_GET['page'] = Security::sanitize($_GET['page'], 'integer');
			if( $_GET['page'] > 0)
			{
				$page = $_GET['page'];
			}
		}

		$stmt = $this->sql->prepare('SELECT 
										p.*,
										t.title,
										t.category as cid,
										t.uid as owner,
										t.op,
										t.views,
										u.username,
										u.email,
										c.title as category,
										( SELECT COUNT(p2.id) FROM forum_posts as p2 WHERE p2.thread = p.thread ) as postcount
									FROM
										forum_posts as p 
									INNER JOIN
										forum_threads as t
									ON
										t.id = p.thread
									LEFT JOIN
										users as u
									ON
										u.id = p.uid
									LEFT JOIN
										forum_categories as c
									ON
										c.id = t.category
									WHERE
										p.thread = :tid
									ORDER BY
										p.id
									ASC
									LIMIT
										' . ( empty($page) || $page <= 1 ? '' : ':page, ' ) . '8' );

		$stmt->bindValue(':tid', $_GET['thread'], PDO::PARAM_INT);
		if( !empty($page) && $page > 1 )
		{			$stmt->bindValue(':page', ( $page * 8 - 8 ), PDO::PARAM_INT);
		}

		$stmt->execute();
		$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		$list = array('title' => $posts[0]['title'], 'views' => $posts[0]['views'], 'urltitle' => '', 'cattitle' => '', 'ctitle' => $posts[0]['category'], 'total' => $posts[0]['postcount'], 'page' => $page, 'posts' => array());
		if( !empty($posts) )
		{
			// add 1 view to the thread if the user have not viewed it this session
			if( !is_array($_SESSION['forum']['viewed']) || !in_array($posts[0]['thread'], $_SESSION['forum']['viewed']))
			{
				$stmt = $this->sql->prepare('UPDATE forum_threads SET views = views + 1 WHERE id = ?');
				$stmt->execute(array($posts[0]['thread']));
				$stmt->closeCursor();

				if( $stmt->rowCount() )
				{
					$_SESSION['forum']['viewed'][] = $posts[0]['thread'];
				}
			}

			$list['urltitle'] = str_replace(' ', '-', $posts[0]['title']);
			$list['urltitle'] = Security::sanitize($list['urltitle'], 'page');
			$list['urltitle'] = urlencode($list['urltitle']);

			$list['cattitle'] = str_replace(' ', '-', $posts[0]['category']);
			$list['cattitle'] = Security::sanitize($list['cattitle'], 'page');
			$list['cattitle'] = urlencode($list['cattitle']);

			foreach( $posts as $key => $value )
			{
				$list['posts'][] = $value;
			}
		}

		return $list;
	}

	protected function loadForumThreads()
	{
		if( !$this->permission('loggedin') )
		{
			return array(
				'status' => false,
				'message' => $this->lang['core']['classes']['forum']['loggedin_err']
			);
		}

		if( empty($_GET['category']) )
		{
			return '';
		}

		$page = 1;
		if( !empty($_GET['page']) )
		{
			$_GET['page'] = Security::sanitize($_GET['page'], 'integer');
			if( $_GET['page'] > 0)
			{
				$page = $_GET['page'];
			}
		}

		$stmt = $this->sql->prepare('SELECT 
										c.*,
										t.title as threadtitle,
										t.id as tid,
										t.views,
										p.date,
										LEFT(p.body, 63) as body,
										u.username,
										u2.username as replyby,
										p3.date as replydate,
										( SELECT COUNT(p2.id) FROM forum_posts as p2 WHERE p2.thread = p.thread ) as postcount,
										( SELECT COUNT(t2.id) FROM forum_threads as t2 WHERE t2.category = c.id ) as threadcount
									FROM
										forum_categories as c 
									LEFT JOIN
										forum_threads as t
									ON
										t.category = c.id
									LEFT JOIN
										forum_posts as p
									ON
										p.id = t.op
									LEFT JOIN
										users as u
									ON
										u.id = p.uid
									LEFT JOIN
										forum_posts as p3
									ON
										p3.id = ( SELECT p4.id FROM forum_posts as p4 WHERE p4.thread = t.id ORDER BY id DESC LIMIT 1 )
									LEFT JOIN
										users as u2
									ON
										u2.id = p3.uid
									WHERE
										c.id = :cid
									ORDER BY
										p.date
									DESC
									LIMIT
										' . ( empty($page) || $page <= 1 ? '' : ':page, ' ) . $this->pagethreadlimit );

		$stmt->bindValue(':cid', $_GET['category'], PDO::PARAM_INT);
		if( !empty($page) && $page > 1 )
		{			$stmt->bindValue(':page', ( $page * $this->pagethreadlimit - $this->pagethreadlimit ), PDO::PARAM_INT);
		}

		$stmt->execute();
		$threads = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();
		
		$list = array('title' => $threads[0]['title'], 'views' => $threads[0]['views'], 'urltitle' => '', 'description' => $threads[0]['description'], 'total' => $threads[0]['threadcount'], 'page' => $page, 'threads' => array());
		if( !empty($threads) )
		{
			$list['urltitle'] = str_replace(' ', '-', $list['title']);
			$list['urltitle'] = Security::sanitize($list['urltitle'], 'page');
			$list['urltitle'] = urlencode($list['urltitle']);

			foreach( $threads as $key => $value )
			{
				$list['threads'][] = $value;
			}
		}

		return $list;
	}

	protected function addNewPost()
	{
		if( !$this->permission('loggedin') )
		{
			return json_encode(array(
				'status' => false,
				'message' => $this->lang['core']['classes']['forum']['loggedin_err']
			));
		}

		if( empty($_POST['tid']) )
		{
			return json_encode(array(
				'status' => false,
				'message' => $this->lang['core']['classes']['forum']['addpost_err1']
			));
		}

		if( empty($_POST['body']) || strlen(strip_tags($_POST['body'])) < 10 )
		{
			return json_encode(array(
				'status' => false,
				'message' => $this->lang['core']['classes']['forum']['addpost_err2']
			));
		}

		// Check CSRF token
		if( !Security::csrfCheck('addnewpost') )
		{
			return json_encode(array(
				'status' => false,
				'message' => $this->lang['core']['classes']['forum']['addpost_err3']
			));
		}

		$stmt = $this->sql->prepare('INSERT INTO forum_posts (body, date, thread, uid) VALUES (:body, :date, :tid, :uid)');
		$stmt->bindValue(':body', Security::sanitize($_POST['body'], 'string'), PDO::PARAM_STR);
		$stmt->bindValue(':date', time(), PDO::PARAM_INT);
		$stmt->bindValue(':tid', Security::sanitize($_POST['tid'], 'integer'), PDO::PARAM_INT);
		$stmt->bindValue(':uid', Security::sanitize($_SESSION['data']['uid'], 'integer'), PDO::PARAM_INT);
		$success = $stmt->execute();
		$stmt->closeCursor();

		if( $success )
		{
			$newid = $this->sql->lastInsertId();

			$stmt = $this->sql->prepare('SELECT COUNT(forum_posts.id) as total, title, thread FROM forum_posts INNER JOIN forum_threads ON forum_threads.id = thread WHERE thread = :tid');
			$stmt->bindValue(':tid', Security::sanitize($_POST['tid'], 'integer'), PDO::PARAM_INT);
			$stmt->execute();
			$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$stmt->closeCursor();

			$urltitle = str_replace(' ', '-', $posts[0]['title']);
			$urltitle = Security::sanitize($urltitle, 'page');
			$urltitle = urlencode($urltitle);

			$sendto =  $this->base_url . '/forum/thread/' . $posts[0]['thread'] . '/' . $urltitle . '/' . ceil( $posts[0]['total'] / $this->pagepostlimit ) . '#post' . $newid;

			return json_encode(array(
				'status' => true,
				'message' => $this->lang['core']['classes']['forum']['addpost_success'],
				'sendto' => $sendto,
				'page' => ceil( $posts[0]['total'] / $this->pagepostlimit )
			));
		}
		else
		{
			return json_encode(array(
				'status' => false,
				'message' => $this->lang['core']['classes']['forum']['addpost_err4']
			));
		}
	}

	protected function addNewThread()
	{
		if( !$this->permission('loggedin') )
		{
			return json_encode(array(
				'status' => false,
				'message' => $this->lang['core']['classes']['forum']['loggedin_err']
			));
		}

		if( empty($_POST['cid']) )
		{
			return json_encode(array(
				'status' => false,
				'message' => $this->lang['core']['classes']['forum']['addthread_err1']
			));
		}

		if( empty($_POST['title']) || strlen(strip_tags($_POST['title'])) < 5 )
		{
			return json_encode(array(
				'status' => false,
				'message' => $this->lang['core']['classes']['forum']['addthread_err2']
			));
		}

		if( empty($_POST['body']) || strlen(strip_tags($_POST['body'])) < 10 )
		{
			return json_encode(array(
				'status' => false,
				'message' => $this->lang['core']['classes']['forum']['addthread_err3']
			));
		}

		// Check CSRF token
		if( !Security::csrfCheck('addnewthread') )
		{
			return json_encode(array(
				'status' => false,
				'message' => $this->lang['core']['classes']['forum']['addthread_err4']
			));
		}

		// Check if category is admin only
		$stmt = $this->sql->prepare('SELECT admin FROM forum_categories WHERE id = :cid');
		$stmt->bindValue(':cid', Security::sanitize($_POST['cid'], 'integer'), PDO::PARAM_INT);
		$stmt->execute();
		$admin = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		if( !empty($admin[0]['admin']) && !$this->permission('admin') )
		{
			return json_encode(array(
				'status' => false,
				'message' => $this->lang['core']['classes']['forum']['addthread_err5']
			));
		}


		$stmt = $this->sql->prepare('INSERT INTO forum_threads (title, uid, category) VALUES (:title, :uid, :category)');
		$stmt->bindValue(':title', Security::sanitize($_POST['title'], 'purestring'), PDO::PARAM_STR);
		$stmt->bindValue(':uid', $_SESSION['data']['uid'], PDO::PARAM_INT);
		$stmt->bindValue(':category', Security::sanitize($_POST['cid'], 'integer'), PDO::PARAM_INT);
		$stmt->execute();
		$stmt->closeCursor();

		$newid = $this->sql->lastInsertId();

		if( $newid > 0 )
		{
			$stmt = $this->sql->prepare('INSERT INTO forum_posts (body, date, thread, uid) VALUES (:body, :date, :tid, :uid)');
			$stmt->bindValue(':body', Security::sanitize($_POST['body'], 'string'), PDO::PARAM_STR);
			$stmt->bindValue(':date', time(), PDO::PARAM_INT);
			$stmt->bindValue(':tid', $newid, PDO::PARAM_INT);
			$stmt->bindValue(':uid', Security::sanitize($_SESSION['data']['uid'], 'integer'), PDO::PARAM_INT);
			$success = $stmt->execute();
			$stmt->closeCursor();

			$newpostid = $this->sql->lastInsertId();

			if( $success )
			{
				$this->sql->exec('UPDATE forum_threads SET op = ' . $newpostid . ' WHERE id = ' . $newid );

				$urltitle = str_replace(' ', '-', Security::sanitize($_POST['title'], 'purestring'));
				$urltitle = Security::sanitize($urltitle, 'page');
				$urltitle = urlencode($urltitle);

				$sendto =  $this->base_url . '/forum/thread/' . $newid . '/' . $urltitle;

				return json_encode(array(
					'status' => true,
					'message' => $this->lang['core']['classes']['forum']['addthread_success'],
					'sendto' => $sendto
				));
			}
			else
			{
				$this->sql->exec('DELETE FROM forum_threads WHERE id = ' . $newid);
			}
		}

		return json_encode(array(
			'status' => false,
			'message' => $this->lang['core']['classes']['forum']['addthread_err6']
		));
	}

	protected function updatePost()
	{
		if( !$this->permission('loggedin') )
		{
			return json_encode(array(
				'status' => false,
				'message' => $this->lang['core']['classes']['forum']['loggedin_err']
			));
		}

		if( empty($_POST['pid']) )
		{
			return json_encode(array(
				'status' => false,
				'message' => $this->lang['core']['classes']['forum']['updatepost_err1']
			));
		}

		if( empty($_POST['body']) || strlen(strip_tags($_POST['body'])) < 10 )
		{
			return json_encode(array(
				'status' => false,
				'message' => $this->lang['core']['classes']['forum']['updatepost_err2']
			));
		}

		// Check CSRF token
		if( !Security::csrfCheck('editpost') )
		{
			return json_encode(array(
				'status' => false,
				'message' => $this->lang['core']['classes']['forum']['updatepost_err3']
			));
		}

		$stmt = $this->sql->prepare('UPDATE forum_posts SET body = :body, edit = :now, editby = :uid WHERE id = :pid ' . ( !$this->permission('admin') ? 'AND uid = :uid2' : '' ) );
		$stmt->bindValue(':body', Security::sanitize($_POST['body'], 'string'), PDO::PARAM_STR);
		$stmt->bindValue(':now', time(), PDO::PARAM_INT);
		$stmt->bindValue(':uid', Security::sanitize($_SESSION['data']['uid'], 'integer'), PDO::PARAM_INT);
		$stmt->bindValue(':pid', Security::sanitize($_POST['pid'], 'integer'), PDO::PARAM_INT);
		if( !$this->permission('admin') )
		{
			$stmt->bindValue(':uid2', Security::sanitize($_SESSION['data']['uid'], 'integer'), PDO::PARAM_INT);
		}
		$success = $stmt->execute();
		$stmt->closeCursor();

		if( $success )
		{
			return json_encode(array(
				'status' => true,
				'message' => $this->lang['core']['classes']['forum']['updatepost_success'],
				'sendto' => 'force'
			));
		}
		else
		{
			return json_encode(array(
				'status' => false,
				'message' => $this->lang['core']['classes']['forum']['updatepost_err4']
			));
		}
	}

	protected function updateThread()
	{
		if( !$this->permission('loggedin') )
		{
			return json_encode(array(
				'status' => false,
				'message' => $this->lang['core']['classes']['forum']['loggedin_err']
			));
		}

		if( empty($_POST['tid']) || empty($_POST['pid']) )
		{
			return json_encode(array(
				'status' => false,
				'message' => $this->lang['core']['classes']['forum']['updatethread_err1']
			));
		}

		if( empty($_POST['title']) || strlen(strip_tags($_POST['title'])) < 5 )
		{
			return json_encode(array(
				'status' => false,
				'message' => $this->lang['core']['classes']['forum']['updatethread_err2']
			));
		}

		if( empty($_POST['body']) || strlen(strip_tags($_POST['body'])) < 10 )
		{
			return json_encode(array(
				'status' => false,
				'message' => $this->lang['core']['classes']['forum']['updatethread_err3']
			));
		}

		// Check CSRF token
		if( !Security::csrfCheck('editthread') )
		{
			return json_encode(array(
				'status' => false,
				'message' => $this->lang['core']['classes']['forum']['updatethread_err4']
			));
		}

		$stmt = $this->sql->prepare('UPDATE forum_threads SET title = :title WHERE id = :tid ' . ( !$this->permission('admin') ? 'AND uid = :uid' : '' ) );
		$stmt->bindValue(':title', Security::sanitize($_POST['title'], 'purestring'), PDO::PARAM_STR);
		$stmt->bindValue(':tid', Security::sanitize($_POST['tid'], 'integer'), PDO::PARAM_INT);
		if( !$this->permission('admin') )
		{
			$stmt->bindValue(':uid', Security::sanitize($_SESSION['data']['uid'], 'integer'), PDO::PARAM_INT);
		}
		$success = $stmt->execute();
		$stmt->closeCursor();

		if( $success )
		{
			$stmt = $this->sql->prepare('UPDATE forum_posts SET body = :body, edit = :time, editby = :edituid WHERE id = :pid AND thread = :tid ' . ( !$this->permission('admin') ? 'AND uid = :uid' : '' ));
			$stmt->bindValue(':edituid', Security::sanitize($_SESSION['data']['uid'], 'integer'), PDO::PARAM_INT);
			$stmt->bindValue(':body', Security::sanitize($_POST['body'], 'string'), PDO::PARAM_STR);
			$stmt->bindValue(':pid', Security::sanitize($_POST['pid'], 'integer'), PDO::PARAM_INT);
			$stmt->bindValue(':time', time(), PDO::PARAM_INT);
			$stmt->bindValue(':tid', Security::sanitize($_POST['tid'], 'integer'), PDO::PARAM_INT);
			if( !$this->permission('admin') )
			{
				$stmt->bindValue(':uid', Security::sanitize($_SESSION['data']['uid'], 'integer'), PDO::PARAM_INT);
			}
			$success = $stmt->execute();
			$stmt->closeCursor();

			return json_encode(array(
				'status' => true,
				'message' => $this->lang['core']['classes']['forum']['updatethread_success'],
				'sendto' => 'force'
			));
		}
		else
		{
			return json_encode(array(
				'status' => false,
				'message' => $this->lang['core']['classes']['forum']['updatethread_err5']
			));
		}
	}

	protected function deletePost()
	{
		if( !$this->permission('admin') )
		{
			return json_encode(array(
				'status' => false,
				'message' => $this->lang['core']['classes']['forum']['loggedin_err']
			));
		}

		if( empty($_POST['id']) )
		{
			return json_encode(array(
				'status' => false,
				'message' => $this->lang['core']['classes']['forum']['deletepost_err1']
			));
		}

		// Check CSRF token
		if( !Security::csrfCheck('usertoken', true) )
		{
			return json_encode(array(
				'status' => false,
				'message' => $this->lang['core']['classes']['forum']['deletepost_err2']
			));
		}

		$stmt = $this->sql->prepare('DELETE FROM forum_posts WHERE id = :pid');
		$stmt->bindValue(':pid', Security::sanitize($_POST['id'], 'integer'), PDO::PARAM_INT);
		$success = $stmt->execute();
		$stmt->closeCursor();

		if( $success )
		{
			return json_encode(array(
				'status' => true,
				'message' => $this->lang['core']['classes']['forum']['deletepost_success'],
				'sendto' => 'force'
			));
		}
		else
		{
			return json_encode(array(
				'status' => false,
				'message' => $this->lang['core']['classes']['forum']['deletepost_err3']
			));
		}
	}

	protected function deleteThread()
	{
		if( !$this->permission('admin') )
		{
			return json_encode(array(
				'status' => false,
				'message' => $this->lang['core']['classes']['forum']['loggedin_err']
			));
		}

		if( empty($_POST['id']) )
		{
			return json_encode(array(
				'status' => false,
				'message' => $this->lang['core']['classes']['forum']['deletethread_err1']
			));
		}

		// Check CSRF token
		if( !Security::csrfCheck('usertoken', true) )
		{
			return json_encode(array(
				'status' => false,
				'message' => $this->lang['core']['classes']['forum']['deletethread_err2']
			));
		}

		$stmt = $this->sql->prepare('DELETE FROM forum_posts WHERE thread = :tid');
		$stmt->bindValue(':tid', Security::sanitize($_POST['id'], 'integer'), PDO::PARAM_INT);
		$stmt->execute();
		$stmt->closeCursor();

		$stmt = $this->sql->prepare('DELETE FROM forum_threads WHERE id = :tid LIMIT 1');
		$stmt->bindValue(':tid', Security::sanitize($_POST['id'], 'integer'), PDO::PARAM_INT);
		$success = $stmt->execute();
		$stmt->closeCursor();

		if( $success )
		{
			return json_encode(array(
				'status' => true,
				'message' => $this->lang['core']['classes']['forum']['deletethread_success'],
				'sendto' => $this->base_url . '/forum'
			));
		}
		else
		{
			return json_encode(array(
				'status' => false,
				'message' => $this->lang['core']['classes']['forum']['deletethread_err3']
			));
		}
	}

	protected function timeSince( $unix )
	{
		$min = 60;
		$hour = 3600;
		$day = 86400;
		
		$diff = time() - $unix;
		$diff2 = $diff;

		$days = floor($diff / $day);
		$days = floor($diff / $day);
		$diff = $diff-($day * $days);
		$hours = floor($diff / $hour);
		$diff = $diff-($hour * $hours);
		$minutes = floor($diff / $min);
		$diff = $diff-($min * $minutes);
		$seconds = $diff;
		
		if($minutes == 1)
		{
			$m = ' Minute';
		}
		else
		{
			$m = ' Minutes';
		}
		
		if($hours == 1)
		{
			$h = ' Hour';
		}
		else
		{
			$h = ' Hours';
		}
		
		if($days == 1)
		{
			$d = ' Day';
		}
		else
		{
			$d = ' Days';
		}

		if($diff2 < 60)
		{
			$timest = $diff . ' Seconds';
		}
		else
		{
			if($minutes >= 1)
			{
				$timest = $minutes . $m;
			}
			if($hours >= 1)
			{
				$timest = $hours . $h;
			}
			if($days >= 1)
			{
				$timest = $days . $d;
			}
			if(!isset($timest))
			{
				$timest = '';
			}
		}

		if($timest == '')
		{
			$timest = 'Just a second';
		}
		
		return $timest . ' ago';
	}
}