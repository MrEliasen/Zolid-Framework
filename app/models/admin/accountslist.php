<?php
/**
 *  Zolid Framework
 *  https://github.com/MrEliasen/Zolid-Framework
 *  
 *  @author 	Mark Eliasen (mark.eliasen@zolidsolutions.com)
 *  @copyright 	Copyright (c) 2014, Mark Eliasen
 *  @version    0.1.6.1
 *  @license 	http://opensource.org/licenses/MIT MIT License
 */

class ModelsAdminAccountslist extends AppModel
{
	/**
	 * Gets the list of accounts.
	 * 
	 * @return array
	 */
	public function getAccounts($offset, $limit)
	{
		$stmt = $this->connection->prepare('SELECT
												a.id,
												username,
												expire
											FROM 
												' . Configure::get('database/prefix') . 'accounts as a
											LEFT JOIN
												' . Configure::get('database/prefix') . 'sessions as s
												ON
													s.id = a.sessid
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