<?php 
/**
 * Compression Class
 *
 * @package     IA PHPframework
 * @subpackage	Libraries
 * @category	Compression
 * @author	B.Och-Erdene
 * @link	http://www.interactive.mn/PHPframework/Compression
 */

class Compression extends Controller {

    public function __construct() {
        parent::__construct();
    }
    
    public static function compressGZIP($data) 
    {    
        $compressed = gzcompress($data, 9);
        return $compressed;
    }
    
    public static function decompressGZIP($data)
    {
        $uncompressed = gzuncompress($data);
        return $uncompressed;
    }
    
    public static function gzinflate($data)
    {
        $data = trim($data);
        $data = base64_decode($data);
        $compressed = @gzinflate($data);
        
        return $compressed;
    }
    
    public static function gzdeflate($data)
    {
        $data = trim($data);
        $compressed = gzdeflate($data, 9);
        $compressed = base64_encode($compressed);
        
        return $compressed;
    }    
    
    public static function decompress($data)
    {
        $data = trim($data);
        $compressed = base64_decode($data);
        $compressed = @gzinflate(substr($compressed, 10, -8));
  
        if ($compressed === false || $compressed == '' || strpos($compressed, 'aQ') !== false || strpos($compressed, 'aR') !== false || strpos($compressed, 'Q') !== false || !mb_check_encoding($compressed, 'UTF-8')) {
            return $data;
        }
        
        return $compressed;
    }
    
    public static function compress($data)
    {
        $compressed = gzdeflate($data, 9);
        $compressed = base64_encode($compressed);
        
        return $compressed;
    }
    
    public static function encode_string_array($stringArray) {
        $s = strtr(base64_encode(addslashes(gzcompress(serialize($stringArray),9))), '+/=', '-_,');
        return $s;
    }

    public static function decode_string_array($stringArray) {
        $s = unserialize(gzuncompress(stripslashes(base64_decode(strtr($stringArray, '-_,', '+/=')))));
        return $s;
    }

}
