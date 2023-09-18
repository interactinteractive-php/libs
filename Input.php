<?php if (!defined('_VALID_PHP')) exit('Direct access to this location is not allowed.');

/**
 * Input Class
 *
 * @package     IA PHPframework
 * @subpackage	Libraries
 * @category	Input
 * @author	B.Och-Erdene
 * @link	http://www.interactive.mn/PHPframework/Input
 */

class Input {
    
    /**
    * Gets the specified GET variable.
    *
    * @param   string  $index    The index to get
    * @param   string  $default  The default value
    * @return  string|array
    */
    public static function get($index = null, $default = null)
    {
        if (self::getCheck($index)) {
            return Security::sanitize($_GET[$index]); 
        } else {
            return $default;
        }
        return null;
    }
    
    public static function getCheck($index = null)
    {
        if (isset($_GET[$index])) {
            return true;
        }
        return false;
    }
    
    public static function isEmptyGet($index = null)
    {
        if (!isset($_GET[$index])) {
            return true;
        }
        if (empty($_GET[$index])) {
            return true;
        }
        return false;
    }
    
    /**
    * Fetch an item from the POST array
    *
    * @param   string  The index key
    * @param   mixed   The default value
    * @return  string|array
    */
    public static function post($index = null, $default = null)
    {
        if (self::postCheck($index)) {
            return ($_POST[$index] === '' || $_POST[$index] === null) ? null : Security::sanitize($_POST[$index]); 
        } else {
            return $default;
        }
    }
    
    public static function postCheck($index = null)
    {
        return isset($_POST[$index]);
    }
    
    public static function isEmpty($index = null)
    {
        if (!isset($_POST[$index])) {
            return true;
        } elseif (empty($_POST[$index])) {
            return true;
        }
        return false;
    }
    
    public static function isNotEmpty($index = null)
    {
        if (empty($_POST[$index])) {
            return false;
        }
        return true;
    }
    
    public static function postNonTags($index = null, $default = null)
    {
        return (func_num_args() === 0) ? $_POST : Arr::get($_POST, $index, $default);
    }

    public static function float($index = null, $default = null)
    {
        if (self::postCheck($index)) {
            return Security::float($_POST[$index]); 
        }
        return null;
    }
    
    public static function param($var)
    {
        return ($var === '' || $var === null) ? null : Security::sanitize($var); 
    }
    
    public static function paramWithDoubleSpace($var)
    {
        return ($var === '' || $var === null) ? null : Security::sanitizeWithDoubleSpace($var); 
    }
    
    public static function postWithDoubleSpace($index = null, $default = null)
    {
        if (self::postCheck($index)) {
            return (($_POST[$index] === '' || $_POST[$index] === null) ? null : Security::sanitizeWithDoubleSpace($_POST[$index])); 
        } else {
            return $default;
        }
    }
    
    public static function postData()
    {
        return $_POST;
    }
    
    public static function requestData()
    {
        return $_REQUEST;
    }
    
    public static function paramFloat($param)
    {
        return Security::float($param); 
    }
    
    public static function integer($index = null, $default = null)
    {
        if (self::postCheck($index)) {
            return Security::integer($_POST[$index]); 
        }
        return null;
    }
    
    public static function numeric($index = null, $default = null)
    {
        if (self::postCheck($index)) {
            
            $numeric = $_POST[$index];
        
            if (is_numeric($numeric)) {
                return $numeric;
            }
        } 
        
        return $default;
    }
    
    public static function paramNum($var)
    {
        return ($var === '' || $var === null) ? null : (is_numeric($var) ? $var : null); 
    }
    
    public static function paramInt($param)
    {
        return Security::integer($param); 
    }
    
    public static function fileData()
    {
        return $_FILES;
    }
    
    public static function getData()
    {
        return $_GET;
    }
    
}
