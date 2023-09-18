<?php if(!defined('_VALID_PHP')) exit('Direct access to this location is not allowed.');

/**
 * Number Class
 *
 * @package     IA PHPframework
 * @subpackage	Libraries
 * @category	Number
 * @author	B.Och-Erdene
 * @link	http://www.interactive.mn/PHPframework/Number
 */
 
class Number {
        
    /**
    * formatMoney()
    * 
    * @param mixed $number
    * @return
    */
    public static function formatMoney($number, $fractional = false)
    { 
        $number = Str::clearCommas($number);

        if (is_numeric($number)) {
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
        }
        return $number; 
    }
    
    public static function amount($number)
    {
        $number = Str::clearCommas($number);

        if (is_numeric($number)) {
            $number = sprintf('%.2f', $number); 
            $number = rtrim(rtrim(rtrim($number,'0'),'0'),'.');
            
            while (true) { 
                $replaced = preg_replace('/(-?\d+)(\d\d\d)/', '$1,$2', $number); 
                if ($replaced != $number) { 
                    $number = $replaced; 
                } else { 
                    break; 
                } 
            } 
        }
        return $number; 
    }

    public static function decimal($number)
    { 
        if ($number == '' || $number === null) {
            return null;
        }
        
        $number = Str::clearCommas($number);
        if (is_numeric($number)) {
            return $number;
        }
        return 0; 
    }
    
    public static function numeric($number)
    {   
        $number = str_replace(',', '', $number);
        if (is_numeric($number)) {
            return $number;
        }
        return 0; 
    }

    public static function numberFormat($number, $decimal)
    { 
        if (is_numeric($number)) {
            $number = number_format($number, $decimal, '.', '');
        }
        
        return $number;
    }
    
    public static function fractionRange($number, $range)
    { 
        $numberResult = number_format($number, $range, '.', ',');
        return $numberResult;
    }
    
    public static function trimTrailingZeroes($nbr)
    {
        return strpos($nbr, '.') !== false ? rtrim(rtrim($nbr, '0'), '.') : $nbr;
    }
    
    public static function bigNumberFormat($nbr, $scale = 2)
    {   
        if ($nbr && strpos($nbr, '.') !== false) {
            
            $number = Number::trimTrailingZeroes($nbr);
            
            if (strpos($number, '.') !== false) {
                
                $explode = explode('.', $number);
            
                if (strlen($explode[1]) > $scale) {
                    return number_format($nbr, $scale, '.', '');
                }
            }
            
            return $number;
        }
        
        return $nbr;
    }

    /**
     * Converts a file size number to a byte value
     *
     * Usage:
     * <code>
     * echo Number::bytes('200K');  // 204800
     * echo Number::bytes('5MiB');  // 5242880
     * echo Number::bytes('1000');  // 1000
     * echo Number::bytes('2.5GB'); // 2684354560
     * </code>
     *
     * @param      string   file size in SB format
     * @return     float
     */
    public static function bytes($size = 0)
    {
        // Prepare the size
        $size = trim((string) $size);
        $byte_units = self::byte_units();
        // Construct an OR list of byte units for the regex
        $accepted = implode('|', array_keys($byte_units));

        // Construct the regex pattern for verifying the size format
        $pattern = '/^([0-9]+(?:\.[0-9]+)?)('.$accepted.')?$/Di';

        // Verify the size format and store the matching parts
        if (!preg_match($pattern, $size, $matches)) {
            throw new \Exception('The byte unit size, "'.$size.'", is improperly formatted.');
        }

        // Find the float value of the size
        $size = (float) $matches[1];

        // Find the actual unit, assume B if no unit specified
        $unit = Arr::get($matches, 2, 'B');

        // Convert the size into bytes
        $bytes = $size * pow(2, $byte_units[$unit]);

        return $bytes;
    }

    public static function byte_units()
    {
        return array(
            'B'   => 0,
            'K'   => 10,
            'Ki'  => 10,
            'KB'  => 10,
            'KiB' => 10,
            'M'   => 20,
            'Mi'  => 20,
            'MB'  => 20,
            'MiB' => 20,
            'G'   => 30,
            'Gi'  => 30,
            'GB'  => 30,
            'GiB' => 30,
            'T'   => 40,
            'Ti'  => 40,
            'TB'  => 40,
            'TiB' => 40,
            'P'   => 50,
            'Pi'  => 50,
            'PB'  => 50,
            'PiB' => 50,
            'E'   => 60,
            'Ei'  => 60,
            'EB'  => 60,
            'EiB' => 60,
            'Z'   => 70,
            'Zi'  => 70,
            'ZB'  => 70,
            'ZiB' => 70,
            'Y'   => 80,
            'Yi'  => 80,
            'YB'  => 80,
            'YiB' => 80,
       );
    }

    /**
     * Converts a number of bytes to a human readable number
     *
     * @param   integer
     * @param   integer
     * @return  boolean|string
     */
    public static function format_bytes($bytes = 0, $decimals = 0)
    {
        $quant = array(
            'TB' => 1099511627776,  // pow( 1024, 4)
            'GB' => 1073741824,     // pow( 1024, 3)
            'MB' => 1048576,        // pow( 1024, 2)
            'KB' => 1024,           // pow( 1024, 1)
            'B ' => 1,              // pow( 1024, 0)
        );

        foreach ($quant as $unit => $mag) {
            if (doubleval($bytes) >= $mag) {
                return sprintf('%01.'.$decimals.'f', ($bytes / $mag)).' '.$unit;
            }
        }

        return false;
    }

    /**
     * Formats a number by injecting non-numeric characters in a specified
     * format into the string in the positions they appear in the format.
     *
     * Usage:
     * <code>
     * echo Number::format('1234567890', '(000) 000-0000'); // (123) 456-7890
     * echo Number::format('1234567890', '000.000.0000'); // 123.456.7890
     * </code>
     *
     * @param   string     the string to format
     * @param   string     the format to apply
     * @return  string
     */
    public static function format($string = '', $format = '')
    {
        if (empty($format) or empty($string)) {
            return $string;
        }

        $result = '';
        $fpos = 0;
        $spos = 0;

        while ((strlen($format) - 1) >= $fpos) {
            if (ctype_alnum(substr($format, $fpos, 1))) {
                $result .= substr($string, $spos, 1);
                $spos++;
            } else {
                $result .= substr($format, $fpos, 1);
            }

            $fpos++;
        }

        return $result;
    }

    /**
     * Transforms a number by masking characters in a specified mask format, and
     * ignoring characters that should be injected into the string without
     * matching a character from the original string (defaults to space).
     *
     * Usage:
     * <code>
     * echo Number::mask_string('1234567812345678', '************0000'); ************5678
     * echo Number::mask_string('1234567812345678', '**** **** **** 0000'); // **** **** **** 5678
     * echo Number::mask_string('1234567812345678', '**** - **** - **** - 0000', ' -'); // **** - **** - **** - 5678
     * </code>
     *
     * @param   string     the string to transform
     * @param   string     the mask format
     * @param   string     a string (defaults to a single space) containing characters to ignore in the format
     * @return  string     the masked string
     */
    public static function mask_string($string = '', $format = '', $ignore = ' ')
    {
        if (empty($format) or empty($string)) {
            return $string;
        }

        $result = '';
        $fpos = 0;
        $spos = 0;

        while ((strlen($format) - 1) >= $fpos) {
            if (ctype_alnum(substr($format, $fpos, 1))) {
                $result .= substr($string, $spos, 1);
                $spos++;
            } else {
                $result .= substr($format, $fpos, 1);

                if (strpos($ignore, substr($format, $fpos, 1)) === false) {
                    ++$spos;
                }
            }

            ++$fpos;
        }

        return $result;
    }

    /**
     * Formats a phone number.
     *
     * @param   string the unformatted phone number to format
     * @param   string the format to use, defaults to '(000) 000-0000'
     * @return  string the formatted string
     * @see     format
     */
    public static function format_phone($string = '', $format = null)
    {
        is_null($format) and $format = '(000) 00000000';
        return static::format($string, $format);
    }

    public static function formatDecimal($number)
    {
        if (!empty($number)) {
            $number = number_format($number, 2, '.', '');
            $numberExt = substr($number, strrpos($number, '.') + 1);
            if ($numberExt == '00') {
                $number = number_format($number, 0, '.', '');
            }
        }
        return $number;
    }

    public static function diffPercent($number1, $number2)
    {
        if ($number1 == $number2) {
            return 0;
        }
        if ($number1 > $number2) {
            return (($number1 - $number2) / $number1)*100;
        }
        return (($number2 - $number1) / $number2)*100;
    }
    
    public static function truncate($value, $precision = 2)
    {
        //Casts provided value
        $value = (string)$value;

        //Gets pattern matches
        preg_match( "/(-+)?\d+(\.\d{1,".$precision."})?/" , $value, $matches );

        //Returns the full pattern match
        return $matches[0];        
    }
    
    public static function separatorNumbers($separator, $numbers)
    {
        $arr = array();
        
        if ($numbers) {
            $numbersArr = explode($separator, $numbers);
            foreach ($numbersArr as $number) {
                $number = trim($number);
                if (is_numeric($number)) {
                    $arr[] = $number;
                }
            }
        }
        
        return implode($separator, $arr);
    }

}