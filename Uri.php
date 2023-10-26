<?php if(!defined('_VALID_PHP')) exit('Direct access to this location is not allowed.');

/**
 * Uri Class
 *
 * @package     IA PHPframework
 * @subpackage	Libraries
 * @category	Uri
 * @author	B.Och-Erdene
 * @link	http://www.interactive.mn/PHPframework/Uri
 */

class Uri {
    
    public static $pUsername = 'user';
    public static $pPassword = 'user*resu';

    public static function host()
    {
        if (isset($_SERVER['HTTPS']) &&
            ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
            isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
            $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $protocol = 'https://';
        } else {
            $protocol = 'http://';
        }

        return $protocol;
    } 
    
    public static function port()
    {
        $port = ($_SERVER['SERVER_PORT'] != '80') ? $_SERVER['SERVER_PORT'] : ''; 
        return $port;
    } 
    
    public static function portDotted()
    {
        $port = ($_SERVER['SERVER_PORT'] != '80') ? ':'.$_SERVER['SERVER_PORT'] : ''; 
        return $port;
    } 
    
    public static function currentURL() 
    {
        $URL = self::host().self::domain().self::portDotted().$_SERVER['REQUEST_URI'];
        return $URL;
    }
    
    public static function currentURLnotPort() 
    {
        $URL = self::host().self::domain().$_SERVER['REQUEST_URI'];
        return $URL;
    }

    public static function fullDomain() 
    {
        $url = self::host().self::domain().self::portDotted();
        return $url;
    }

    public static function fullDomainNotPort() 
    {
        $url = self::host().self::domain();
        return $url;
    }
    
    public static function domain() 
    {
        $url = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
        return $url;
    }
    
    public static function domainPort() 
    {
        $url = self::domain().self::portDotted();
        return $url;
    }
    
    public static function basePath()
    {
        $path = dirname($_SERVER['PHP_SELF']).'/';
        return $path;
    }
    
    public static function referer()
    {
        $URL = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        return $URL;
    }
    
    public static function isUrlAuth() {

        if (Session::isCheck(SESSION_PREFIX . 'isUrlAuthenticate')) {
            
            $username = null;
            $password = null;

            if (isset($_SERVER['PHP_AUTH_USER'])) {
                
                $username = $_SERVER['PHP_AUTH_USER'];
                $password = $_SERVER['PHP_AUTH_PW'];

            } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {

                if (strpos(strtolower($_SERVER['HTTP_AUTHORIZATION']), 'basic') === 0) {
                    list($username, $password) = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
                }
            }
             
            if (!Session::isCheck(SESSION_PREFIX . 'loggedUrlAuthenticate')) {    
                
                Session::set(SESSION_PREFIX . 'loggedUrlAuthenticate', false);
                
            } else {
                
                if (self::validateUserPassword($username, $password)) {
                    Session::set(SESSION_PREFIX . 'loggedUrlAuthenticate', true);
                }
            }
            
            if (!Session::get(SESSION_PREFIX . 'loggedUrlAuthenticate')) {
                
                header('WWW-Authenticate: ' .  
                    'Basic realm="Protected Page: ' .  
                    'Enter your username and password for access."');  
                header("HTTP/1.0 401 Unauthorized");  

                die();
            }   
        }
    }
    
    public static function validateUserPassword($username, $password) {
        
        $dbConfig = Config::getFromCache('META_LOGIN_USER_PASS');
        
        if ($dbConfig) {
            
            $decrypt = Crypt::decrypt($dbConfig, 'md');
            
            if ($decrypt) {
                $decryptArr = explode(':', $decrypt);
                
                if (count($decryptArr) == 2) {
                    
                    $usernameLower = strtolower($username);
                    $usernameConfig = strtolower($decryptArr[0]);
                    
                    if ($username && $password && ($usernameLower == $usernameConfig && $password == $decryptArr[1])) {
                        return true;
                    }
                }
            }
            
        } elseif ($username && $password && ($username == self::$pUsername && $password == self::$pPassword)) {
            return true;
        }
        
        return false;
    }
    
    public static function isSSL() {
        
	if (isset($_SERVER['HTTPS'])) {
            
            $https = $_SERVER['HTTPS'];
            
            if (strtolower($https) == 'on' || $https == '1') {
		return true;
            }
            
	} elseif (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
            return true;
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            return true;
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
            return true;
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_PORT']) && $_SERVER['HTTP_X_FORWARDED_PORT'] == 443) {
            return true;
        } elseif (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https') {
            return true;
        }
        
        return false;
    }
    
}