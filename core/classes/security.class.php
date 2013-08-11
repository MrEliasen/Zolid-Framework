<?php
/**
 *  Zolid Framework - MIT licensed
 *  https://github.com/MrEliasen/Zolid-Framework
 *
 *  Class which handles all security releated functions throughout the system.
 *
 *  @author     Mark Eliasen
 *  @website    www.zolidweb.com
 *  @copyright  (c) 2013 - Mark Eliasen
 *  @version    0.1.2
 */

if( !defined('CORE_PATH') )
{
    die('Direct file access not allowed.');
}

class Security
{
    /**
     * Checks if a CSRF token is valid 
     * 
     * @param  string $key the identifier for the  $_REQUEST and $_SESSION value to compare.
     * @return boolean      true on valid token, false on invalid or missing token.
     */
    public static function csrfCheck($key, $static = false )
    {
		// Check if the token exists for the current session
        if( empty( $_SESSION['csrf'][$key] ) ){
			return false;
		}

		// Check if the value is found in the REQUEST
        if( empty( $_REQUEST[$key] ) ){
			return false;
		}

        // Check if the 2 tokens match eachother
        if( $_REQUEST[$key] != $_SESSION['csrf'][$key] ){
			return false;
		}
		
		//To avoid the token to be used again.
        if( !$static )
        {
            unset( $_SESSION['csrf'][$key] );   
        }
        return true;
    }
	
    /**
     * generate a CSRF token.
     * 
     * @param  string $key the name of the token, used when checking if it is valid.
     * @return string      the csrf token. Add this token to a GET or POST value with the same key as the one supplied here.
     */
    public static function csrfGenerate($key)
    {
		$token = sha1( time() . $_SERVER['REMOTE_ADDR'] . Security::randomGenerator(15) );
        $_SESSION['csrf'][$key] = $token;

        return $token;
    }

    /**
     * Value sanitation. Sanitize input and output with ease using one of the sanitation types below.
     * 
     * @param  string $data the string/value you wish to sanitize
     * @param  string $type the type of sanitation you wish to use.
     * @return string       the sanitized string
     */
    public static function sanitize($data, $type)
    {
		## Use the HTML Purifier, as it help remove malicious scripts and code. ##
		##       HTML Purifier 4.4.0 - Standards Compliant HTML Filtering       ##
		require_once(CORE_PATH . '/libs/htmlpurifier-4.4.0/HTMLPurifier.standalone.php');

		$purifier = new HTMLPurifier();
		$config = HTMLPurifier_Config::createDefault();
		$config->set('Core.Encoding', 'UTF-8');

		switch($type){
			case 'string':
				$data = filter_var( $data, FILTER_SANITIZE_STRING );
				break;
				
			case 'purestring':
				$data = strip_tags( $data );
				break;
				
			case 'atoz':
				$data = preg_replace( '/[^a-zA-Z]+/', '', strip_tags( $data) );
				break;
				
			case 'page':
				$data = preg_replace( '/[^0-9a-zA-Z\-\_\.]+/', '', str_replace('.php', '', strip_tags(  $data ) ) );
				break;
				
			case 'integer':
				$data =  filter_var( $data, FILTER_SANITIZE_NUMBER_INT );
				break;
			
			case 'float':
				$data = filter_var( $data, FILTER_SANITIZE_NUMBER_INT, FILTER_FLAG_ALLOW_FRACTION );
				break;
				
			case 'mixedint':
				$data = filter_var( $data, FILTER_SANITIZE_NUMBER_INT, FILTER_FLAG_ALLOW_FRACTION, FILTER_FLAG_ALLOW_THOUSAND );
				break;
				
			case 'email':
				$data = strtolower( filter_var( $data, FILTER_SANITIZE_EMAIL ) );
				break;
				
			case 'phone':
				$data = filter_var(  $data, FILTER_SANITIZE_NUMBER_INT );
				break;
		}
		
        /* HTML purifier to help prevent XSS in case anything slipped through. */
        $data = $purifier->purify( $data );

		return $data;
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
		if($length < 1){
			$length = 1;
		}
		
        if( $urlsafe )
        {
            $options = 'abcdefghijklmnopqrstuvxyz1234567890ABCDEFGHIJKLMNOPQRSTUVXYZ098765432';
        }
        else
        {
            $options = 'aei^*@bcdf1234ghjklB{]}CDFouyGHJK[LMmn4567pq$+|,.AEI-_rstvxzNPQRSTV890XZ!#%OUY/()=?';
        }

		$key = '';
		$alt = mt_rand() % 2;
		for ($i = 0; $i < $length; $i++) {
			$key .= $options[(mt_rand() % strlen($options))];
		}
		
		return $key;
	}
	
/*-------------------------------------------------------------------------

	Cryptastic, by Andrew Johnson (2009).
	http://www.itnewb.com/user/Andrew

	You are free to use this code for personal/business use,
	without attribution, although it would be appreciated.

	-----------------------------------------------------------------------

	CAUTION, CAUTION, CAUTION! USE AT YOUR OWN RISK!

	It's your duty to use good passwords, salts and keys; and come up
	with an adequately safe techinque to store and access them.

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
