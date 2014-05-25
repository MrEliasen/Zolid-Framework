<?php

class PluginMembergalleryModelsHome extends AppModel
{
	public function getMembers($offset, $limit)
	{
		$stmt = $this->connection->prepare('SELECT
												id,
												username,
												avatar
											FROM 
												' . Configure::get('database/prefix') . 'accounts
											ORDER BY
												id ASC
											LIMIT
												' . $offset . ', ' . $limit);
		$stmt->execute();
		$list = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		return $list;
	}
}