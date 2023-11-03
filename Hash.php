<?php if(!defined('_VALID_PHP')) exit('Direct access to this location is not allowed.');

/**
 * Hash Class
 *
 * @package     IA PHPframework
 * @subpackage	Libraries
 * @category	Hash
 * @author	B.Och-Erdene
 * @link	http://www.interactive.mn/PHPframework/Hash
 */

class Hash
{
    /**
     *
     * @param string $algo The algorithm (md5, sha1, sha256, whirlpool, etc)
     * @param string $data The data to encode
     * @param string $salt The salt (This should be the same throughout the system probably)
     * @return string The hashed/salted data
     */
    
    public static function create($algo, $data, $salt = null)
    {
        if (defined('GF_SERVICE_ADDRESS') && GF_SERVICE_ADDRESS) {
            
            WebService::$isDefaultSessionId = true;
            $result = WebService::runSerializeResponse(GF_SERVICE_ADDRESS, 'getPasswordHash', array('passwordHash' => $data));
            
            if (isset($result['status']) && $result['status'] == 'success' && isset($result['result']['result'])) {
                return $result['result']['result'];
            }
        }
    
        $salt = $salt ? $salt : 'N#L!xQ';
        $context = hash_init($algo, HASH_HMAC, $salt);
        
        hash_update($context, $data);
        
        return hash_final($context);   
    }
    
    public static function createHash($algo, $data)
    {    
        if ($data == '') {
            return null;
        }
        
        return hash($algo, $data, false);
    }
    
    /**
     * Plain data to encypted data
     * @param type $data The data to encode
     */
    public static function createMD5reverse($data)
    {
        if ($data == '') {
            return null;
        }
        
        $md5Data = strtolower(md5($data));                
        $md5Array = str_split($md5Data, 2);
        
        $encryptedData = '';
        
        foreach ($md5Array as $md5String) {
            $encryptedData .= strrev($md5String);
        }
        
        return $encryptedData;
    }
    
    public static function cryptString($string, $salt, $action = 'e') 
    {
        $output = false;
        $encrypt_method = 'AES-256-CBC';
        $key = hash('sha256', $salt);
        $iv = substr(hash('sha256', $salt), 0, 16);

        if ($action == 'e') {
            $output = base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv));
        } elseif ($action == 'd') {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }

        return $output;
    }
    
    public static function encryption($data) {

        $iv   = 'V6!)fTn7]n^eBrfy'; 
        $key  = 'PjEc~Q^D;4:*5v&D';

        $encodedEncryptedData = base64_encode(openssl_encrypt($data, 'aes-128-cbc', $key, OPENSSL_RAW_DATA, $iv));
        $encodedIV            = base64_encode($iv);
        $encryptedPayload     = $encodedEncryptedData.':'.$encodedIV;

        return $encryptedPayload;
    }
    
    public static function decryption($data) {
        
        $key = 'PjEc~Q^D;4:*5v&D';
        $parts = explode(':', $data);
        
        if (isset($parts[1])) {
            $decryptedData = openssl_decrypt(base64_decode($parts[0]), 'aes-128-cbc', $key, OPENSSL_RAW_DATA, base64_decode($parts[1]));
            return $decryptedData;
        } else {
            return false;
        }
    }
    
}