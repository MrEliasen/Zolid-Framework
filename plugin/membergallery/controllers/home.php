<?php

class PluginMembergalleryControllersHome extends PluginMembergalleryController
{
	public function getMembersList()
	{
		$page = Security::sanitize(Misc::data('page', 'get'), 'integer');
		$limit = 20;
		$offset = ( $page < 1 ? 1 : $page ) * $limit - $limit;

		$list = $this->model->getMembers($offset, $limit);

		$users = array();
		foreach( $list as $user )
		{
			$users[] = array(
				'username' => $user['username'],
				'id' => $user['id'],
				'avatar' => $this->avatarurl($user['avatar']),
				'profileurl' => $this->makeUrl('users/profile') . '&id=' . $user['id'] 
			);
		}

		return $users;
	}
}