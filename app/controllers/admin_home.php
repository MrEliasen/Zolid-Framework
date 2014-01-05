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

class admin_home extends AppController
{
	/**
	 * Loads from default statistics for the number of accounts in the system.
	 * 
	 * @return array
	 */
	public function getStatistics()
	{
		$today = mktime(0, 0, 1, date('m'), date('d'), date('Y'));
		$stmt = $this->model->connection->prepare('SELECT COUNT(id) as usertotal, ( SELECT COUNT(id) FROM ' . Configure::get('database/prefix') . 'accounts WHERE created >= :date) as userstoday FROM ' . Configure::get('database/prefix') . 'accounts');
		$stmt->bindValue(':date', $today, PDO::PARAM_INT);
		$stmt->execute();
		$stats = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt->closeCursor();

		return $stats;
	}

	/**
	 * Checks if any new version is available for the Zolid Framework.
	 * 
	 * @return array
	 */
	public function checkVersion()
    {
        $output = array(
            'current' => ZF_VERSION,
            'latest' => 'Unknown',
            'upgrade' => false,
            'release' => null,
            'message' => ''
        );

        $versiondata = file_get_contents('https://raw.github.com/MrEliasen/Zolid-Framework/master/latestversion', null, stream_context_create(array('http' => array('timeout' => 5))));

        if( !empty($versiondata) )
        {
            $versiondata = @json_decode($versiondata, true);
            if( !empty($versiondata) )
            {
	            if( version_compare($output['current'], $versiondata['version'], '<') )
	            {
	                $output['upgrade'] = true;
	            }

	            $output['latest'] = Security::sanitize($versiondata['version'], 'mixedint');
	            $output['release'] = Security::sanitize($versiondata['date'], 'integer');
	            $output['message'] = Security::sanitize($versiondata['message'], 'string');
            }
            else
            {
            	$output['latest'] = 'Failed (invalid response)';
            } 
      	}
        else
        {
            $output['latest'] = 'Failed (timeout)';
        }

        return $output;
    }
}