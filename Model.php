<?php if(!defined('_VALID_PHP')) exit('Direct access to this location is not allowed.');

class Model {
    
    public function __construct()
    {
        global $db, $lang;
        
        $this->lang = $lang;
        $this->db = $db;
        
        $this->ws = new WebService();
        $this->load = new Loader();
        
        if (!isset($this->view)) {
            $this->view = new View();
        }
    }
    
    public function __get($key)
    {
        return getInstance()->$key;
    }

}