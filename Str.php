<?php if(!defined('_VALID_PHP')) exit('Direct access to this location is not allowed.');

/**
 * String Class
 *
 * @package     IA PHPframework
 * @subpackage	Libraries
 * @category	String
 * @author	B.Och-Erdene
 * @link	http://www.interactive.mn/PHPframework/String
 */
 
class Str {
	
    public static function underscore($string) {
        return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $string));
    }

    public static function humanize($lowerCaseAndUnderscoredWord) {
        return ucwords(str_replace("_", " ", $lowerCaseAndUnderscoredWord));
    }

    public static function titleize($string) {
        $str = trim($string);
        $str = str_replace('  ', ' ', $str);
        $str = mb_strtolower($str, "utf-8");
        $str = str_replace(' ', '-', $str);

        return $str;
    }
	
    public static function cp1251_utf8($string) 
    {
        $cyr_lower_chars = array(
        'е','щ','ф','ц','у','ж','э',
        'н','г','ш','ү','з','к','ъ',
        'й','ы','б','ө','а','х','р',
        'о','л','д','п','я','ч','ё',
        'с','м','и','т','ь','в','ю',);

        $latin_lower_chars = array(
        'å','ù','ô','ö','ó','æ','ý',
        'í','ã','ø','¿','ç','ê','ú',
        'é','û','á','º','à','õ','ð',
        'î','ë','ä','ï','ÿ','÷','¸',
        'ñ','ì','è','ò','ü','â','þ',);

        $cyr_upper_chars = array(
        'Е','Щ','Ф','Ц','У','Ж','Э',
        'Н','Г','Ш','Ү','З','К','Ъ',
        'Й','Ы','Б','Ө','А','Х','Р',
        'О','Л','Д','П','Я','Ч','Ё',
        'С','М','И','Т','Ь','В','Ю',);

        $latin_upper_chars = array(
        'Å','Ù','Ô','Ö','Ó','Æ','Ý',
        'Í','Ã','Ø','¯','Ç','Ê','Ú',
        'É','Û','Á','ª','À','Õ','Ð',
        'Î','Ë','Ä','Ï','ß','×','¨',
        'Ñ','Ì','È','Ò','Ü','Â','Þ',);

        //replacing lower cyrillic
        $text = str_replace($latin_lower_chars, $cyr_lower_chars, $string);
        //replacing upper cyrillic
        $text = str_replace($latin_upper_chars, $cyr_upper_chars, $text);

        return $text;
    }
    
    public static function utf8_substr($str, $offset, $length = false)
    {
        preg_match_all('~[\x09\x0A\x0D\x20-\x7E]
        | [\xC2-\xDF][\x80-\xBF]
        | \xE0[\xA0-\xBF][\x80-\xBF]
        | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}
        | \xED[\x80-\x9F][\x80-\xBF]
        | \xF0[\x90-\xBF][\x80-\xBF]{2}
        | [\xF1-\xF3][\x80-\xBF]{3}
        | \xF4[\x80-\x8F][\x80-\xBF]{2}
        ~xs', $str, $match);
        
        if ($length !== false) {
            $a = array_slice($match[0], $offset, $length);
        } else {
            $a = array_slice($match[0], $offset);
        }
        return implode('', $a);
    } 
    
    public static function excelSheetName($title) 
    {
        return str_replace(array('*',':','/','\\','?','[',']'), '', $title);
    }    

    public static function htmltotext($comment)
    {	
	$search = array('@<script[^>]*?>.*?</script>@si', 
                        '@<[\/\!]*?[^<>]*?>@si',          
                        '@([\r\n])[\s]+@',              
                        '@&(quot|#34);@i',                
                        '@&(amp|#38);@i',
                        '@&(lt|#60);@i',
                        '@&(gt|#62);@i',
                        '@&(nbsp|#160);@i',
                        '@&(iexcl|#161);@i',
                        '@&(cent|#162);@i',
                        '@&(pound|#163);@i',
                        '@&(copy|#169);@i');
        /**
         * @description 5.5.x дээр "/e modifier" deprecated болсон тул коммент хийв
         * @date 2015-11-25
         * @author Ulaankhuu Ts
         */
        //'@&#(\d+);@e'                  

	$replace = array('',
                         '',
                         '\1',
                         '"',
                         '&',
                         '<',
                         '>',
                         ' ',
                         chr(161).' ',
                         chr(162).' ',
                         chr(163).' ',
                         chr(169).' ',
                         'chr(\1) ');
   
	$comment = preg_replace($search, $replace, $comment);
	$comment = preg_replace("[ \t\n\r]", " ", $comment);
	$comment = trim($comment);
        
	return $comment;
    }
  
    public static function cleanOut($text) {
        $text = strtr($text, array('\r\n' => '', '\r' => '', '\n' => ''));
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $text = str_replace('<br>', '<br />', $text);
        return stripslashes($text);
    }
  
    public static function sanitize($string, $trim = false, $int = false, $str = false) {
        $string = filter_var($string, FILTER_SANITIZE_STRING);
        $string = trim($string);
        $string = stripslashes($string);
        $string = strip_tags($string);
        $string = str_replace(array('‘', '’', '“', '”'), array("'", "'", '"', '"'), $string);

        if ($trim)
            $string = substr($string, 0, $trim);
        if ($int)
            $string = preg_replace("/[^0-9\s]/", "", $string);
        if ($str)
            $string = preg_replace("/[^a-zA-Z\s]/", "", $string);

        return $string;
    }
	
    public static function sub($str, $len, $append = '...') {
        $strlen = strlen($str);
        if ($strlen <= $len) return $str;

        return (substr($str, 0, $len) . $append);
    }
	
    public static function url($str, $len=null) {
        if ($len != null) $str = self::sub($str, $len, '');
        $str = preg_replace("/[\W]+/i", '-', $str);
        $str = self::lower($str);
        return $str;
    }

    public static function camelize($lowerCaseAndUnderscoredWord) {
        return ucwords(str_replace('_', '', $lowerCaseAndUnderscoredWord));
    }
    
    public static function firstUpper($str) {
        $fc = mb_strtoupper(mb_substr($str, 0, 1));
        return $fc.mb_substr($str, 1);
    }

    public static function pr($var) {
        echo "<pre>";
        print_r($var);
        echo "</pre>";
    }

    public static function upper($string, $encoding = null) {
        return helperUpper($string, $encoding);
    }
    
    public static function lower($string, $encoding = null) {
        return helperLower($string, $encoding);
    }
    
    /**
    * firstUpperMB (Multibyte String)
    * 
    * @param mixed $string
    * @param mixed $encoding
    * @param boolean lower (эхний тэмдэгтээс бусадыг жижиг болгох эсэх)
    * @return string
    * @author Ulaankhuu Ts
    */    
    public static function firstUpperMB($string, $lower = false, $encoding = 'utf-8')
    {
        $first = mb_strtoupper(mb_substr($string, 0, 1, $encoding), $encoding);
        $last = mb_substr($string, 1,mb_strlen($string), $encoding);
        $result = $lower === false ? $first.$last : $first.mb_strtolower($last, $encoding);
        
        return $result;
    }         
    
    public static function firstUpperCutMB($string, $encoding = 'utf-8')
    {
        return mb_substr($string, 0, 1, $encoding);
    }
    
    public static function moreMB($string, $strCount = 20, $encoding = 'utf-8')
    {
        $moreString = $string;
        if (mb_strlen($string, $encoding) > $strCount) {
            $moreString = trim(mb_substr($string, 0, $strCount, $encoding)) . '...';
        }
        
        return $moreString;
    }         

    public static function truncate($string, $to) {
        $posicion = strpos($string, $to);
        if ($posicion !== false) {
            return mb_substr($string, 0, $posicion, 'utf-8');
        } else {
            return false;
        }
    }

    public static function round($num) {
        $count = strlen($num);
        if($count <= 3) return $num;
        if($count > 3 && $count < 6) return round(($num / 1000), 2) . "k";
        if($count == 6) return round(($num / 100000), 2) . "m";
        if($count >= 7) return round(($num / 1000000), 2) . "G";
    }

    public static function remove_doublewhitespace($s = null){
        return preg_replace('/([\s])\1+/', ' ', $s);
    }

    public static function remove_whitespace($s = null){
        return preg_replace('/[\s]+/', '', $s);
    }

    public static function remove_whitespace_feed($s = null){
        return preg_replace('/[\t\n\r\0\x0B]/', '', $s);
    }

    public static function smart_clean($s = null){
        return self::sanitize( trim( self::remove_doublewhitespace( self::remove_whitespace_feed($s) ) ) );
    }
    
    public static function random_string($type = 'alnum', $len = 8) {
        switch ($type) {
            case 'basic': 
                return mt_rand();
            break;
            case 'alnum':
            case 'numeric':
            case 'nozero':
            case 'alpha':
            case 'alphanumeric':    
                switch ($type) {
                    case 'alpha':	
                        $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    break;
                    case 'alnum':	
                        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ;:_-+=%#!*?[]';
                    break;
                    case 'alphanumeric':	
                        
                        $length = $len; 
                        $add_dashes = false; 
                        $available_sets = 'lud';    
                        $sets = array();
                        
                        if (strpos($available_sets, 'l') !== false) {
                            $sets[] = 'abcdefghijklmnopqrstuvwxyz';
                        }
                        if (strpos($available_sets, 'u') !== false) {
                            $sets[] = 'ABCDEFGHIJKLMNPQRSTUVWXYZ';
                        }
                        if (strpos($available_sets, 'd') !== false) {
                            $sets[] = '123456789';
                        }
                        if (strpos($available_sets, 's') !== false) {
                            $sets[] = '!@#$%&*?';
                        }

                        $all = $password = '';
                        
                        foreach ($sets as $set) {
                            $password .= $set[array_rand(str_split($set))];
                            $all .= $set;
                        }

                        $all = str_split($all);
                        for ($i = 0; $i < $length - count($sets); $i++) {
                            $password .= $all[array_rand($all)];
                        }

                        $password = str_shuffle($password);

                        if (!$add_dashes) {
                            return $password;
                        }

                        $dash_len = floor(sqrt($length));
                        $dash_str = '';
                        
                        while (strlen($password) > $dash_len) {
                            $dash_str .= substr($password, 0, $dash_len) . '-';
                            $password = substr($password, $dash_len);
                        }
                        
                        $dash_str .= $password;
                        
                        return $dash_str;
                    break;
                    case 'numeric':	
                        $pool = '0123456789';
                    break;
                    case 'nozero':	
                        $pool = '123456789';
                    break;
                }

                $str = '';
                for ($i=0; $i < $len; $i++) {
                    $str .= substr($pool, mt_rand(0, strlen($pool) -1), 1);
                }
                return $str;
            break;
            case 'unique':
            case 'md5':
                return md5(uniqid(mt_rand()));
            break;
        }
    }
        
    public static function randName() {
        $code = '';
        for ($x = 0; $x<6; $x++) {
            $code .= '-'.substr(strtoupper(sha1(rand(0,999999999999999))),2,6);
        }
        $code = substr($code,1);
        return $code;
    }
    
    public static function repeater($data, $num = 1) {
        return (($num > 0) ? str_repeat($data, $num) : '');
    }
	
    public static function clearSymbol($string) {
        $string = str_replace(array(',', '.'), '', $string);
        return self::sanitize($string);
    }
  
    public static function clearCommas($string) {
        $string = str_replace(',', '', $string);
        return self::sanitize($string);
    }
    
    public static function removeCharacter($string, $character) {
        $string = str_replace($character, '', $string);
        return $string;
    }
  
    public static function comma2point($string) {
        $string = str_replace(',', '.', $string);
        return self::sanitize($string);
    }
	
    public static function formatMoney($number, $fractional = false) { 
        if ($fractional) { 
            $number = sprintf('%.2f', $number); 
        } 
        while (true) { 
            $replaced = preg_replace('/(-?\d+)(\d\d\d)/', '$1,$2', $number); 
            if ($replaced != $number) { 
                $number = $replaced; 
            } else { 
                break; 
            } 
        } 
        return $number; 
    }
    
    public static function replace($needle, $replacement, $haystack) {
        $needle_len = mb_strlen($needle);
        $replacement_len = mb_strlen($replacement);
        $pos = mb_strpos($haystack, $needle);
        while ($pos !== false) {
            $haystack = mb_substr($haystack, 0, $pos) . $replacement
                    . mb_substr($haystack, $pos + $needle_len);
            $pos = mb_strpos($haystack, $needle, $pos + $replacement_len);
        }
        return $haystack; 
    } 
   
    public static function nlTobr($string) {
        $string = str_replace(array('\r\n', '\r', '\n', "\r\n", "\r", "\n"), '<br />', $string); 
        return $string; 
    }
    
    public static function nlToSpace($string) {
        $string = str_replace(array('\r\n', '\r', '\n', "\r\n", "\r", "\n"), ' ', $string); 
        return $string; 
    }
    
    public static function nlToSpaceNL($string) {
        $string = str_replace(array('\r\n', '\r', '\n', "\r\n", "\r", "\n"), ' '."\n", $string); 
        return $string; 
    }
    
    public static function removeNL($string) {
        $string = str_replace(array('\r\n', '\r', '\n', "\r\n", "\r", "\n"), '', $string); 
        return $string; 
    }
   
    public static function getSplitIndexValue($delimiter, $string, $index)
    {
        $explode = explode($delimiter, $string);
        if (isset($explode[$index])) {
            return $explode[$index];
        }
        return null;
    }
    
    static public function doubleQuoteToHtmlChar($string)
    {
        $string = str_replace('"', '&quot;', $string); 
        return $string;
    }
    
    static public function doubleQuoteToSingleQuote($string)
    {
        $string = str_replace('"', "'", $string); 
        return $string;
    }
    
    static public function htmlCharToDoubleQuote($string)
    {
        $string = str_replace('&quot;', '"', $string); 
        return $string;
    }
    
    static public function htmlCharToSingleQuote($string)
    {
        $string = str_replace('&#39;', "'", $string); 
        return $string;
    }
    
    static public function quoteToHtmlChar($string)
    {
        $string = str_replace(array('"', "'"), array('&quot;', '&#39;'), $string); 
        return $string;
    }
    
    static public function removeBr($string) {
        $string = str_replace(array('<br />', '<br/>', '<br>', '<br >'), '', $string); 
        return $string; 
    }
    
    static public function remove_querystring_var($url, $key) { 
	$url = preg_replace('/(.*)(?|&)' . $key . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&'); 
	$url = substr($url, 0, -1); 
	return $url; 
    }
    
    static public function simple_parse_str($str) {
        $out = array();
        $expl = explode("&", $str);
        foreach ($expl as $r) {
            if ($r != "") {
                $tmp = explode("=", $r);
                $out[$tmp[0]] = $tmp[1];
            }
        }
        return $out;
    }
    
    static public function cleanSpecialChar($string) {
        $clear = preg_replace('/[^A-Za-zФЦУЖЭНГШҮЗКЪЙЫБӨАХРОЛДПЯЧЁСМИТЬВЮЕЩфцужэнгшүзкъйыбөахролдпячёсмитьвюещ0-9\s]/', '', strip_tags(html_entity_decode($string)));
        return $clear; 
    }
    
    static public function cleanRecognizeChar($string) {  
        return preg_replace('/[^[:print:]]/', '', $string);
    }
    
    static public function urlCharReplace($string, $reverse = false) {
        $search_chars = array('/', '=', '+');
        $replace_chars = array('[z]', '[t]', '[n]');
        
        if (!$reverse) {
            $text = str_replace($search_chars, $replace_chars, $string);
        } else {
            $text = str_replace($replace_chars, $search_chars, $string);
        }

        return $text;
    }
    
    static public function urlCharAndReplace($string, $reverse = false) {
        $search_chars = array('/', '=', '+', '&');
        $replace_chars = array('[z]', '[t]', '[n]', '[and]');
        
        if (!$reverse) {
            $text = str_replace($search_chars, $replace_chars, $string);
        } else {
            $text = str_replace($replace_chars, $search_chars, $string);
        }

        return $text;
    }
    
    static public function filterLikePos($q, $glue = '*', $defaultMode = 'l') {
        
        $leftSubstr = mb_substr($q, 0, 1);
        $rightSubstr = mb_substr($q, -1);
                
        if ($leftSubstr == $glue && $rightSubstr == $glue) {
            $q = substr(substr($q, 0, -1), 1);
            $q = '%'.$q.'%';
        } elseif ($leftSubstr == $glue) {
            $q = substr($q, 1);
            $q = '%'.$q;
        } elseif ($rightSubstr == $glue) {
            $q = substr($q, 0, -1);
            $q = $q.'%';
        } else {
            if ($defaultMode == 'r') { /*right*/
                $q = $q.'%';
            } elseif ($defaultMode == 'l') { /*left*/
                $q = '%'.$q;
            } elseif ($defaultMode == 'b') { /*both*/
                $q = '%'.$q.'%';
            }
        }
        
        return $q;
    }
    
    static public function removeScriptTags($htmlContent) {
        
        $htmlContent = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $htmlContent);
        $htmlContent = preg_replace('#<iframe(.*?)>(.*?)</iframe>#is', '', $htmlContent);
        
        return $htmlContent;
    }
		
}