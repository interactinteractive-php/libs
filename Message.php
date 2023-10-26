<?php if(!defined('_VALID_PHP')) exit('Direct access to this location is not allowed.');

/**
 * Message Class
 *
 * @package     IA PHPframework
 * @subpackage	Libraries
 * @category	Message
 * @author	B.Och-Erdene
 * @link	http://www.interactive.mn/PHPframework/Message
 */

class Message  {
    
    private static $msgTypes = array('help', 'info', 'warning', 'success', 'error', 'red', 'danger');
    private static $msgWrapper = "<div class=\"alert alert-%s col\"><button class=\"close\" aria-hidden=\"true\" data-dismiss=\"alert\"></button>\n%s</div>\n";
    private static $msgBefore = '';
    private static $msgAfter = '';
    public static $msgType = '';

    /**
     * Add a message to the queue
     * 
     * @author B.Och-Erdene
     * 
     * @param  string   $type       
     * @param  string   $message     	
     * @param  string   $redirect_to
     * @return  bool 
     * 
     */
    public static function add($type, $message, $redirect_to) 
    {       
        Session::init();
        
        if (!array_key_exists('flash_messages', $_SESSION)) $_SESSION['flash_messages'] = array();

        if (!isset($_SESSION['flash_messages']) ) return false;
        
        //if( !isset($type) || !isset($message[0]) ) return false;

        if (strlen(trim($type)) == 1) {
            $type = str_replace(array('h', 'i', 'w', 'e', 's', 'red', 'd'), array('help', 'info', 'warning', 'error', 'success', 'red', 'danger'), $type);
        } elseif ($type == 'information') {
            $type = 'info';	
        }

        // Make sure it's a valid message type
        // if( !in_array($type, self::$msgTypes) ) die('"' . strip_tags($type) . '" is not a valid message type!' );

        // If the session array doesn't exist, create it
        if (!array_key_exists($type, $_SESSION['flash_messages'])) $_SESSION['flash_messages'][$type] = array();
        
        if (!isset($type) || !isset($message[0])) {
            self::clear();
        } else {
            $_SESSION['flash_messages'][$type][] = $message;
        }

        if ($redirect_to == 'back') {

            if (is_ajax_request()) {
                
                echo jsonResponse(array(
                    'status' => $type, 
                    'message' => $message,
                    'renderType' => 'pnotify'
                ));
                
            } else {
                
                if (isset($_SERVER['HTTP_REFERER'])) {
                    header("Location: " . $_SERVER['HTTP_REFERER']);
                } else {
                    echo '<script type="text/javascript">history.go(-1);</script>';
                }
            }
            
            exit();
            
        } elseif ($redirect_to == 'now') {
            
            header("location: http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
            exit();
            
        } else {
            
            if (headers_sent()) {
                echo "<script>document.location.href='$redirect_to';</script>\n"; exit();
            } else {
                @ob_end_clean();
                header ( 'HTTP/1.1 301 Moved Permanently' );
                header ( "Location: " . $redirect_to );
                exit();
            }
        }
    }

    /**
     * Display the queued messages
     * 
     * @author B.Och-Erdene
     * 
     * @param  string   $type
     * @param  bool     $print
     * @return mixed              
     * 
     */
    public static function display($type = 'all', $print = false)
    {
        if (!isset($_SESSION['flash_messages'])) return false;

        if ($type == 'g' || $type == 'growl') {
            //$this->displayGrowlMessages();
            return true;
        }
        
        $messages = '';
        $data = '';

        // Print a certain type of message?
        if (in_array($type, self::$msgTypes)) {
            foreach ($_SESSION['flash_messages'][$type] as $msg) {
                $messages .= self::$msgBefore . $msg . self::$msgAfter;
            }

            $data .= sprintf(self::$msgWrapper, $type, $messages);

            // Clear the viewed messages
            self::clear($type);
            
            // Print ALL queued messages
        } elseif ($type == 'all') {
            
            $flash_messages = $_SESSION['flash_messages'];
            
            foreach ($flash_messages as $msgType => $msgArray) {
                
                $messages = '';
                
                if ($msgType == '' && isset($msgArray[0])) {
                    $data .= $msgArray[0];
                } else {
                
                    foreach ($msgArray as $msg) {
                        $messages .= self::$msgBefore . $msg . self::$msgAfter;	
                    }

                    $data .= sprintf(self::$msgWrapper, $msgType, $messages);
                }
            }

            // Clear ALL of the messages
            self::clear();

        // Invalid Message Type?
        } else { 
            return false;
        }

        // Print everything to the screen or return the data
        if ($print) { 
            echo $data; 
        } else { 
            return $data; 
        }
    }

    /**
     * Check to  see if there are any queued error messages
     * 
     * @return bool  true  = There ARE error messages
     *               false = There are NOT any error messages
     * 
     */
    public static function hasErrors()
    { 
        return empty($_SESSION['flash_messages']['error']) ? false : true;	
    }

    /**
     * Check to see if there are any ($type) messages queued
     * 
     * @param  string   $type     The type of messages to check for
     * @return bool            	  
     * 
     */
    public static function hasMessages($type = null)
    {
        if (!is_null($type)) {
            if (!empty($_SESSION['flash_messages'][$type])) return $_SESSION['flash_messages'][$type];	
        } else {
            foreach (self::$msgTypes as $type) {
                if (!empty($_SESSION['flash_messages'])) return true;	
            }
        }
        return false;
    }

    /**
     * Clear messages from the session data
     * 
     * @param  string   $type     The type of messages to clear
     * @return bool 
     */
    public static function clear($type = 'all')
    { 
        if ($type == 'all') {
            unset($_SESSION['flash_messages']); 
        } else {
            unset($_SESSION['flash_messages'][$type]);
        }
        return true;
    }

    public function __toString() { return self::hasMessages(); }

    public function __destruct() {
        //$this->clear();
    }

}
