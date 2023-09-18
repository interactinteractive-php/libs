<?php if(!defined('_VALID_PHP')) exit('Direct access to this location is not allowed.');

class Loader {
    
    public function __construct() {}
    
    public function model($name, $modelPath = 'models/', $debug = '') {
        
        if (isset($GLOBALS['MAIN_BASEPATH'])) {
            $modelPath = $GLOBALS['MAIN_BASEPATH'] . $modelPath; 
        }
        
        $path = $modelPath.$name.'_model.php';
        $modelName = ucfirst($name).'_Model';
        
        $pt = &getInstance();
        
        if (file_exists($path)) {
            
            require_once($path);
            
        } else {
            
            $firstLetterLower = strtolower(substr($name, 0, 1));
            $firstLetterUpper = strtoupper(substr($name, 0, 1));
            $lastLetters = substr($name, 1, 25);
            $path = $modelPath.$firstLetterLower.$lastLetters.'_model.php';
            
            require_once(file_exists($path) === true ? $path : $modelPath.$firstLetterUpper.$lastLetters.'_model.php');
        }       
        
        $model = new $modelName();
        
        $pt->model = $model;
    }

}
