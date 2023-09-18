<?php if(!defined('_VALID_PHP')) exit('Direct access to this location is not allowed.');

/**
 * Controller Class
 *
 * @package     IA PHPframework
 * @subpackage	Libraries
 * @category	Controller
 * @author	B.Och-Erdene
 * @link	http://www.interactive.mn/PHPframework/Controller
 */

class Controller {
    
    private static $instance;
    
    //public $model;
    
    public function __construct() 
    {
        global $db, $lang;
        
        self::$instance =& $this;
        
        $this->lang = $lang;
        $this->db = $db;
        
        $this->ws = new WebService();
        
        if (!isset($this->view)) {
            $this->view = new View();
        }
        
        $this->load = new Loader();
    }
    
    /**
     * 
     * @param string $name Name of the model
     * @param string $path Location of the models
     */
    public function loadModel($name, $modelPath = 'models/') {
        
        $path = $modelPath.$name.'_model.php';
        $modelName = $name.'_Model';
        
        if (file_exists($path)) {
            
            require_once($path);
            
            $this->model = new $modelName();
            
        } else {
            
            $nameSubStr = substr($name, 0, 1);
            $firstLetterLower = strtolower($nameSubStr);
            $firstLetterUpper = strtoupper($nameSubStr);
            $lastLetters = substr($name, 1, 25);
            $path = $modelPath.$firstLetterLower.$lastLetters.'_model.php';
            
            require_once(file_exists($path) === true ? $path : $modelPath.$firstLetterUpper.$lastLetters.'_model.php');
            
            $this->model = new $modelName();
        }       
        
        return $this;
    }
    
    /**
     * 
     * @param string $name Name of the controller
     * @param string $path Location of the controllers
     */
    public function loadController($controllerName, $controllerPath = 'controllers/') {
        
        if (isset($GLOBALS['MAIN_BASEPATH'])) {
            $controllerPath = $GLOBALS['MAIN_BASEPATH'] . $controllerPath;
        }
        
        $path = $controllerPath.$controllerName.'.php';
        
        if (file_exists($path)) {
            
            require_once($path);
            
            return new $controllerName();
            
        } else {
            
            $firstLetterLower = strtolower(substr($controllerName, 0, 1));
            $lastLetters = substr($controllerName, 1, 25);
            
            require_once($controllerPath.$firstLetterLower.$lastLetters.'.php');
            
            return new $controllerName();
        }        
    }
    
    public static function &getInstance() {
        return self::$instance;
    }

}