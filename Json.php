<?php if(!defined('_VALID_PHP')) exit('Direct access to this location is not allowed.');

/**
 * Json Class
 *
 * @package     IA PHPframework
 * @subpackage	Libraries
 * @category	Json
 * @author	B.Och-Erdene
 * @link	http://www.interactive.mn/PHPframework/Json
 */

class Json {
    
    public static function encode($array) {
        return self::escapeJsonString(json_encode($array));
    }
    
    public static function decode($string) {
        return json_decode($string, true);
    }
    
    public static function setHeaderEncode($array) {
        header('Content-Type: application/json');
        echo self::escapeJsonString(json_encode($array));
    }
    
    public static function escapeJsonString($value) {   
        $escapers =     array("\\",     "/",   "\"",  "\n",  "\r",  "\t", "\x08", "\x0c", "'");
        $replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t",  "\\f",  "\\b", "\'");
        $result = str_replace($escapers, $replacements, $value);
        return $result;
    }
    
    public static function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
  
}