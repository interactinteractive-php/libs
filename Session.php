<?php if(!defined('_VALID_PHP')) exit('Direct access to this location is not allowed.');

/**
 * Session Class
 *
 * @package     IA PHPframework
 * @subpackage	Libraries
 * @category	Session
 * @author	B.Och-Erdene
 * @link	http://www.interactive.mn/PHPframework/Session
 */

class Session 
{
    
    private static $isRegenerateID = false;
    private static $cookieName = null;

    public static function init($writeClose = true)
    {   
        if ((session_id() == '' && !headers_sent()) || !$writeClose) {
            
            ini_set('session.cookie_lifetime', SESSION_LIFETIME); 
            ini_set('session.gc_maxlifetime', SESSION_LIFETIME); 
            
            ini_set('session.use_trans_sid', 0);
            ini_set('session.use_strict_mode', 1);
            ini_set('session.use_cookies', 1);
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            
            if (Uri::isSSL()) {
                ini_set('session.cookie_secure', 1);
            }
            
            self::$cookieName = md5(Uri::fullDomain());
            session_name(self::$cookieName); 
            
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            
            self::regenerate($writeClose);
        } 
    }

    public static function regenerate($writeClose)
    {
        if (!self::$isRegenerateID) {
            
            self::$isRegenerateID = true;

            if ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') 
                    || isset($_GET['fDownload']) 
                    || isset($_POST['fDownload']) 
                ) {
                
                if (isset($_COOKIE[self::$cookieName]) && $_COOKIE[self::$cookieName] === session_id()) {
                    
                    $params = session_get_cookie_params();
                    
                    setcookie(
                        self::$cookieName,
                        session_id(),
                        time() + $params['lifetime'],
                        $params['path'],
                        $params['domain'],
                        $params['secure'],
                        true
                    );
                }
                
                if ($writeClose) {
                    session_write_close();
                }
                
            } else {
                
                if (!isset($_SESSION[SESSION_PREFIX . 'lastRegenerate'])) {
                    
                    $_SESSION[SESSION_PREFIX . 'lastRegenerate'] = time();
                    
                } elseif ($_SESSION[SESSION_PREFIX . 'lastRegenerate'] < (time() - self::getIntLifeTime())) {
                    
                    $_SESSION[SESSION_PREFIX . 'lastRegenerate'] = time();
                    
                    @session_regenerate_id();
                }
            }
        }
        
        return true;
    }
    
    public static function set($key, $value)
    {
        if (is_ajax_request() && session_status() === PHP_SESSION_NONE) {
            self::init(false);
        }
        
        $_SESSION[$key] = $value;
    }
    
    public static function get($key)
    {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        } else {
            return null;
        }
    }
    
    public static function getArrayValue($key, $value)
    {
        if (isset($_SESSION[$key][$value]))
        return $_SESSION[$key][$value];
    }
    
    public static function isCheck($key)
    {
        return isset($_SESSION[$key]);
    }
    
    public static function delete($key)
    {
        unset($_SESSION[$key]);
    }
    
    public static function destroy()
    {
        $params = session_get_cookie_params();

        setcookie(
            session_name(), 
            session_id(), 
            time() - 42000,
            $params['path'], 
            $params['domain'],
            $params['secure'], 
            true
        );
        
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        
        return true;
    }
	
    public static function unsets()
    {
        unset($_SESSION);
    }
    
    public static function unitName()
    {
        return Session::get(SESSION_PREFIX . 'sdbun');
    }
    
    public static function getIntLifeTime() 
    {    
        return (SESSION_LIFETIME < 300) ? 1 : 300;
    }
    
}