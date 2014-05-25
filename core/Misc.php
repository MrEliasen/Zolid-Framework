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

// prevent direct file access
if( !defined('ROOTPATH') )
{
    die();
}

class Misc
{
	// Protected constructor to prevent instance creation
	protected function __construct()
	{
		
	}

	/**
	 * Convert a string to "CamelCase" from "file_case"
	 * 
	 * @param string $str the string to convert
	 * @return string the converted string
	 */
	public static function toCamelCase( $str )
	{
		$str = ucwords(str_replace('/', ' ', $str));
		return str_replace(' ', '', $str);
	}

	/**
	 * Convert a string from "CamelCase" to "file/path"
	 * 
	 * @param string $str the string to convert
	 * @return string the converted string
	 */
	public static function camelCaseToFilePath( $str )
	{
		static $cctfp_func = null;
		if( $cctfp_func == null )
		{
			$cctfp_func = create_function('$c', 'return "/" . strtolower($c[1]);');
		}

		if( isset($str[0]) )
		{
			$str[0] = strtolower($str[0]);
		}

		return preg_replace_callback('/([A-Z])/', $cctfp_func, $str);
	}

	/**
	 * Will return a human understandable timestamp of how long time it has been since the $date.
	 * 
	 * @param integer $date The UNIX timestamp from a date you want to calculate the time since.
	 * @return string A string with the human readable timestamp, eg. "13 hours ago".
	 */
    public static function timeSince( $date )
    {
    	$min = 60;
		$hour = 3600;
		$day = 86400;
		
		$diff = time() - $date;
		$diff2 = $diff;

		$days = floor($diff / $day);
		$days = floor($diff / $day);
		$diff = $diff-($day * $days);
		$hours = floor($diff / $hour);
		$diff = $diff-($hour * $hours);
		$minutes = floor($diff / $min);
		$diff = $diff-($min * $minutes);
		$seconds = $diff;
		$timest = '';

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
		}

		if( empty($timest) )
		{
			$timest = 'Just a second';
		}
		
		return $timest . ' ago';
	}

	/**
	 * Will check if all the $fields has been submitted/recieved. Helps to easily check if all required form fields has been filled out.
	 *  
	 * @param  string $type   The method the fields was submitted as, POST, GET, COOKIE etc.
	 * @param  string  $fields An array with the names of all the fields you wish to check.
	 * @return boolean returns true if the fields are received and not empty, else returns false.
	 */
	public static function receivedFields( $fields, $type = 'request')
	{
		switch( strtolower($type) )
		{
			case 'post':
				$received = $_REQUEST;
				break;
			
			case 'get':
				$received = $_GET;
				break;

			case 'request':
			default:
				$received = $_REQUEST;
				break;
		}

		$result = true;
		foreach( explode(',', $fields) as $key )
		{
			if( empty($received[trim($key)]) )
			{
				$result = false;
			}
		}

		// clear from memory, just in case.
		unset($received);

		return $result;
	}

	/**
	 * Gets the value from $method[ $var ]. Returns null if not found.
	 * 
	 * @param  string $var    The key for the value you wish to retrive.
	 * @param  string $method POST, GET or REQUEST. defaults to $_REQUEST.
	 * @return mixed          
	 */
	public static function data( $var, $method = 'request' )
	{
		switch( $method )
		{
			case 'post':
				$method = $_POST;
				break;

			case 'get':
				$method = $_GET;
				break;

			default:
				$method = $_REQUEST;
				break;
		}

		if( !isset($method[$var]) )
		{
			return null;
		}

		return $method[$var];
	}

	/**
	 * Checks if the maintenane flag is set and if the user's ip is not on the "allowed" list.
	 * 
	 * @return boolean True if the maintenance should apply to the user, false if the user can ignore the maintenance.
	 */
	public static function maintenance()
	{
		Configure::load('core');

		if( Configure::get('core/maintenance') && ( empty($_SERVER['REMOTE_ADDR']) || !in_array($_SERVER['REMOTE_ADDR'], explode(',', Configure::get('core/allowed_ips'))) ) )
		{
			return true;
		}

		return false;
	}

	/**
	 * Get the list of countries and ISO code.
	 * 
	 * @param  boolean $reverse will reverse the key and value from ISO => COUNTRY to COUNTRY => ISO
	 * @return array The list of countries and iso codes as a php array
	 */
	public static function countries( $reverse = false )
	{
		$data = array(
			'AD' => 'Andorra',
			'AE' => 'United Arab Emirates',
			'AF' => 'Afghanistan',
			'AG' => 'Antigua &amp; Barbuda',
			'AI' => 'Anguilla',
			'AL' => 'Albania',
			'AM' => 'Armenia',
			'AN' => 'Netherlands Antilles',
			'AO' => 'Angola',
			'AQ' => 'Antarctica',
			'AR' => 'Argentina',
			'AS' => 'American Samoa',
			'AT' => 'Austria',
			'AU' => 'Australia',
			'AW' => 'Aruba',
			'AZ' => 'Azerbaijan',
			'BA' => 'Bosnia and Herzegovina',
			'BB' => 'Barbados',
			'BD' => 'Bangladesh',
			'BE' => 'Belgium',
			'BF' => 'Burkina Faso',
			'BG' => 'Bulgaria',
			'BH' => 'Bahrain',
			'BI' => 'Burundi',
			'BJ' => 'Benin',
			'BM' => 'Bermuda',
			'BN' => 'Brunei Darussalam',
			'BO' => 'Bolivia',
			'BR' => 'Brazil',
			'BS' => 'Bahama',
			'BT' => 'Bhutan',
			'BV' => 'Bouvet Island',
			'BW' => 'Botswana',
			'BY' => 'Belarus',
			'BZ' => 'Belize',
			'CA' => 'Canada',
			'CC' => 'Cocos (Keeling) Islands',
			'CF' => 'Central African Republic',
			'CG' => 'Congo',
			'CH' => 'Switzerland',
			'CI' => 'Côte D\'ivoire (Ivory Coast)',
			'CK' => 'Cook Iislands',
			'CL' => 'Chile',
			'CM' => 'Cameroon',
			'CN' => 'China',
			'CO' => 'Colombia',
			'CR' => 'Costa Rica',
			'CU' => 'Cuba',
			'CV' => 'Cape Verde',
			'CX' => 'Christmas Island',
			'CY' => 'Cyprus',
			'CZ' => 'Czech Republic',
			'DE' => 'Germany',
			'DJ' => 'Djibouti',
			'DK' => 'Denmark',
			'DM' => 'Dominica',
			'DO' => 'Dominican Republic',
			'DZ' => 'Algeria',
			'EC' => 'Ecuador',
			'EE' => 'Estonia',
			'EG' => 'Egypt',
			'EH' => 'Western Sahara',
			'ER' => 'Eritrea',
			'ES' => 'Spain',
			'ET' => 'Ethiopia',
			'FI' => 'Finland',
			'FJ' => 'Fiji',
			'FK' => 'Falkland Islands (Malvinas)',
			'FM' => 'Micronesia',
			'FO' => 'Faroe Islands',
			'FR' => 'France',
			'FX' => 'France, Metropolitan',
			'GA' => 'Gabon',
			'GB' => 'United Kingdom (Great Britain)',
			'GD' => 'Grenada',
			'GE' => 'Georgia',
			'GF' => 'French Guiana',
			'GH' => 'Ghana',
			'GI' => 'Gibraltar',
			'GL' => 'Greenland',
			'GM' => 'Gambia',
			'GN' => 'Guinea',
			'GP' => 'Guadeloupe',
			'GQ' => 'Equatorial Guinea',
			'GR' => 'Greece',
			'GS' => 'South Georgia and the South Sandwich Islands',
			'GT' => 'Guatemala',
			'GU' => 'Guam',
			'GW' => 'Guinea-Bissau',
			'GY' => 'Guyana',
			'HK' => 'Hong Kong',
			'HM' => 'Heard &amp; McDonald Islands',
			'HN' => 'Honduras',
			'HR' => 'Croatia',
			'HT' => 'Haiti',
			'HU' => 'Hungary',
			'ID' => 'Indonesia',
			'IE' => 'Ireland',
			'IL' => 'Israel',
			'IN' => 'India',
			'IO' => 'British Indian Ocean Territory',
			'IQ' => 'Iraq',
			'IR' => 'Islamic Republic of Iran',
			'IS' => 'Iceland',
			'IT' => 'Italy',
			'JM' => 'Jamaica',
			'JO' => 'Jordan',
			'JP' => 'Japan',
			'KE' => 'Kenya',
			'KG' => 'Kyrgyzstan',
			'KH' => 'Cambodia',
			'KI' => 'Kiribati',
			'KM' => 'Comoros',
			'KN' => 'St. Kitts and Nevis',
			'KP' => 'Korea, Democratic People\'s Republic of',
			'KR' => 'Korea, Republic of',
			'KW' => 'Kuwait',
			'KY' => 'Cayman Islands',
			'KZ' => 'Kazakhstan',
			'LA' => 'Lao People\'s Democratic Republic',
			'LB' => 'Lebanon',
			'LC' => 'Saint Lucia',
			'LI' => 'Liechtenstein',
			'LK' => 'Sri Lanka',
			'LR' => 'Liberia',
			'LS' => 'Lesotho',
			'LT' => 'Lithuania',
			'LU' => 'Luxembourg',
			'LV' => 'Latvia',
			'LY' => 'Libyan Arab Jamahiriya',
			'MA' => 'Morocco',
			'MC' => 'Monaco',
			'MD' => 'Moldova, Republic of',
			'MG' => 'Madagascar',
			'MH' => 'Marshall Islands',
			'ML' => 'Mali',
			'MN' => 'Mongolia',
			'MM' => 'Myanmar',
			'MO' => 'Macau',
			'MP' => 'Northern Mariana Islands',
			'MQ' => 'Martinique',
			'MR' => 'Mauritania',
			'MS' => 'Monserrat',
			'MT' => 'Malta',
			'MU' => 'Mauritius',
			'MV' => 'Maldives',
			'MW' => 'Malawi',
			'MX' => 'Mexico',
			'MY' => 'Malaysia',
			'MZ' => 'Mozambique',
			'NA' => 'Namibia',
			'NC' => 'New Caledonia',
			'NE' => 'Niger',
			'NF' => 'Norfolk Island',
			'NG' => 'Nigeria',
			'NI' => 'Nicaragua',
			'NL' => 'Netherlands',
			'NO' => 'Norway',
			'NP' => 'Nepal',
			'NR' => 'Nauru',
			'NU' => 'Niue',
			'NZ' => 'New Zealand',
			'OM' => 'Oman',
			'PA' => 'Panama',
			'PE' => 'Peru',
			'PF' => 'French Polynesia',
			'PG' => 'Papua New Guinea',
			'PH' => 'Philippines',
			'PK' => 'Pakistan',
			'PL' => 'Poland',
			'PM' => 'St. Pierre &amp; Miquelon',
			'PN' => 'Pitcairn',
			'PR' => 'Puerto Rico',
			'PT' => 'Portugal',
			'PW' => 'Palau',
			'PY' => 'Paraguay',
			'QA' => 'Qatar',
			'RE' => 'Réunion',
			'RO' => 'Romania',
			'RU' => 'Russian Federation',
			'RW' => 'Rwanda',
			'SA' => 'Saudi Arabia',
			'SB' => 'Solomon Islands',
			'SC' => 'Seychelles',
			'SD' => 'Sudan',
			'SE' => 'Sweden',
			'SG' => 'Singapore',
			'SH' => 'St. Helena',
			'SI' => 'Slovenia',
			'SJ' => 'Svalbard &amp; Jan Mayen Islands',
			'SK' => 'Slovakia',
			'SL' => 'Sierra Leone',
			'SM' => 'San Marino',
			'SN' => 'Senegal',
			'SO' => 'Somalia',
			'SR' => 'Suriname',
			'ST' => 'Sao Tome &amp; Principe',
			'SV' => 'El Salvador',
			'SY' => 'Syrian Arab Republic',
			'SZ' => 'Swaziland',
			'TC' => 'Turks &amp; Caicos Islands',
			'TD' => 'Chad',
			'TF' => 'French Southern Territories',
			'TG' => 'Togo',
			'TH' => 'Thailand',
			'TJ' => 'Tajikistan',
			'TK' => 'Tokelau',
			'TM' => 'Turkmenistan',
			'TN' => 'Tunisia',
			'TO' => 'Tonga',
			'TP' => 'East Timor',
			'TR' => 'Turkey',
			'TT' => 'Trinidad &amp; Tobago',
			'TV' => 'Tuvalu',
			'TW' => 'Taiwan, Province of China',
			'TZ' => 'Tanzania, United Republic of',
			'UA' => 'Ukraine',
			'UG' => 'Uganda',
			'UM' => 'United States Minor Outlying Islands',
			'US' => 'United States of America',
			'UY' => 'Uruguay',
			'UZ' => 'Uzbekistan',
			'VA' => 'Vatican City State (Holy See)',
			'VC' => 'St. Vincent &amp; the Grenadines',
			'VE' => 'Venezuela',
			'VG' => 'British Virgin Islands',
			'VI' => 'United States Virgin Islands',
			'VN' => 'Viet Nam',
			'VU' => 'Vanuatu',
			'WF' => 'Wallis &amp; Futuna Islands',
			'WS' => 'Samoa',
			'YE' => 'Yemen',
			'YT' => 'Mayotte',
			'YU' => 'Yugoslavia',
			'ZA' => 'South Africa',
			'ZM' => 'Zambia',
			'ZR' => 'Zaire',
			'ZW' => 'Zimbabwe'
		);

		if( $reverse )
		{
			$data = array_flip($data);
		}

		return $data;
	}

	/**
	 * Get the list of timezones and their name/title
	 * 
	 * @param  boolean $reverse If you wish to swap the value and key arround or not.
	 * @return array
	 */
    public static function timezones( $reverse = false )
    {
        $data = array(
            'Pacific/Midway'       => '(GMT-11:00) Midway Island',
            'US/Samoa'             => '(GMT-11:00) Samoa',
            'US/Hawaii'            => '(GMT-10:00) Hawaii',
            'US/Alaska'            => '(GMT-09:00) Alaska',
            'US/Pacific'           => '(GMT-08:00) Pacific Time (US &amp; Canada)',
            'America/Tijuana'      => '(GMT-08:00) Tijuana',
            'US/Arizona'           => '(GMT-07:00) Arizona',
            'US/Mountain'          => '(GMT-07:00) Mountain Time (US &amp; Canada)',
            'America/Chihuahua'    => '(GMT-07:00) Chihuahua',
            'America/Mazatlan'     => '(GMT-07:00) Mazatlan',
            'America/Mexico_City'  => '(GMT-06:00) Mexico City',
            'America/Monterrey'    => '(GMT-06:00) Monterrey',
            'Canada/Saskatchewan'  => '(GMT-06:00) Saskatchewan',
            'US/Central'           => '(GMT-06:00) Central Time (US &amp; Canada)',
            'US/Eastern'           => '(GMT-05:00) Eastern Time (US &amp; Canada)',
            'US/East-Indiana'      => '(GMT-05:00) Indiana (East)',
            'America/Bogota'       => '(GMT-05:00) Bogota',
            'America/Lima'         => '(GMT-05:00) Lima',
            'America/Caracas'      => '(GMT-04:30) Caracas',
            'Canada/Atlantic'      => '(GMT-04:00) Atlantic Time (Canada)',
            'America/La_Paz'       => '(GMT-04:00) La Paz',
            'America/Santiago'     => '(GMT-04:00) Santiago',
            'Canada/Newfoundland'  => '(GMT-03:30) Newfoundland',
            'America/Buenos_Aires' => '(GMT-03:00) Buenos Aires',
            'Greenland'            => '(GMT-03:00) Greenland',
            'Atlantic/Stanley'     => '(GMT-02:00) Stanley',
            'Atlantic/Azores'      => '(GMT-01:00) Azores',
            'Atlantic/Cape_Verde'  => '(GMT-01:00) Cape Verde Is.',
            'Africa/Casablanca'    => '(GMT) Casablanca',
            'Europe/Dublin'        => '(GMT) Dublin',
            'Europe/Lisbon'        => '(GMT) Lisbon',
            'Europe/London'        => '(GMT) London',
            'Africa/Monrovia'      => '(GMT) Monrovia',
            'Europe/Amsterdam'     => '(GMT+01:00) Amsterdam',
            'Europe/Belgrade'      => '(GMT+01:00) Belgrade',
            'Europe/Berlin'        => '(GMT+01:00) Berlin',
            'Europe/Bratislava'    => '(GMT+01:00) Bratislava',
            'Europe/Brussels'      => '(GMT+01:00) Brussels',
            'Europe/Budapest'      => '(GMT+01:00) Budapest',
            'Europe/Copenhagen'    => '(GMT+01:00) Copenhagen',
            'Europe/Ljubljana'     => '(GMT+01:00) Ljubljana',
            'Europe/Madrid'        => '(GMT+01:00) Madrid',
            'Europe/Paris'         => '(GMT+01:00) Paris',
            'Europe/Prague'        => '(GMT+01:00) Prague',
            'Europe/Rome'          => '(GMT+01:00) Rome',
            'Europe/Sarajevo'      => '(GMT+01:00) Sarajevo',
            'Europe/Skopje'        => '(GMT+01:00) Skopje',
            'Europe/Stockholm'     => '(GMT+01:00) Stockholm',
            'Europe/Vienna'        => '(GMT+01:00) Vienna',
            'Europe/Warsaw'        => '(GMT+01:00) Warsaw',
            'Europe/Zagreb'        => '(GMT+01:00) Zagreb',
            'Europe/Athens'        => '(GMT+02:00) Athens',
            'Europe/Bucharest'     => '(GMT+02:00) Bucharest',
            'Africa/Cairo'         => '(GMT+02:00) Cairo',
            'Africa/Harare'        => '(GMT+02:00) Harare',
            'Europe/Helsinki'      => '(GMT+02:00) Helsinki',
            'Europe/Istanbul'      => '(GMT+02:00) Istanbul',
            'Asia/Jerusalem'       => '(GMT+02:00) Jerusalem',
            'Europe/Kiev'          => '(GMT+02:00) Kyiv',
            'Europe/Minsk'         => '(GMT+02:00) Minsk',
            'Europe/Riga'          => '(GMT+02:00) Riga',
            'Europe/Sofia'         => '(GMT+02:00) Sofia',
            'Europe/Tallinn'       => '(GMT+02:00) Tallinn',
            'Europe/Vilnius'       => '(GMT+02:00) Vilnius',
            'Asia/Baghdad'         => '(GMT+03:00) Baghdad',
            'Asia/Kuwait'          => '(GMT+03:00) Kuwait',
            'Africa/Nairobi'       => '(GMT+03:00) Nairobi',
            'Asia/Riyadh'          => '(GMT+03:00) Riyadh',
            'Asia/Tehran'          => '(GMT+03:30) Tehran',
            'Europe/Moscow'        => '(GMT+04:00) Moscow',
            'Asia/Baku'            => '(GMT+04:00) Baku',
            'Europe/Volgograd'     => '(GMT+04:00) Volgograd',
            'Asia/Muscat'          => '(GMT+04:00) Muscat',
            'Asia/Tbilisi'         => '(GMT+04:00) Tbilisi',
            'Asia/Yerevan'         => '(GMT+04:00) Yerevan',
            'Asia/Kabul'           => '(GMT+04:30) Kabul',
            'Asia/Karachi'         => '(GMT+05:00) Karachi',
            'Asia/Tashkent'        => '(GMT+05:00) Tashkent',
            'Asia/Kolkata'         => '(GMT+05:30) Kolkata',
            'Asia/Kathmandu'       => '(GMT+05:45) Kathmandu',
            'Asia/Yekaterinburg'   => '(GMT+06:00) Ekaterinburg',
            'Asia/Almaty'          => '(GMT+06:00) Almaty',
            'Asia/Dhaka'           => '(GMT+06:00) Dhaka',
            'Asia/Novosibirsk'     => '(GMT+07:00) Novosibirsk',
            'Asia/Bangkok'         => '(GMT+07:00) Bangkok',
            'Asia/Jakarta'         => '(GMT+07:00) Jakarta',
            'Asia/Krasnoyarsk'     => '(GMT+08:00) Krasnoyarsk',
            'Asia/Chongqing'       => '(GMT+08:00) Chongqing',
            'Asia/Hong_Kong'       => '(GMT+08:00) Hong Kong',
            'Asia/Kuala_Lumpur'    => '(GMT+08:00) Kuala Lumpur',
            'Australia/Perth'      => '(GMT+08:00) Perth',
            'Asia/Singapore'       => '(GMT+08:00) Singapore',
            'Asia/Taipei'          => '(GMT+08:00) Taipei',
            'Asia/Ulaanbaatar'     => '(GMT+08:00) Ulaan Bataar',
            'Asia/Urumqi'          => '(GMT+08:00) Urumqi',
            'Asia/Irkutsk'         => '(GMT+09:00) Irkutsk',
            'Asia/Seoul'           => '(GMT+09:00) Seoul',
            'Asia/Tokyo'           => '(GMT+09:00) Tokyo',
            'Australia/Adelaide'   => '(GMT+09:30) Adelaide',
            'Australia/Darwin'     => '(GMT+09:30) Darwin',
            'Asia/Yakutsk'         => '(GMT+10:00) Yakutsk',
            'Australia/Brisbane'   => '(GMT+10:00) Brisbane',
            'Australia/Canberra'   => '(GMT+10:00) Canberra',
            'Pacific/Guam'         => '(GMT+10:00) Guam',
            'Australia/Hobart'     => '(GMT+10:00) Hobart',
            'Australia/Melbourne'  => '(GMT+10:00) Melbourne',
            'Pacific/Port_Moresby' => '(GMT+10:00) Port Moresby',
            'Australia/Sydney'     => '(GMT+10:00) Sydney',
            'Asia/Vladivostok'     => '(GMT+11:00) Vladivostok',
            'Asia/Magadan'         => '(GMT+12:00) Magadan',
            'Pacific/Auckland'     => '(GMT+12:00) Auckland',
            'Pacific/Fiji'         => '(GMT+12:00) Fiji',
        );

		if( $reverse )
		{
			$data = array_flip($data);
		}

		return $data;
    }
}