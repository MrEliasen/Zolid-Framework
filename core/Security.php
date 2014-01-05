<?php
/**
 *  Zolid Framework
 *  https://github.com/MrEliasen/Zolid-Framework
 *  
 *  @author     Mark Eliasen (mark.eliasen@zolidsolutions.com)
 *  @copyright  Copyright (c) 2014, Mark Eliasen
 *  @version    0.1.6.1
 *  @license    http://opensource.org/licenses/MIT MIT License
 */

// do not allow direct file access
if( !defined('ROOTPATH') )
{
    die();
}

class Security
{
    protected static $key;
    protected static $keep = array();

    // Protected constructor to prevent instance creation
    protected function __construct()
    {
        
    }

    public static function bruteforcePrevention( $seconds )
    {
        sleep( intval($seconds) );
    }

    /**
     * generates a CSRF token.
     * 
     * @param  string $name the name of the token, used when checking if it is valid.
     * @return string the csrf token. Add this token to a GET or POST value with the same name as the one supplied here.
     */
    public static function newToken( $name )
    {
        self::$keep[] = $name;
		$token = sha1( self::randomGenerator(32) );
        $_SESSION['csrf'][$name] = $token;

        return $token;
    }

    /**
     * Checks if a CSRF token is valid 
     * 
     * @param  string $name the identifier for the $_REQUEST and $_SESSION value to compare.
     * @return boolean      true on valid token, false on invalid or missing token.
     */
    public static function validateToken( $name, $keep = false )
    {
        Configure::load('security');

        if( !Configure::load('security.csf_enabled') )
        {
            return true;
        }

        if( empty($_SESSION['csrf'][$name]) || empty($_REQUEST['token']) || $_REQUEST['token'] != $_SESSION['csrf'][$name] )
        {
            return false;
        }
        
        if( !$keep )
        {
            self::removeToken($name);
        }

        return true;
    }

    /**
     * Checks if a CSRF token is valid 
     * 
     * @param  string $name the identifier for the $_REQUEST and $_SESSION value to compare.
     * @return boolean      true on valid token, false on invalid or missing token.
     */
    public static function clearOldToken( )
    {
        if( empty($_SESSION['csrf']) )
        {
            return;
        }

        foreach( $_SESSION['csrf'] as $key => $token )
        {
            if( !in_array($key, self::$keep))
            {
                self::removeToken($key);
            }
        }
    }
    
    /**
     * invalidates/removes a csrf token so it can no longer be used. 
     * 
     * @param  string $name the identifier for the csrf token.
     * @return boolean      true on valid token, false on invalid or missing token.
     */
    public static function removeToken( $name )
    {
        if( isset($_SESSION['csrf'][$name]) )
        {
            unset( $_SESSION['csrf'][$name] );
        }

        return true;
    }

    /**
     * validate if the email is .. well valid!
     * 
     * @param string the email address
     * @return boolean true/false
     */
    public static function validateEmail( $email )
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Escapes a string, making it safe for regex
     * 
     * @param string $string The URI to be escaped
     * @return string the escaped $uri string
     */
    public static function escape( $string )
    {
        return addcslashes($string, "/\\");
    }
    
    /**
     * Unescapes a string that has been escaped with Security::escape()
     *
     * @param string $string The URI to be unescaped
     * @return string the unescaped $string string
     */
    public static function unescape( $string )
    {
        return stripcslashes($string);
    }
    
    /**
     * Unescapes a URI that has been escaped with Security::escape()
     *
     * @param string $string The URI to be unescaped
     * @return string the unescaped $string string
     */
    public static function htmlPurify( $value, $configValues = array() )
    {
        // Load the database configuration
        Configure::load('security');

        $include = ROOTPATH . 'libs' . DS . 'htmlpurifier-' . Configure::get('security/purifier_version') . DS . 'HTMLPurifier.standalone.php';

        // Make sure the html purifier lib is available, else throw and error.
        if( !is_readable($include) )
        {
            Error::throwNew('Library "HTML Purifier" was not found. ' . $include);
        }

        // Include the html purifier lib.
        require_once($include);
        
        // Begin building the config
        $config = HTMLPurifier_Config::createDefault();
        
        // If no encoding has been passed, set it to UTF-8 By default
        $config->set('Core.Encoding', $configValues['Core.Encoding']);

        //Loop through the configuration values to generate the config for the html purifier
        if( !empty($configValues) )
        {
            foreach( $configValues as $setting => $val )
            {
                $config->set($setting, $val);
            }
        }
        
        // HTML purifier helps prevent XSS, and will remove any HTML tags not specified.
        $purifier = new HTMLPurifier($config);
        return $purifier->purify( $value );
    }

    /**
     * Sanitize input and output with ease using one of the sanitation rules below.
     * 
     * @param  string $data the string/value you wish to sanitize
     * @param  string $type the type of sanitation you wish to use.
     * @return string       the sanitized string
     */
    public static function sanitize( $data, $type )
    {
        Configure::load('security');

        // Do not allow any html by default.
        $config = array(
            'HTML.Allowed' => '',
            'Core.Encoding' => 'UTF-8'
        );

		switch( $type )
        {
			case 'string':
                $config['HTML.Allowed'] = Configure::get('security/allowed_html');
				break;
                
            case 'purestring':
                $data = filter_var( $data, FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH );
                break;
                
            case 'username':
                $data = preg_replace('/[^a-zA-Z0-9\-\_]+/', '', self::escape($data) );
                $data = self::unescape($data);
                break;
				
			case 'atoz':
				$data = preg_replace('/[^a-zA-Z]+/', '', self::escape($data) );
                $data = self::unescape($data);
				break;
				
			case 'route':
				$data = preg_replace('/[^0-9a-zA-Z\-\_\/]+/', '', self::escape($data) );
                $data = self::unescape($data);
				break;
				
			case 'integer':
				$data =  filter_var($data, FILTER_SANITIZE_NUMBER_INT);
				break;
			
            case 'mixedint':
				$data = preg_replace('/[^0-9\.,\+-]+/', '', self::escape($data) );
                $data = self::unescape($data);
				break;
				
			case 'email':
				$data = strtolower( filter_var($data, FILTER_SANITIZE_EMAIL) );
				break;

            default:
                Error::throwNew('Invalid sanitation type "' . $type . '" specified');
                break;
		}

        // run it through the HTML purifier
        return self::htmlPurify($data, $config);
	}

    /**
     * generates a random string, "url safe" or with special characters as well
     * 
     * @param  integer $length  how long the string should be, default is 64
     * @param  boolean $urlsafe whether the string should be url safe or not
     * @return string           the random string
     */
    public static function randomGenerator($length = 64, $urlsafe = false)
    {
        // Make sure the length is an integer
        $length = intval($length);

        // make sure the length is positive (1+)
		if( $length < 1 )
        {
			Error::throwNew('Invalid length  "' . $length . '" specified');
		}
		
        $options = 'aei^*@bcdf1234ghjklB{]}CDFouyGHJK[LMmn4567pq$+|,.AEI-_rstvxzNPQRSTV890XZ!#%OUY/()=?';

        // Check if whether we can only use url safe characters only or not.
        if( $urlsafe )
        {
            $options = 'abcdefghijklmnopqrstuvxyz1234567890ABCDEFGHIJKLMNOPQRSTUVXYZ0987654321-_';
        }

		$key = '';
		$alt = mt_rand() % 2;
		for( $i = 0; $i < $length; $i++ )
        {
			$key .= $options[ ( mt_rand() % strlen($options) ) ];
		}
		
		return $key;
	}

    /**
     * Hash the email with the $algo -rithm, used for identifications when the user login.
     * 
     * @param  string $email the email address
     * @return string        the hashes email address
     */
    public static function hash( $email, $algo = 'SHA512' )
    {
        Configure::load('security');

        return hash_hmac($algo, strtolower($email), Configure::get('security/hash_key'));
    }

    /**
     * AES encryptes the $data input
     * 
     * @param  Mixed $data the data you wish to encrypt
     * @return string the encrypted data
     */
    public static function encryptData( $data )
    {
        Configure::load('security');

        if( empty(self::$key) )
        {
            self::$key = self::pbkdf2(Configure::get('security/encryption_key'), Configure::get('security/encryption_salt'), Configure::get('security/encryption_key_iterations'), 32);
        }

        $data = self::encrypt($data, self::$key);
        return $data;
    }

    /**
     * AES decrypts the $data input
     * 
     * @param  Mixed $data The data you wish to decrypt
     * @return string The decrypted data.
     */
    public static function decryptData( $data )
    {
        Configure::load('security');
        
        if( empty(self::$key) )
        {
            self::$key = self::pbkdf2(Configure::get('security/encryption_key'), Configure::get('security/encryption_salt'), Configure::get('security/encryption_key_iterations'), 32);
        }

        $data = self::decrypt($data, self::$key);
        return $data;
    }
	
    /*-------------------------------------------------------------------------
            Cryptastic, by Andrew Johnson (2009).
            http://www.itnewb.com/user/Andrew

            You are free to use this code for personal/business use,
            without attribution, although it would be appreciated.
    -------------------------------------------------------------------------*/

	/** Encryption Procedure
     *
     *  @param mixed msg message/data
     *  @param string k encryption key
     *  @param boolean base64 base64 encode result
     *
     *  @return string iv+ciphertext+mac or
     * boolean false on error
    */
    public static function encrypt( $msg, $k, $base64 = false ) {
 
        # open cipher module (do not change cipher/mode)
        if ( ! $td = mcrypt_module_open('rijndael-256', '', 'ctr', '') )
            return false;
 
        $msg = serialize($msg);                         # serialize
        $iv = mcrypt_create_iv(32, MCRYPT_RAND);        # create iv
 
        if ( mcrypt_generic_init($td, $k, $iv) !== 0 )  # initialize buffers
            return false;
 
        $msg = mcrypt_generic($td, $msg);               # encrypt
        $msg = $iv . $msg;                              # prepend iv
        $mac = Security::pbkdf2($msg, $k, 1000, 32);       # create mac
        $msg .= $mac;                                   # append mac
 
        mcrypt_generic_deinit($td);                     # clear buffers
        mcrypt_module_close($td);                       # close cipher module
 
        if ( $base64 ) $msg = base64_encode($msg);      # base64 encode?
 
        return $msg;                                    # return iv+ciphertext+mac
    }
 
    /** Decryption Procedure
     *
     *  @param string msg output from encrypt()
     *  @param string k encryption key
     *  @param boolean base64 base64 decode msg
     *
     *  @return string original message/data or
     * boolean false on error
    */
    public static function decrypt( $msg, $k, $base64 = false ) {
 
        if ( $base64 ) $msg = base64_decode($msg);          # base64 decode?
 
        # open cipher module (do not change cipher/mode)
        if ( ! $td = mcrypt_module_open('rijndael-256', '', 'ctr', '') )
            return false;
 
        $iv = substr($msg, 0, 32);                          # extract iv
        $mo = strlen($msg) - 32;                            # mac offset
        $em = substr($msg, $mo);                            # extract mac
        $msg = substr($msg, 32, strlen($msg)-64);           # extract ciphertext
        $mac = Security::pbkdf2($iv . $msg, $k, 1000, 32);     # create mac
 
        if ( $em !== $mac )                                 # authenticate mac
            return false;
 
        if ( mcrypt_generic_init($td, $k, $iv) !== 0 )      # initialize buffers
            return false;
 
        $msg = mdecrypt_generic($td, $msg);                 # decrypt
        $msg = unserialize($msg);                           # unserialize
 
        mcrypt_generic_deinit($td);                         # clear buffers
        mcrypt_module_close($td);                           # close cipher module
 
        return $msg;                                        # return original msg
    }
 
    /** PBKDF2 Implementation (as described in RFC 2898);
     *
     *  @param string p password
     *  @param string s salt
     *  @param int c iteration count (use 1000 or higher)
     *  @param int kl derived key length
     *  @param string a hash algorithm
     *
     *  @return string derived key
    */
    public static function pbkdf2( $p, $s, $c, $kl, $a = 'sha256' ) {
 
        $hl = strlen(hash($a, null, true)); # Hash length
        $kb = ceil($kl / $hl);              # Key blocks to compute
        $dk = '';                           # Derived key
 
        # Create key
        for ( $block = 1; $block <= $kb; $block ++ ) {
 
            # Initial hash for this block
            $ib = $b = hash_hmac($a, $s . pack('N', $block), $p, true);
 
            # Perform block iterations
            for ( $i = 1; $i < $c; $i ++ )
 
                # XOR each iterate
                $ib ^= ($b = hash_hmac($a, $b, $p, true));
 
            $dk .= $ib; # Append iterated block
        }
 
        # Return derived key of correct length
        return substr($dk, 0, $kl);
    }
}
