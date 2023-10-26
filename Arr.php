<?php if(!defined('_VALID_PHP')) exit('Direct access to this location is not allowed.');

/**
 * Array Class
 *
 * @package     IA PHPframework
 * @subpackage	Libraries
 * @category	Array
 * @author	B.Och-Erdene
 * @link	http://www.interactive.mn/PHPframework/Array
 */
 
class Arr {
    
    /**
    * Gets a dot-notated key from an array, with a default value if it does
    * not exist.
    *
    * @param   array   $array    The search array
    * @param   mixed   $key      The dot-notated key or array of keys
    * @param   string  $default  The default value
    * @return  mixed
    */
    public static function get($array, $key, $default = null)
    {
        if ( ! is_array($array) and ! $array instanceof \ArrayAccess) {
            return null;
        }

        if (is_null($key)) {
            return $array;
        }

        if (is_array($key)) {
            $return = array();
            foreach ($key as $k) {
                $return[$k] = static::get($array, $k, $default);
            }
            return $return;
        }

        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $key_part) {
            if (($array instanceof \ArrayAccess and isset($array[$key_part])) === false) {
                if ( ! is_array($array) or ! array_key_exists($key_part, $array)) {
                    return $default;
                }
            }

            $array = $array[$key_part];
        }

        return $array;
    }
    
    /**
    * Set an array item (dot-notated) to the value.
    *
    * @param   array   $array  The array to insert it into
    * @param   mixed   $key    The dot-notated key to set or array of keys
    * @param   mixed   $value  The value
    * @return  void
    */
    public static function set(&$array, $key, $value = null)
    {
        if (is_null($key)) {
            $array = $value;
            return;
        }
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                static::set($array, $k, $v);
            }
        } else {
            $keys = explode('.', $key);
            while (count($keys) > 1) {
                $key = array_shift($keys);
                if ( ! isset($array[$key]) or ! is_array($array[$key])) {
                    $array[$key] = array();
                }
                $array =& $array[$key];
            }
            $array[array_shift($keys)] = $value;
        }
    }
    
    /**
    * arrayUnigue()
    * 
    * @param array $array
    * @return array
    */
    public static function arrayUnigue($array = array(), $preserveKeys = false) { 
        
        $arrayRewrite = array();
        $arrayHashes = array();

        foreach($array as $key => $item) {
            $hash = md5(serialize($item));

            if (!isset($arrayHashes[$hash])) {
                $arrayHashes[$hash] = $hash;
                if ($preserveKeys) {
                    $arrayRewrite[$key] = $item;
                } else {
                    $arrayRewrite[] = $item;
                }
            }
        }
        
        return $arrayRewrite;
    }
    
    /**
    * Use upload multi files and merge input files.
    *
    * @param   array   $array  The array to insert it into
    * @return  array
    */
    public static function arrayFiles(&$file_post) 
    {
        $file_ary = array();
        $file_count = count($file_post['name']);
        $file_keys = array_keys($file_post);

        for ($i=0; $i<$file_count; $i++) {
            foreach ($file_keys as $key) {
                $file_ary[$i][$key] = $file_post[$key][$i];
            }
        }

        return $file_ary;
    }
    
    /**
    * Multidimensional array search
    * 
    * @param mixed $value
    * @param array $array
    * @param mixed $key
    * @return boolean
    * @author Ulaankhuu Ts
    */    
    public static function in_array_multi($value, $array, $key)
    {
        $top = sizeof($array) - 1;
        $bottom = 0;
        
        while ($bottom <= $top) {
            if ($array[$bottom][$key] == $value)
                return true;
            else 
                if (is_array($array[$bottom][$key]))
                    if (in_multiarray($value, ($array[$bottom][$key])))
                        return true;

            $bottom++;
        }        
        
        return false;
    }
    
    /**
    * Multidimensional array count
    * 
    * @param mixed $value
    * @param array $array
    * @param mixed $key
    * @return boolean
    * @author Ulaankhuu Ts
    */    
    public static function count_array_multi($value, $array,$key)
    {
        $top = sizeof($array) - 1;
        $bottom = 0;
        $counter = 0;
        
        while ($bottom <= $top) {
            if ($array[$bottom][$key] == $value)
                $counter++;
            else 
                if (is_array($array[$bottom][$key]))
                    if (in_multiarray($value, ($array[$bottom][$key])))
                        $counter++;

            $bottom++;
        }
        
        return $counter;
    }    
    
    /**
    * This is what I generally use for a clean list of items
    *
    * @param   array   $array  The array to insert it into
    * @param   mixed   $glue   separator
    * @return  string
    */
    public static function implode_r($glue, $array, $lastGlueRemove = false) 
    {
        $ret_str = '';
        
        if (count($array) > 1) {
            foreach ($array as $a) {
                if (is_array($a)) {
                    $ret_str .= isset($a[0]) ? $a[0].$glue : self::implode_r($glue, $a);
                } elseif ($a != '') {
                    $ret_str .= strval($a) . $glue;
                }
            }
        } else {
            foreach ($array as $a) {
                $ret_str .= (is_array($a)) ? self::implode_r($glue, $a) : strval($a);
            }
        }
        
        if ($lastGlueRemove) {
            $ret_str = rtrim($ret_str, $glue);
        }
            
        return $ret_str;
    }
    
    public static function implode_key($glue, $array, $key, $lastGlueRemove = false) 
    {
        $ret_str = '';
        
        if (is_array($array)) {
            
            $array = array_map(
                'unserialize',
                array_unique(
                    array_map(
                        'serialize',
                        $array
                    )
                )
            );
            
            $check = array();
            
            foreach ($array as $a) {
                
                if (isset($a[$key])) {
                    if (is_array($a[$key])) {
                        $ret_str .= self::implode_r($glue, $a[$key]);
                    } elseif (!empty($a[$key]) && !isset($check[$a[$key]])) {
                        $ret_str .= strval($a[$key]) . $glue;
                        $check[$a[$key]] = 1;
                    }
                }
            }
            
            if ($lastGlueRemove) {
                $ret_str = rtrim($ret_str, $glue);
            }
        }
        
        return $ret_str;
    }
    
    public static function implodeKeyNonUniq($glue, $array, $key, $lastGlueRemove = false) 
    {
        $ret_str = '';
        
        if (is_array($array)) {
            
            foreach ($array as $a) {
                
                if (is_array($a[$key])) {
                    $ret_str .= self::implode_r($glue, $a[$key]);
                } elseif (!empty($a[$key])) {
                    $ret_str .= strval($a[$key]) . $glue;
                }
            }
            
            if ($lastGlueRemove) {
                $ret_str = rtrim($ret_str, $glue);
            }
        }
        
        return $ret_str;
    }
    
    public static function implode_key_condition($glue, $array, $key, $keyCondition, $valueCondition, $lastGlueRemove = false) 
    {
        $ret_str = '';
        
        if (is_array($array)) {
            
            $array = array_map(
                'unserialize',
                array_unique(
                    array_map(
                        'serialize',
                        $array
                    )
                )
            );
            
            foreach ($array as $a) {
                if($a[$keyCondition] != $valueCondition)
                    $ret_str .= (is_array($a[$key])) ? self::implode_r($glue, $a[$key]) : strval($a[$key]) . $glue ;
            }
            
            if ($lastGlueRemove) {
                $ret_str = rtrim($ret_str, $glue);
            }
        }
        
        return $ret_str;
    }
    
    public static function group_by($arr, $key, $title) {
        $result = array();
        //$result2 = array();
        $n = 0;
        foreach ($arr as $i) {
            /*if (array_key_exists($key, $result)) {  
                if ($result[$key] != $i[$key]) {
                    $result[$key] = $i[$key];
                    $result2[$n] = array($key => $i[$key], $title => $i[$title]);
                    $n++;
                }
            } else {
                $result[$key] = $i[$key];
            }*/
            $result[$n] = array($key => $i[$key], $title => $i[$title]);
            $n++;
        }  
        $unique = array_map(
            'unserialize',
            array_unique(
                array_map(
                    'serialize',
                    $result
                )
            )
        );
        
        return $unique;
    }
    
    public static function array_change_key_case_ext(array $array, $case = 10, $useMB = false, $mbEnc = 'UTF-8') 
    {
        $newArray = array();
        
        //for more speed define the runtime created functions in the global namespace
        
        //get function
        if($useMB === false) {
            $function = 'strToUpper'; //default
            switch($case) {
                //first-char-to-lowercase
                case 25:
                    //maybee lcfirst is not callable
                    if(!function_exists('lcfirst')) 
                        $function = create_function('$input', '
                            return strToLower($input[0]) . substr($input, 1, (strLen($input) - 1));
                        ');
                    else $function = 'lcfirst';
                    break;
                
                //first-char-to-uppercase                
                case 20:
                    $function = 'ucfirst';
                    break;
                
                //lowercase
                case 10:
                    $function = 'strToLower';
            }
        } else {
            //create functions for multibyte support
            switch($case) {
                //first-char-to-lowercase
                case 25:
                    $function = create_function('$input', '
                        return mb_strToLower(mb_substr($input, 0, 1, \'' . $mbEnc . '\')) . 
                            mb_substr($input, 1, (mb_strlen($input, \'' . $mbEnc . '\') - 1), \'' . $mbEnc . '\');
                    ');
                    
                    break;
                
                //first-char-to-uppercase
                case 20:
                    $function = create_function('$input', '
                        return mb_strToUpper(mb_substr($input, 0, 1, \'' . $mbEnc . '\')) . 
                            mb_substr($input, 1, (mb_strlen($input, \'' . $mbEnc . '\') - 1), \'' . $mbEnc . '\');
                    ');
                    
                    break;
                
                //uppercase
                case 15:
                    $function = create_function('$input', '
                        return mb_strToUpper($input, \'' . $mbEnc . '\');
                    ');
                    break;
                
                //lowercase
                default: //case 10:
                    $function = create_function('$input', '
                        return mb_strToLower($input, \'' . $mbEnc . '\');
                    ');
            }
        }
        
        //loop array
        foreach($array as $key => $value) {
            if(is_array($value)) //$value is an array, handle keys too
                $newArray[$function($key)] = self::array_change_key_case_ext($value, $case, $useMB);
            elseif(is_string($key))
                $newArray[$function($key)] = $value;
            else $newArray[$key] = $value; //$key is not a string
        } //end loop
        
        return $newArray;
    }
    
    public static function changeKeyLower($arr)
    {
        return array_map(function($item){
            if (is_array($item))
                $item = Arr::changeKeyLower($item);
            return $item;
        },array_change_key_case($arr, CASE_LOWER));
    }
    
    public static function changeKeyUpper($arr)
    {
        return array_map(function($item){
            if (is_array($item))
                $item = Arr::changeKeyUpper($item);
            return $item;
        },array_change_key_case($arr, CASE_UPPER));
    }
    
    /**
    * Array advanced search
    * 
    * Use: Arr::search( array $array, string "key1 = 'val1' and key2 >= 'val2' or key3 != 'val3'", int $all = 0 );
    */
    
    public static function search($SearchArray, $query, $all = 0, $Return = 'direct')
    {
        $SearchArray = json_decode(json_encode($SearchArray), true);
        $ResultArray = array();

        if (is_array($SearchArray)) {
            $desen	= "@[\s*]?[\'{1}]?([a-zA-Z\ç\Ç\ö\Ö\ş\Ş\ı\İ\ğ\Ğ\ü\Ü[:space:]0-9-_]*)[\'{1}]?[\s*]?(\<\=|\>\=|\=|\!\=|\<|\>)\s*\'([a-zA-Z\ç\Ç\ö\Ö\ş\Ş\ı\İ\ğ\Ğ\ü\Ü[:space:]0-9-_]*)\'[\s*]?(and|or|\&\&|\|\|)?@si";
            $DonenSonuc	= preg_match_all($desen, $query, $Result);

            if ($DonenSonuc) {
                foreach ($SearchArray as $i => $ArrayElement) {
                    $SearchStatus = 0;
                    $EvalString = "";

                    for ( $r = 0; $r < count($Result[1]); $r++ ):

                        if ( $Result[2][$r] == '=' ) {
                            $Operator   = "==";
                        } elseif ( $Result[2][$r] == '!=' ) {
                            $Operator   = "!=";
                        } elseif ( $Result[2][$r] == '>=' ) {
                            $Operator   = ">=";
                        } elseif ( $Result[2][$r] == '<=' ) {
                            $Operator   = "<=";
                        } elseif ( $Result[2][$r] == '>' ) {
                            $Operator   = ">";
                        } elseif ( $Result[2][$r] == '<' ) {
                            $Operator   = "<";
                        } else {
                            $Operator   = "==";
                        }

                        $AndOperator    = "";

                        if ( $r != count ($Result[1]) - 1 ) {
                            $AndOperator = $Result[4][$r]?:'and';
                        }
                        $EvalString .= '("' . $ArrayElement[trim($Result[1][$r])] . '"' . $Operator . '"' . trim($Result[3][$r]) . '") ' . $AndOperator . ' ';
                    endfor;

                    eval('if( ' . $EvalString . ' ) $SearchStatus = 1;');

                    if ( $SearchStatus === 1 ) {
                        if ( $all === 1 ) {
                            if ( $Return == 'direct' ) :

                                $ResultArray[] = $ArrayElement;

                            elseif ( $Return == 'array' ) :

                                $ResultArray['index'][] = $i;
                                $ResultArray['array'][] = $ArrayElement;

                            endif;
                        } else {
                            if ( $Return == 'direct' ) :
                                $ResultArray = $i;
                            elseif ( $Return == 'array' ) :    
                                $ResultArray['index'] =	$i;
                            endif;

                            return $ResultArray;
                        }
                    }
                }

                if ( $all === 1 ){
                    return $ResultArray;
                }
            }
        }
        return false;
    }
    
    public static function multidimensional_search($parents, $searched)
    {
        if (empty($searched) || empty($parents)) {
            return false;
        }

        foreach ($parents as $key => $value) {
            $exists = true;
            foreach ($searched as $skey => $svalue) {
                $exists = ($exists && isset($parents[$key][$skey]) && $parents[$key][$skey] == $svalue);
            }
            if ($exists) { return $parents[$key]; }
        }

        return false;
    } 
    
    public static function getRowMultiDimensionalFilter($arr, $searched) {
        $arr = array_filter($arr, function($ar) use($searched) {
            //return ($ar['name'] == 'cat 1' AND $ar['id'] == '3');// you can add multiple conditions
        });
    }

    public static function multidimensional_list($parents, $searched)
    {
        if (empty($searched) || empty($parents)) {
            return false;
        }
        (Array) $array = array();
        $exists = true;
        
        $eval = '';
        
        foreach ($searched as $skey => $svalue) {
            $eval .= ' isset($parents[$key][\''.$skey.'\']) && $parents[$key][\''.$skey.'\'] == \''.$svalue.'\' &&';
        }
        
        $eval = trim($eval);
        $eval = rtrim($eval, '&&');
        
        foreach ($parents as $key => $value) {
            
            $isTrue = eval("return ($eval);");
            
            if ($isTrue) {
                array_push($array, $parents[$key]);
            }
        }
        
        return $array;
    } 
    
    public static function toZeroArray($arr)
    {
        if (!array_key_exists(0, $arr)) {
            $array = array(0 => $arr);
            return $array;   
        }
        return $arr;
    }
    
    /**
     * Массивийн түлхүүр буюу key - ээс KEY like хайлт хийн тохирох key үүд буцаана
     * 
     * Use: Arr::getSearchKeyFromArray((Array)$array, (String)'keyName');
     */
    public static function getSearchKeyFromArray($array, $prefix, $remplacePref = false)
    {
        $keys = array_keys($array);
        $result = array();

        foreach ($keys as $key) {
            if (strpos($key, $prefix) === 0) {
                if ($remplacePref === true) {
                    $result[str_replace($prefix, "", $key)] = $array[$key];
                } elseif ($remplacePref !== false && $remplacePref !== "") {
                    $result[str_replace($prefix, $remplacePref, $key)] = $array[$key];
                } else {
                    $result[$key] = $array[$key];
                }               
            }
        }
        return $result;
    }
    
    public static function checkSearchKeyFromArray($array, $prefix)
    {
        $keys = array_keys($array);

        foreach ($keys as $key) {
            if (strpos($key, $prefix) === 0) {
                return true;            
            }
        }
        
        return false;   
    }
    
    public static function array_contains_key( array $input_array, $search_value, $case_sensitive = false)
    {
        if ($case_sensitive)
            $preg_match = '/'.$search_value.'/';
        else
            $preg_match = '/'.$search_value.'/i';
        
        $return_array = array();
        $keys = array_keys($input_array);
        
        foreach ( $keys as $k ) {
            if (preg_match($preg_match, $k))
                $return_array[$k] = $input_array[$k];
        }
        
        return $return_array;
    }
    
    public static function array_insert($input, $offset, $replacement)
    {
        array_splice($input, $offset, 0, 0);
        $input[$offset] = $replacement;
        return $input;
    }
    
    public static function bubbleSort(array $array)
    {
        $array_size = count($array);
        for ($i = 0; $i < $array_size; $i++) {
            for ($j = 0; $j < $array_size; $j++) {
                if ($array[$i] < $array[$j]) {
                    $tem = $array[$i];
                    $array[$i] = $array[$j];
                    $array[$j] = $tem;
                }
            }
        }
        return $array;
    }
    
    public static function selectionSort(array $array)
    {
        $length = count($array);
        for ($i = 0; $i < $length; $i ++) {
            $min = $i;
            for ($j = $i + 1; $j < $length; $j++) {
                if ($array[$j] < $array[$min]) {
                    $min = $j;
                }
            }
            $tmp = $array[$min];
            $array[$min] = $array[$i];
            $array[$i] = $tmp;
        }
        return $array;
    }
    
    public static function insertionSort(array $array)
    {
        $count = count($array);
        for ($i = 1; $i < $count; $i++) {
            $j = $i - 1;
            // second element of the array
            $element = $array[$i];
            while ( $j >= 0 && $array[$j] > $element ) {
                $array[$j + 1] = $array[$j];
                $array[$j] = $element;
                $j = $j - 1;
            }
        }
        return $array;
    }
    
    public static function shellSort(array $array)
    {
        $gaps = array(1, 2, 3, 4, 6);
        $gap = array_pop($gaps);
        $length = count($array);
        
        while ( $gap > 0 ) {
            for ($i = $gap; $i < $length; $i++) {
                $tmp = $array[$i];
                $j = $i;
                while ( $j >= $gap && $array[$j - $gap] > $tmp ) {
                    $array[$j] = $array[$j - $gap];
                    $j -= $gap;
                }
                $array[$j] = $tmp;
            }
            $gap = array_pop($gaps);
        }
        return $array;
    }
    
    public static function combSort(array $array)
    {
        $gap = count($array);
        $swap = true;
        while ( $gap > 1 || $swap ) {
            if ($gap > 1)
                $gap /= 1.25;
            $swap = false;
            $i = 0;
            while ( $i + $gap < count($array) ) {
                if ($array[$i] > $array[$i + $gap]) {
                    // swapping the elements.
                    list($array[$i], $array[$i + $gap]) = array(
                            $array[$i + $gap],
                            $array[$i]
                    );
                    $swap = true;
                }
                $i++;
            }
        }
        return $array;
    }
    
    public static function mergeSort(array $array)
    {
        if (count($array) <= 1)
            return $array;

        $left = self::mergeSort(array_splice($array, floor(count($array) / 2)));
        $right = self::mergeSort($array);

        $result = array();

        while ( count($left) > 0 && count($right) > 0 ) {
            if ($left[0] <= $right[0]) {
                array_push($result, array_shift($left));
            } else {
                array_push($result, array_shift($right));
            }
        }
        while ( count($left) > 0 )
            array_push($result, array_shift($left));

        while ( count($right) > 0 )
            array_push($result, array_shift($right));

        return $result;
    }
    
    public static function quickSort(array $array)
    {
        if (count($array) == 0) {
            return $array;
        }
        $pivot = $array[0];
        $left = $right = array();
        for ($i = 1; $i < count($array); $i++) {
            if ($array[$i] < $pivot) {
                $left[] = $array[$i];
            } else {
                $right[] = $array[$i];
            }
        }
        return array_merge(self::quickSort($left), array(
                $pivot
        ), self::quickSort($right));
    }
    
    public static function permutationSort($items, $perms = array())
    {
        if (empty($items)) {
            if (self::inOrder($perms)) {
                return $perms;
            }
        } else {
            for ($i = count($items) - 1; $i >= 0; --$i) {
                $newitems = $items;
                $newperms = $perms;
                list($foo) = array_splice($newitems, $i, 1);
                array_unshift($newperms, $foo);
                $res = self::permutationSort($newitems, $newperms);
                if ($res) {
                    return $res;
                }
            }
        }
    }

    public static function inOrder($array)
    {
        for ($i = 0; $i < count($array); $i++) {
            if (isset($array[$i + 1])) {
                if ($array[$i] > $array[$i + 1]) {
                    return False;
                }
            }
        }
        return true;
    }
    
    public static function radixSort($array)
    {
        $n = count($array);
        $partition = array();

        for ($slot = 0; $slot < 256; ++$slot) {
            $partition[] = array();
        }

        for ($i = 0; $i < $n; ++$i) {
            $partition[$array[$i]->age & 0xFF][] = &$array[$i];
        }

        $i = 0;

        for ($slot = 0; $slot < 256; ++$slot) {
            for ($j = 0, $n = count($partition[$slot]); $j < $n; ++$j) {
                $array[$i++] = &$partition[$slot][$j];
            }
        }
        return $array;
    }
    
    /**
     * @name Mutlidimensional Array Sort Order
     * 
     * @uses $arr2 = Arr::array_msort($arr1, array('META_VALUE_CODE'=>SORT_DESC, 'META_VALUE_NAME'=>SORT_ASC));
     * @return array
     */
    public static function array_msort(array $array, $cols)
    {
        $colarr = array();
        foreach ($cols as $col => $order) {
            $colarr[$col] = array();
            foreach ($array as $k => $row) { $colarr[$col]['_'.$k] = strtolower($row[$col]); }
        }
        $eval = 'array_multisort(';
        foreach ($cols as $col => $order) {
            $eval .= '$colarr[\''.$col.'\'],'.$order.',';
        }
        $eval = substr($eval,0,-1).');';
        eval($eval);
        $ret = array();
        foreach ($colarr as $col => $arr) {
            foreach ($arr as $k => $v) {
                $k = substr($k,1);
                if (!isset($ret[$k])) $ret[$k] = $array[$k];
                $ret[$k][$col] = $array[$k][$col];
            }
        }
        return $ret;
    }
    
    /**
     * @name Mutlidimensional Array Natural Sort Order
     * 
     * @uses $arr2 = Arr::naturalsort($arr1, array('META_VALUE_CODE'));
     * @return array
     */
    public static function naturalsort($array, $key)
    {
        if (is_array($array) && count($array) > 0) {
            if (!empty($key)) {
                $mapping = array();
                foreach ($array as $k => $v) {
                    $sort_key = '';
                    if (!is_array($key)) {
                        $sort_key = $v[$key];
                    } else {
                        foreach ($key as $key_key) {
                            $sort_key .= $v[$key_key];
                        }
                    }
                    $mapping[$k] = $sort_key;
                }
                natsort($mapping);
                $sorted = array();
                foreach ($mapping as $k => $v) {
                    $sorted[] = $array[$k];
                }
                return $sorted;
            }
        }
        return $array;
    }
    
    public static function array_natsort_list($array)
    {
        // for all arguments without the first starting at end of list
        for ($i=func_num_args(); $i > 1; $i--) {
            // get column to sort by
            $sort_by = func_get_arg($i-1);
            // clear arrays
            $new_array = array();
            $temporary_array = array();
            // walk through original array
            foreach($array as $original_key => $original_value) {
                // and save only values
                $temporary_array[] = $original_value[$sort_by];
            }
            // sort array on values
            natsort($temporary_array);
            // delete double values
            $temporary_array = array_unique($temporary_array);
            // walk through temporary array
            foreach ($temporary_array as $temporary_value) {
                // walk through original array
                foreach ($array as $original_key => $original_value) {
                    // and search for entries having the right value
                    if ($temporary_value == $original_value[$sort_by]) {
                        // save in new array
                        $new_array[$original_key] = $original_value;
                    }
                }
            }
            // update original array
            $array = $new_array;
        }
        return $array;
    }
    
    public static function sort2d($array, $index, $order='asc', $natsort = false, $case_sensitive = false)
    {
        if (is_array($array) && count($array) > 0) {
            foreach (array_keys($array) as $key)
                $temp[$key] = $array[$key][$index];
            
                if (!$natsort) ($order == 'asc')? asort($temp) : arsort($temp);
                else {
                    ($case_sensitive) ? natsort($temp) : natcasesort($temp);
                if ($order != 'asc')
                $temp = array_reverse($temp, true);
            }
            foreach (array_keys($temp) as $key)
                (is_numeric($key)) ? $sorted[] = $array[$key] : $sorted[$key] = $array[$key];
            return $sorted;
        }
        return $array;
    }
    
    public static function sortBy($field, $array, $direction = 'asc')
    {   
        if ($direction == 'asc') {
            
            usort($array, create_function('$a, $b', '
                return strnatcmp($a["' . $field . '"], $b["' . $field . '"]);
            '));
            
        } else {
            
            usort($array, create_function('$a, $b', '
                return strnatcmp($b["' . $field . '"], $a["' . $field . '"]);
            '));
        }

        return $array;
    }

    /**
    * @name Mutlidimensional Array Sorter.
    *
    * This function can be used for sorting a multidimensional array by sql like order by clause
    * 
    * @uses $order_by_name = sort_array_multidim($array,'firstname DESC, surname ASC');
    * @param mixed $array
    * @param mixed $order_by
    * @return array
    */
    public static function sort_array_multidim(array $array, $order_by)
    {
        if(!is_array($array[0]))
            throw new Exception('$array must be a multidimensional array!', E_USER_ERROR);
        
        $columns = explode(',',$order_by);
        foreach ($columns as $col_dir) {
            if (preg_match('/(.*)([\s]+)(ASC|DESC)/is',$col_dir,$matches)) {
                if (!array_key_exists(trim($matches[1]),$array[0]))
                    trigger_error('Unknown Column <b>' . trim($matches[1]) . '</b>',E_USER_NOTICE);
                else {
                    if (isset($sorts[trim($matches[1])]))
                        trigger_error('Redundand specified column name : <b>' . trim($matches[1] . '</b>'));
                    $sorts[trim($matches[1])] = 'SORT_'.strtoupper(trim($matches[3]));
                }
            } else {
                throw new Exception("Incorrect syntax near : '{$col_dir}'",E_USER_ERROR);
            }
        }
        $colarr = array();
        foreach ($sorts as $col => $order) {
            $colarr[$col] = array();
            foreach ($array as $k => $row) {
                $colarr[$col]['_'.$k] = strtolower($row[$col]);
            }
        }
        $multi_params = array();
        foreach ($sorts as $col => $order) {
            $multi_params[] = '$colarr[\'' . $col .'\']';
            $multi_params[] = $order;
        }
        $rum_params = implode(',',$multi_params);
        eval("array_multisort({$rum_params});");
        $sorted_array = array();
        foreach ($colarr as $col => $arr) {
            foreach ($arr as $k => $v) {
                $k = substr($k,1);
                if (!isset($sorted_array[$k]))
                    $sorted_array[$k] = $array[$k];
                $sorted_array[$k][$col] = $array[$k][$col];
            }
        }
        return array_values($sorted_array);
    } 
    
    public static function ksort_recursive(&$array) {
        ksort($array);
        foreach ($array as &$a) {
            is_array($a) && self::ksort_recursive($a);
        }
    }
    
    public static function changeKeyName(array $array, $old_key, $new_key) 
    {
        if (!array_key_exists($old_key, $array)) {
            return $array;
        }

        $keys = array_keys($array);
        $keys[array_search($old_key, $keys)] = $new_key;

        return array_combine($keys, $array);
    }
    
    public static function multiDimensionalChangeKeyName($array, $oldkey, $newkey)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = self::multiDimensionalChangeKeyName($value, $oldkey, $newkey);
            } else {
                $array[$newkey] = $array[$oldkey];    
            }
        }
        
        unset($array[$oldkey]); 
        
        return $array;   
    }
    
    /**
     * Arr::multi_rename_key($tags, "url", "value");
     * Arr::multi_rename_key($tags, array("url","name"), array("value","title"));
     */
    public static function multi_rename_key(&$array, $old_keys, $new_keys)
    {
        if (!is_array($array)) {
            ($array == '') ? $array = array() : false;
            return $array;
        }
        foreach ($array as &$arr) {
            if (is_array($old_keys)) {
                foreach ($new_keys as $k => $new_key) {
                    (isset($old_keys[$k])) ? true : $old_keys[$k]=NULL;
                    $arr[$new_key] = (isset($arr[$old_keys[$k]]) ? $arr[$old_keys[$k]] : null);
                    unset($arr[$old_keys[$k]]);
                }
            } else {
                $arr[$new_keys] = (isset($arr[$old_keys]) ? $arr[$old_keys] : null);
                unset($arr[$old_keys]);
            }
        }
        return $array;
    }
    
    public static function objectToArray($d)
    {  
        if (is_object($d)) {
            // Gets the properties of the given object
            // with get_object_vars function
            $d = get_object_vars($d);
        }
        if (is_array($d)) {
            /*
            * Return array converted to object
            * Using __FUNCTION__ (Magic constant)
            * for recursive call
            */
            return array_map(__FUNCTION__, $d);
        } else {
            // Return array
            return $d;
        }
    }
    
    public static function convertDeToArray($pDataElement, &$pArray) 
    {
        if (!isset($pDataElement->elements) || (is_countable($pDataElement->elements) && count($pDataElement->elements) == 0)) {
            $pArray[$pDataElement->key] = $pDataElement->value;
        } else {
            $newArray = array();
            if (is_array($pDataElement->elements)) {
                $count = count($pDataElement->elements);
                for ($i = 0; $i < $count; $i++) {
                    self::convertDeToArray($pDataElement->elements[$i], $newArray);
                }
            } else {
                self::convertDeToArray($pDataElement->elements, $newArray);
            }
            $pArray[$pDataElement->key] = $newArray;
        }
    }
    
    public static function convertDeArrayToArray($pDataElement, &$pArray) 
    {
        if (!isset($pDataElement['elements']) || (is_countable($pDataElement['elements']) && count($pDataElement['elements']) == 0)) {
            $pArray[$pDataElement['key']] = $pDataElement['value'];
        } else {
            $newArray = array();
            if (is_array($pDataElement['elements'])) {
                $count = count($pDataElement['elements']);
                for ($i = 0; $i < $count; $i++) {
                    self::convertDeArrayToArray($pDataElement['elements'][$i], $newArray);
                }
            } else {
                self::convertDeArrayToArray($pDataElement['elements'], $newArray);
            }
            $pArray[$pDataElement['key']] = $newArray;
        }
    }     
  
    public static function arrayToDataElement($array)
    {
        $keys = array_keys($array);
        $de = array();
        $de_child = array();
        
        foreach ($keys as $k => $kv) {
            if (!is_array($array[$kv])) {
                array_push($de, array(
                    'key' => $kv,
                    'value' => $array[$kv]
                ));
            } else {
                $ii = count($array[$kv]);
                if (isset($array[$kv][0]) && $ii != count($array[$kv], COUNT_RECURSIVE)) {
                    $de_child = array();
                    
                    foreach ($array[$kv] as $ck => $ckv) {
                        if(!is_array($ckv))
                            array_push($de_child, array(
                                'key' => $ck,
                                'value' => $ckv
                            ));
                        else
                            array_push($de_child, array(
                                    'key' => $ck,
                                    'elements' => static::arrayToDataElement($ckv)
                                )
                            );
                    }
                } else {
                    $de_child = static::arrayToDataElement($array[$kv]);                    
                }
                
                array_push($de, array(
                    'key' => $kv,
                    'elements' => $de_child
                ));                
            }
        }
        
        return $de;
    }
    
    public static function compress($array) { 
        return gzcompress(var_export($array, true), 9); 
    }
    
    public static function decompress($content) { 
        eval('$array='.(empty($content) ? 'array()' : gzuncompress($content)).';'); 
        return $array; 
    }   
    
    public static function encode($array) {
        
        $json = json_encode($array, JSON_UNESCAPED_UNICODE);
        $compressed = gzdeflate($json, 9);
        $compressed = base64_encode($compressed);
        
        //$s = strtr(base64_encode(addslashes(gzcompress(serialize($stringArray), 9))), '+/=', '-_,');
        return $compressed;
    }

    public static function decode($string) {

        $string = base64_decode($string);
        $string = @gzinflate($string);
        $array = array();
        
        if (Json::isJson($string)) {
            $array = Json::decode($string);
        }
        
        //$s = unserialize(gzuncompress(stripslashes(base64_decode(strtr($stringArray, '-_,', '+/=')))));
        return $array;
    }
    
    public static function groupByArray($rows, $key) {
        $result = array();
        
        foreach ($rows as $data) {
            $id = $data[$key];
            if (isset($result[$id])) {
                $result[$id]['rows'][] = $data;
            } else {
                $result[$id]['rows'] = array($data);
                $result[$id]['row'] = $data;
            }
        }
        
        return $result;
    }
    
    public static function groupByArrayOnlyRows($rows, $key) {
        $result = array();
        
        foreach ($rows as $data) {
            $id = Str::sanitize($data[$key]);
            if (isset($result[$id])) {
                $result[$id][] = $data;
            } else {
                $result[$id] = array($data);
            }
        }
        
        return $result;
    }
    
    public static function groupByArrayRowByKey($rows, $key) {
        $result = array();
        
        foreach ($rows as $data) {
            $result[$key] = $data[$key];
        }
        
        return $result;
    } 
    
    public static function groupByArrayOnlyRow($rows, $key, $key1) {
        $result = array();
        
        foreach ($rows as $data) {
            $id = $data[$key];
            if (!isset($result[$id])) {
                $result[$id] = ($key1) ? $data[$key1] : $data;
            } 
        }
        
        return $result;
    } 
    
    public static function groupByArrayOnlyKey($rows, $key) {
        $result = array();
        
        foreach ($rows as $data) {
            $id = $data[$key];
            if (!isset($result[$id])) {
                $result[$id] = $id;
            } 
        }
        
        return $result;
    }   
    
    public static function groupByArrayByNullKey($rows, $key) {
        $result = array();
        
        foreach ($rows as $data) {
            $id = $data[$key] == '' ? 'яяяrow' : $data[$key];
            if (isset($result[$id])) {
                $result[$id]['rows'][] = $data;
            } else {
                $result[$id]['rows'] = array($data);
                $result[$id]['row'] = $data;
            }
        }
        
        return $result;
    }
    
    public static function assignArrayByPath(&$arr, $path, $value, $separator = '.') {
        $keys = explode($separator, $path);

        foreach ($keys as $key) {
            $arr = &$arr[$key];
        }

        $arr = $value;
    }
    
    public static function groupByArrayLower($rows, $key) {
        $result = array();
        
        foreach ($rows as $data) {
            $id = strtolower($data[$key]);
            if (isset($result[$id])) {
                $result[$id]['rows'][] = $data;
            } else {
                $result[$id]['rows'] = array($data);
                $result[$id]['row'] = $data;
            }
        }
        
        return $result;
    }    
    
    public static function array_to_xml($data, &$xml_data) {
        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                $key = 'item' . $key; //dealing with <0/>..<n/> issues
            }
            if (is_array($value)) {
                $subnode = $xml_data->addChild($key);
                self::array_to_xml($value, $subnode);
            } else {
                $xml_data->addChild("$key", htmlspecialchars("$value"));
            }
        }

        return $xml_data;
    }
    
    public static function buildTree(array $elements, $parentId = 0, $idField, $parentField) {
        $branch = array();

        foreach ($elements as $element) {
            if ($element[$parentField] == $parentId) {
                $children = self::buildTree($elements, $element[$idField], $idField, $parentField);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }

        return $branch;
    }
    
    public static function getNextKeyArray($array, $key) {
        $keys = array_keys($array);
        $position = array_search($key, $keys);
        if (isset($keys[$position + 1])) {
            $nextKey = $keys[$position + 1];
        }
        return isset($nextKey) ? $nextKey : null;
    }
    
}
