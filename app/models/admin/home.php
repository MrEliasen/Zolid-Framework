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

class ModelsAdminHome extends AppModel
{
	/**
	 * Gets the statistical information we need from the database.
	 * 
	 * @return array
	 */
	public function getStatistics()
    {
		$today = mktime(0, 0, 1, date('m'), date('d'), date('Y'));
		$stmt = $this->connection->prepare('SELECT COUNT(id) as usertotal, ( SELECT COUNT(id) FROM ' . Configure::get('database/prefix') . 'accounts WHERE created >= :date) as userstoday FROM ' . Configure::get('database/prefix') . 'accounts');
		$stmt->bindValue(':date', $today, PDO::PARAM_INT);
		$stmt->execute();
		$stats = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		return $stats;
	}
}