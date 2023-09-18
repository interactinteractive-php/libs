<?php if (!defined('_VALID_PHP')) exit('Direct access to this location is not allowed.');

/**
 * Crypt Class
 *
 * @package     IA PHPframework
 * @subpackage	Libraries
 * @category	Crypt
 * @author	B.Och-Erdene
 * @link	http://www.interactive.mn/PHPframework/Crypt
 */

error_reporting(1);
ini_set('display_errors', 1);

class Crypt {
    
    private static $skey = "$2#1";
 
    public function safe_b64encode($string) 
    {
        $data = base64_encode($string);
        $data = str_replace(array('+','/','='),array('-','_',''),$data);
        return $data;
    }
 
    public function safe_b64decode($string) 
    {
        $data = str_replace(array('-','_'),array('+','/'),$string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }
 
    static public function encode($value, $key = false) 
    { 
        if ($value == "") { return false; }
 
        $text = $value;
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        
        if (!$key) {
            $crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, self::$skey, $text, MCRYPT_MODE_ECB, $iv);
        } else {
            $crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $text, MCRYPT_MODE_ECB, $iv);
        }
        
        return trim(self::safe_b64encode($crypttext)); 
    }
 
    static public function decode($value, $key = false) 
    {
        if (!$value) { return false; }
        
        $crypttext = self::safe_b64decode($value); 
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        
        if (!$key) {
            $decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, self::$skey, $crypttext, MCRYPT_MODE_ECB, $iv);
        } else {
            $decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $crypttext, MCRYPT_MODE_ECB, $iv);
        }
        
        return trim($decrypttext);
    }
	
    static public function encrypt($string, $secret_key = 'sk', $secret_iv = 'si') {
        $output = false;

        $encrypt_method = "AES-256-CBC";

        // hash
        $key = hash('sha256', $secret_key);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secret_iv), 0, 16);

        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);

        return $output;
    }

    static public function decrypt($string, $secret_key = 'sk', $secret_iv = 'si') {
        $output = false;

        $encrypt_method = "AES-256-CBC";

        // hash
        $key = hash('sha256', $secret_key);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secret_iv), 0, 16);

        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);

        return $output;
    }
    
}
