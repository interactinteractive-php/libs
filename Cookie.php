<?php if(!defined('_VALID_PHP')) exit('Direct access to this location is not allowed.');

/**
 * Cookie Class
 *
 * @package     IA PHPframework
 * @subpackage	Libraries
 * @category	Cookie
 * @author	B.Och-Erdene
 * @link	http://www.interactive.mn/PHPframework/Cookie
 */
 
class Cookie {
	
    static public function set($key, $value, $expire = null, $httpOnly = false) {
        
        if ($expire == null) $expire = time(); # default now..
        $_COOKIE[$key] = $value;
        
        $secure = false;
        
        if (Uri::isSSL()) {
            $secure = true;
        }

        setcookie($key, $value, $expire, '/', Uri::domain(), $secure, $httpOnly);
        
        return $value;
    }

    static public function get($key) {
        return isset($_COOKIE[$key]) ? $_COOKIE[$key] : '';
    }
    
    static public function isCheck($key) {
        return isset($_COOKIE[$key]);
    }
    
    static public function destroy($key) {
        unset($_COOKIE[$key]);
    }
	
}