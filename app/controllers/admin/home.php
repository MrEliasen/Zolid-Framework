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

class ControllersAdminHome extends AppController
{
	/**
	 * Loads from default statistics for the number of accounts in the system.
	 * 
	 * @return array
	 */
	public function getStatistics()
	{
		return $this->model->getStatistics();
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
            'priority' => 0,
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
	            $output['priority'] = Security::sanitize($versiondata['priority'], 'string');
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