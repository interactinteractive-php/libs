<?php if(!defined('_VALID_PHP')) exit('Direct access to this location is not allowed.');

class View {

    public function __construct()
    {
        global $db, $lang;
        
        $this->lang = $lang;
        $this->db = $db;
        
        $this->ws = new WebService();
    }

    public function render($name, $path = 'views/')
    {
        if (isset($GLOBALS['MAIN_BASEPATH'])) {
            $path = $GLOBALS['MAIN_BASEPATH'] . $path; 
        }
        
        require $path.$name.'.php';    
    }
    
    public function renderPrint($name, $path = 'views/') 
    {
        ob_start();
        
        if (isset($GLOBALS['MAIN_BASEPATH'])) {
            $path = $GLOBALS['MAIN_BASEPATH'] . $path; 
        }
        
        require $path.$name.'.php';
        $contents = ob_get_clean();
        return $contents;
    }

}