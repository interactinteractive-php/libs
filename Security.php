<?php if(!defined('_VALID_PHP')) exit('Direct access to this location is not allowed.');

/**
 * Security Class
 *
 * @package     IA PHPframework
 * @subpackage	Libraries
 * @category	Security
 * @author	B.Och-Erdene
 * @link	http://www.interactive.mn/PHPframework/Session
 */

class Security
{
    
    public static function html($string)
    {
        return htmlentities($string, ENT_QUOTES, "UTF-8");
    }
    
    public static function str($string, $int = false, $str = false, $trim = false)
    {
        if (is_array($string)) {
            foreach ($string as $key => $value) {
                $string[self::sanitize($key)] = self::sanitize(trim($value), $int, $str, $trim);
            }
        } else {
            
            $string = filter_var($string, FILTER_UNSAFE_RAW);
            $string = trim($string);
            $string = stripslashes($string);
            $string = strip_tags($string);
            $string = str_replace(array('‘', '’', '“', '”'), array("'", "'", '"', '"'), $string);

            if ($trim)
                $string = substr($string, 0, $trim);
            if ($str)
                $string = preg_replace("/[^a-zA-Z\s]/", "", $string);
        }

        return $string;
    }
    
    public static function filter($string)
    {
        if (is_array($string)) {
            foreach ($string as $key => $value) {
                $string[self::sanitize($key)] = self::filter(trim($value));
            }
        } else { 
            $string = trim($string);
            $string = str_replace(array('‘', '’', '“', '”'), array("'", "'", '"', '"'), $string);
        }

        return $string;
    }
    
    public static function float($float)
    {
        if (is_array($float)) {
            foreach ($float as $key => $value) {
                $float[self::float($key)] = self::float(trim($value));
            }
        } else {
            if (is_numeric($float) || is_float($float) || is_int($float) || is_integer($float)) {
                $float = (float) $float;
                return $float;
            }
        }
        return $float;
    }
    
    public static function integer($integer)
    {
        if (is_array($integer)) {
            foreach ($integer as $key => $value) {
                $integer[self::integer($key)] = self::integer(trim($value));
            }
        } else {
            if (is_numeric($integer) || is_float($integer) || is_int($integer) || is_integer($integer)) {
                $integer = (integer) $integer;
                return $integer;
            }
        }
        return $integer;
    }
    
    public static function sanitize($string, $int = false, $str = false, $trim = false)
    {
        if (is_array($string)) {
            foreach ($string as $key => $value) {
                if (is_array($string)) {
                    $string[self::sanitize($key)] = self::sanitize($value, $int, $str, $trim);
                } else {
                    $string[self::sanitize($key)] = self::sanitize($value, $int, $str, $trim);
                }
            }
        } else {
            
            $string = self::jsReplacer($string);
            $string = str_replace(array('‘', '’', '“', '”'), array("'", "'", '"', '"'), $string);
            //$string = htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
            $string = htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
            $string = str_replace('&amp;', '&', $string);
            $string = stripslashes($string);
            $string = strip_tags($string);
            $string = trim($string);
            $string = Str::remove_doublewhitespace($string);

            if ($trim)
                $string = substr($string, 0, $trim);
            if ($str)
                $string = preg_replace("/[^a-zA-Z\s]/", "", $string);
        }

        return $string;
    }
    
    public static function sanitizeWithDoubleSpace($string, $int = false, $str = false, $trim = false)
    {
        if (is_array($string)) {
            foreach ($string as $key => $value) {
                if (is_array($string)) {
                    $string[self::sanitize($key)] = self::sanitize($value, $int, $str, $trim);
                } else {
                    $string[self::sanitize($key)] = self::sanitize(trim($value), $int, $str, $trim);
                }
            }
        } else {
            
            $string = self::jsReplacer($string);
            $string = str_replace(array('‘', '’', '“', '”'), array("'", "'", '"', '"'), $string);
            //$string = htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
            $string = htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
            $string = str_replace('&amp;', '&', $string);
            $string = trim($string);
            $string = stripslashes($string);
            $string = strip_tags($string);

            if ($trim)
                $string = substr($string, 0, $trim);
            if ($str)
                $string = preg_replace("/[^a-zA-Z\s]/", "", $string);
        }

        return $string;
    }
    
    public static function jsReplacer($string) {
        
        if ($string === '' || $string === null) {
            return null;
        }
        
        $string = str_ireplace('&lt;p&gt;&lt;script', '&lt;p&gt;&lt;scrptrmvd', $string);
        $string = str_ireplace('lt;script', 'lt;scrptrmvd', $string);
        $string = str_ireplace('<script ', '<scrptrmvd ', $string);
        $string = str_ireplace('<script%20', '<scrptrmvd%20', $string);
        $string = str_ireplace('<script>', '<scrptrmvd>', $string);
        $string = str_ireplace('</script>', '</scrptrmvd>', $string);
        
        $string = str_ireplace('<applet ', '<аррlеt ', $string);
        $string = str_ireplace('<applet>', '<аррlеt>', $string);
        $string = str_ireplace('</applet>', '</аррlеt>', $string);
        
        $string = str_ireplace('javascript:', 'jаvаsсriрt:', $string);
        $string = str_ireplace('vbscript:', 'vbsсriрt:', $string);
        $string = str_ireplace('livescript:', 'livеsсriрt:', $string);
        
        $string = str_ireplace(' onabort', ' оnаbоrt', $string);
        $string = str_ireplace(' onactivate', ' оnасtivаtе', $string);
        $string = str_ireplace(' onafterprint', ' оnаftеrрrint', $string);
        $string = str_ireplace(' onafterupdate', ' оnаftеruрdаtе', $string);
        $string = str_ireplace(' onbeforeactivate', ' оnbеfоrеасtivаtе', $string);
        $string = str_ireplace(' onbeforecopy', ' оnbеfоrесорy', $string);
        $string = str_ireplace(' onbeforecut', ' оnbеfоrесut', $string);
        $string = str_ireplace(' onbeforedeactivate', ' оnbеfоrеdеасtivаtе', $string);
        $string = str_ireplace(' onbeforeeditfocus', ' оnbеfоrееditfосus', $string);
        $string = str_ireplace(' onbeforepaste', ' оnbеfоrераstе', $string);
        $string = str_ireplace(' onbeforeprint', ' оnbеfоrерrint', $string);
        $string = str_ireplace(' onbeforeunload', ' оnbеfоrеunlоаd', $string);
        $string = str_ireplace(' onbeforeupdate', ' оnbеfоrеuрdаtе', $string);
        $string = str_ireplace(' onblur', ' оnblur', $string);
        $string = str_ireplace(' onbounce', ' оnbоunсе', $string);
        $string = str_ireplace(' oncellchange', ' оnсеllсhаngе', $string);
        $string = str_ireplace(' onchange', ' оnсhаngе', $string);
        $string = str_ireplace(' onclick', ' оnсliсk', $string);
        $string = str_ireplace(' oncontextmenu', ' оnсоntеxtmеnu', $string);
        $string = str_ireplace(' oncontrolselect', ' оnсоntrоlsеlесt', $string);
        $string = str_ireplace(' oncopy', ' оnсорy', $string);
        $string = str_ireplace(' oncut', ' оnсut', $string);
        $string = str_ireplace(' ondataavailable', ' оndаtааvаilаblе', $string);
        $string = str_ireplace(' ondatasetchanged', ' оndаtаsеtсhаngеd', $string);
        $string = str_ireplace(' ondatasetcomplete', ' оndаtаsеtсоmрlеtе', $string);
        $string = str_ireplace(' ondblclick', ' оndblсliсk', $string);
        $string = str_ireplace(' ondeactivate', ' оndеасtivаtе', $string);
        $string = str_ireplace(' ondrag', ' оndrаg', $string);
        $string = str_ireplace(' ondragend', ' оndrаgеnd', $string);
        $string = str_ireplace(' ondragenter', ' оndrаgеntеr', $string);
        $string = str_ireplace(' ondragleave', ' оndrаglеаvе', $string);
        $string = str_ireplace(' ondragover', ' оndrаgоvеr', $string);
        $string = str_ireplace(' ondragstart', ' оndrаgstаrt', $string);
        $string = str_ireplace(' ondrop', ' оndrор', $string);
        $string = str_ireplace(' onerror', ' оnеrrоr', $string);
        $string = str_ireplace(' onerrorupdate', ' оnеrrоruрdаtе', $string);
        $string = str_ireplace(' onfilterchange', ' оnfiltеrсhаngе', $string);
        $string = str_ireplace(' onfinish', ' оnfinish', $string);
        $string = str_ireplace(' onfocus', ' оnfосus', $string);
        $string = str_ireplace(' onfocusin', ' оnfосusin', $string);
        $string = str_ireplace(' onfocusout', ' оnfосusоut', $string);
        $string = str_ireplace(' onhelp', ' оnhеlр', $string);
        $string = str_ireplace(' onkeydown', ' оnkеydоwn', $string);
        $string = str_ireplace(' onkeypress', ' оnkеyрrеss', $string);
        $string = str_ireplace(' onkeyup', ' оnkеyuр', $string);
        $string = str_ireplace(' onlayoutcomplete', ' оnlаyоutсоmрlеtе', $string);
        $string = str_ireplace(' onload', ' оnlоаd', $string);
        $string = str_ireplace(' onlosecapture', ' оnlоsесарturе', $string);
        $string = str_ireplace(' onmousedown', ' оnmоusеdоwn', $string);
        $string = str_ireplace(' onmouseenter', ' оnmоusееntеr', $string);
        $string = str_ireplace(' onmouseleave', ' оnmоusеlеаvе', $string);
        $string = str_ireplace(' onmousemove', ' оnmоusеmоvе', $string);
        $string = str_ireplace(' onmouseout', ' оnmоusеоut', $string);
        $string = str_ireplace(' onmouseover', ' оnmоusеоvеr', $string);
        $string = str_ireplace(' onmouseup', ' оnmоusеuр', $string);
        $string = str_ireplace(' onmousewheel', ' оnmоusеwhееl', $string);
        $string = str_ireplace(' onmove', ' оnmоvе', $string);
        $string = str_ireplace(' onmoveend', ' оnmоvееnd', $string);
        $string = str_ireplace(' onmovestart', ' оnmоvеstаrt', $string);
        $string = str_ireplace(' onpaste', ' оnраstе', $string);
        $string = str_ireplace(' onpropertychange', ' оnрrореrtyсhаngе', $string);
        $string = str_ireplace(' onreadystatechange', ' оnrеаdystаtесhаngе', $string);
        $string = str_ireplace(' onreset', ' оnrеsеt', $string);
        $string = str_ireplace(' onresize', ' оnrеsizе', $string);
        $string = str_ireplace(' onresizeend', ' оnrеsizееnd', $string);
        $string = str_ireplace(' onresizestart', ' оnrеsizеstаrt', $string);
        $string = str_ireplace(' onrowenter', ' оnrоwеntеr', $string);
        $string = str_ireplace(' onrowexit', ' оnrоwеxit', $string);
        $string = str_ireplace(' onrowsdelete', ' оnrоwsdеlеtе', $string);
        $string = str_ireplace(' onrowsinserted', ' оnrоwsinsеrtеd', $string);
        $string = str_ireplace(' onscroll', ' оnsсrоll', $string);
        $string = str_ireplace(' onselect', ' оnsеlесt', $string);
        $string = str_ireplace(' onselectionchange', ' оnsеlесtiоnсhаngе', $string);
        $string = str_ireplace(' onselectstart', ' оnsеlесtstаrt', $string);
        $string = str_ireplace(' onstart', ' оnstаrt', $string);
        $string = str_ireplace(' onstop', ' оnstор', $string);
        $string = str_ireplace(' onsubmit', ' оnsubmit', $string);
        $string = str_ireplace(' onunload', ' оnunlоаd', $string);

        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        /*$string = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $string);
        $string = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $string);
        $string = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $string);*/
        
        return $string;
    }
    
}