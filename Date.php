<?php if(!defined('_VALID_PHP')) exit('Direct access to this location is not allowed.');

/**
 * Date Class
 *
 * @package     IA PHPframework
 * @subpackage	Libraries
 * @category	Date
 * @author	B.Och-Erdene
 * @link	http://www.interactive.mn/PHPframework/Date
 */
 
class Date
{
    public static $prevDate = array();
    
    public static function format($format, $datetime, $translate = false) 
    {
        if (empty($datetime)) {
            return null;
        }
        $datetime = str_replace(
                    array("+", "T", "00:00:00 08:00", "00:00:00 09:00", "00:00:00 01:00"), 
                    array(" ", " ", "00:00:00",   "00:00:00" , "00:00:00"), $datetime);
        $date = new DateTime($datetime);
        $date = $date->format($format);
        
        if ($translate && class_exists('Lang')) {
            $date = str_replace(
                    array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"), 
                    array(Lang::line('date_monday'), Lang::line('date_tuesday'), Lang::line('date_wednesday'), Lang::line('date_thursday'), 
                        Lang::line('date_friday'), Lang::line('date_saturday'), Lang::line('date_sunday')), 
                    $date);

            $date = str_replace(
                    array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"), 
                    array(Lang::line('date_january'), Lang::line('date_febrary'), Lang::line('date_march'), Lang::line('date_april'), 
                        Lang::line('date_may'), Lang::line('date_june'), Lang::line('date_july'), Lang::line('date_august'), 
                        Lang::line('date_september'), Lang::line('date_october'), Lang::line('date_november'), Lang::line('date_december')), 
                    $date);
        }
        return $date;
    }
    
    /**
     * Date Class
     *
     * @package         IA PHPframework
     * @subpackage	Libraries
     * @category	PHP native date
     * @param           date $date
     * @param           string $format 
     * @author          Ts.Ulaankhuu
     */    
    public static function formatter($date, $format = 'Y-m-d') {
        return !empty($date) ? date($format, strtotime($date)) : '';
    }
    
    public static function nextDate($date, $days, $format = 'Y-m-d') {
        return date($format, strtotime($date . ' + ' . $days . ' days'));
    }    

    public static function betweenMonth($date1, $date2) {
        /*$d1 = new DateTime($date1);
        $d2 = new DateTime($date2);
        $interval = $d2->diff($d1);
        return $interval->format('%m');*/
        $date1 = strtotime(self::formatter($date1, 'Y-m-d'));
        $date2 = strtotime(self::formatter($date2, 'Y-m-d'));
        $months = 0;
        while (strtotime('+1 MONTH', $date1) < $date2) {
            $months++;
            $date1 = strtotime('+1 MONTH', $date1);
        }
        
        return $months.' '.Lang::line('date_month').', '. ($date2 - $date1) / (60*60*24).' '.Lang::line('date_day');
    }
    
    public static function diffDays($date1, $date2, $dateType = 'day') {
        $d1 = new DateTime(self::formatter($date1, 'Y-m-d'));
        $d2 = new DateTime(self::formatter($date2, 'Y-m-d'));
        $interval = $d2->diff($d1);
        
        if ($dateType == 'day') {
            return $interval->format('%d');
        } elseif ($dateType == 'month') {
            return $interval->format('%m');
        } elseif ($dateType == 'year') {
            return $interval->format('%y');
        }
    }
    
    public static function currentDate($format = 'Y-m-d H:i:s') {
        return date($format);
    }
    
    public static function currentTimestamp() {
        return time();
    }
    
    public static function timestampToDate($time, $format = 'Y-m-d H:i:s') {
        return !empty($time) ? date($format, $time) : '';
    }
    
    public static function beforeDate($format, $days) {
        return date($format, strtotime($days));
    }
    
    public static function weekdayAfter($format, $date, $day) {
        return date($format, strtotime($day, strtotime($date)));
    }
    
    public static function lastDay($format, $date, $day) {
        return date($format, strtotime('previous '.$day, strtotime($date)));
    }
    
    public static function monthList() {
        for ($i=1;$i<13;$i++) {
            $months[] = array('MONTH_ID' => $i , 'MONTH_NAME' => $i) ;
        }
        return $months ;
    }
    
    public static function sumTime($time1, $time2) {
        $times = array($time1, $time2);
        $seconds = 0;
        foreach ($times as $time) {
            list($hour,$minute,$second) = explode(':', $time);
            $seconds += $hour*3600;
            $seconds += $minute*60;
            $seconds += $second;
        }
        $hours = floor($seconds/3600);
        $seconds -= $hours*3600;
        $minutes  = floor($seconds/60);
        $seconds -= $minutes*60;
        // return "{$hours}:{$minutes}:{$seconds}";
        return sprintf('%02d:%02d', $hours, $minutes);
    }
    
    public static function dateRange( $first, $last, $step = '+1 day', $format = 'Y-m-d' ) {

        $dates = array();
        $current = strtotime( $first );
        $last = strtotime( $last );

        while( $current <= $last ) {
            $dates[] = date($format, $current);
            $current = strtotime($step, $current);
        }

        return $dates;
    }
    
    public static function diffMonths($date1, $date2) {
        $d1 = new DateTime(self::formatter($date1, 'Y-m').'-01');
        $d2 = new DateTime(self::formatter($date2, 'Y-m').'-01');
        $interval = $d2->diff($d1);
        return $interval->format('%m');
    }
    
    public function addWorkingDays($format, $sign = '+', $startDate, $adddays) {
        $retdate = $startDate;
        if ($adddays < 0) {
            $adddays = $adddays * -1;
            $sign = "-";
        }
        while ($adddays > 0) {
            $retdate = date('Y-m-d', strtotime("$retdate {$sign}1 day"));

            $what_day = date("N", strtotime($retdate));
            if ($what_day != 6 && $what_day != 7) // 6 and 7 are weekend
                $adddays--;
        };

        return date($format, strtotime($retdate));
    }
    
    public function getPrevDate($dateType) {
        
        if (!isset(Date::$prevDate[$dateType])) {
            
            if ($dateType == 'sysdate') {
                $result = date('Y-m-d');
            } elseif ($dateType == 'sysdatetime') {
                $result = date('Y-m-d H:i:s');
            } else {
                $result = date('Y-m-d H:i:s');
            }
            
            Date::$prevDate[$dateType] = $result;
            
            return $result;
            
        } else {
            return Date::$prevDate[$dateType];
        }
    }
    
    public function minutesToHumanReadable($inputMinutes) {
        
        $inputSeconds = $inputMinutes * 60;
        $secondsInAMinute = 60;
        $secondsInAnHour = 60 * $secondsInAMinute;
        $secondsInADay = 24 * $secondsInAnHour;

        // Extract days
        $days = floor($inputSeconds / $secondsInADay);

        // Extract hours
        $hourSeconds = $inputSeconds % $secondsInADay;
        $hours = floor($hourSeconds / $secondsInAnHour);

        // Extract minutes
        $minuteSeconds = $hourSeconds % $secondsInAnHour;
        $minutes = floor($minuteSeconds / $secondsInAMinute);

        // Extract the remaining seconds
        $remainingSeconds = $minuteSeconds % $secondsInAMinute;
        $seconds = ceil($remainingSeconds);

        // Format and return
        $timeParts = [];
        $sections = [
            Lang::line('date_day') => (int)$days,
            Lang::line('date_hour') => (int)$hours,
            Lang::line('date_minute') => (int)$minutes,
            Lang::line('date_second') => (int)$seconds
        ];
        
        if (Lang::getCode() == 'en') {
            
            foreach ($sections as $name => $value){
                if ($value > 0) {
                    $timeParts[] = $value.' '.$name.($value == 1 ? '' : 's');
                }
            }
        
        } else {
            
            foreach ($sections as $name => $value){
                if ($value > 0) {
                    $timeParts[] = $value.' '.$name;
                }
            }
        }

        return implode(', ', $timeParts);
    }

}