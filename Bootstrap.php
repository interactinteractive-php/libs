<?php if (!defined('_VALID_PHP')) exit('Direct access to this location is not allowed.');

/**
 * Bootstrap Class
 *
 * @package     IA PHPframework
 * @subpackage	Libraries
 * @category	Bootstrap
 * @author	B.Och-Erdene
 * @link	http://www.interactive.mn/PHPframework/Bootstrap
 */
class Bootstrap {

    private $_url = null;
    private $_controller = null;
    private $_controllerPath = 'controllers/'; // Always include trailing slash
    private $_modelPath = 'models/'; // Always include trailing slash
    
    private $_md_controllerPath = 'middleware/controllers/'; // Always include trailing slash
    private $_md_modelPath = 'middleware/models/'; // Always include trailing slash
    
    private $_projects_controllerPath = 'projects/controllers/'; // Always include trailing slash
    private $_projects_modelPath = 'projects/models/'; // Always include trailing slash
    
    private $_errorFile = 'err.php';
    private $_defaultFile = 'index.php';
    
    private static $middlewareUrls = array(
        'mdum', 'mdlayout', 
        'amactivity', 'mdrole',
        'mdlayoutrender', 'mdworkspace', 'mdtaskflow',
        'mdworkflow', 'mdmetadata',
        'mdhelpdesk', 'mdfolder',
        'mdmetadata', 'mdwebservice',
        'mdnotification', 'mdwarehouse',
        'mduser', 'mdcomment',
        'mdgl', 'rmreport', 'mdupgrade', 
        'mdasset', 'mdform', 
        'mdwidget', 'mdcache', 'mdbpmn',  
        'mdcommon', 'mdprocessflow',
        'mdconfig', 'mdmeta', 
        'mdobject', 'mdeditor', 
        'mdcontentui', 'mdreport', 
        'mdexpression', 'mdtemplate', 
        'mddatamodel', 'mddashboard', 
        'mddoceditor', 'mdstatement', 
        'mdpreview', 'mdlanguage', 'mdalert', 
        'mdsalary', 'mdintegration', 
        'mdpermission', 'mdmenu', 'mdprocess', 
        'mddoc', 'mdpki', 'mdlifecycle', 
        'mdpivot', 'mdlicense', 'mdlog', 
        'mdcalendar', 'mdlock', 'mdpos', 
        'dashboard', 'mdtimestable', 'mdintranet', 
        'mdproc', 'restapi'
    );
    
    private static $projectsUrls = array(
        'law',
        'man',
        'legal',
        'health',
        'cjob',
        'contentui',
        'subject',
        'student',
        'school',
        'corp'
    );
    
    /**
     * Starts the Bootstrap
     * 
     * @return boolean
     */
    public function init() {
        
        if (isset($GLOBALS['MAIN_BASEPATH'])) {
            $this->_md_controllerPath = $GLOBALS['MAIN_BASEPATH'] . 'middleware/controllers/'; 
            $this->_md_modelPath = $GLOBALS['MAIN_BASEPATH'] . 'middleware/models/'; 
        }
        
        // Sets the protected $_url
        $this->_getUrl();

        // Load the default controller if no URL is set
        // eg: Visit http://localhost it loads Default Controller
        if (empty($this->_url[0])) {
            $this->_loadDefaultController();
            return false;
        }

        $this->_loadExistingController();
        $this->_callControllerMethod();
    }

    /**
     * (Optional) Set a custom path to controllers
     * @param string $path
     */
    public function setControllerPath($path) {
        $this->_controllerPath = trim($path, '/') . '/';
    }

    /**
     * (Optional) Set a custom path to models
     * @param string $path
     */
    public function setModelPath($path) {
        $this->_modelPath = trim($path, '/') . '/';
    }

    /**
     * (Optional) Set a custom path to the error file
     * @param string $path Use the file name of your controller, eg: error.php
     */
    public function setErrorFile($path) {
        $this->_errorFile = trim($path, '/');
    }

    /**
     * (Optional) Set a custom path to the error file
     * @param string $path Use the file name of your controller, eg: index.php
     */
    public function setDefaultFile($path) {
        $this->_defaultFile = trim($path, '/');
    }

    /**
     * Fetches the $_GET from 'url'
     */
    private function _getUrl() {
        $url = isset($_GET['url']) ? $_GET['url'] : null;
        $url = rtrim($url, '/');
        $url = filter_var($url, FILTER_UNSAFE_RAW);
        $this->_url = explode('/', $url);
    }

    /**
     * This loads if there is no GET parameter passed
     */
    private function _loadDefaultController() {
        require_once $this->_controllerPath . $this->_defaultFile;
        $this->_controller = new Index();
        $this->_controller->index();
    }

    /**
     * Load an existing controller if there IS a GET parameter passed
     * 
     * @return boolean|string
     */
    private function _loadExistingController() {
        
        $this->_controllerPath = $this->_controllerPath;
        $this->_modelPath = $this->_modelPath;
        
        if (in_array(strtolower($this->_url[0]), self::$middlewareUrls)) {
            
            if (defined('MAIN_APP_PATH') && MAIN_APP_PATH) {
                
                $this->_md_controllerPath = MAIN_APP_PATH . $this->_md_controllerPath;
                $this->_md_modelPath = MAIN_APP_PATH . $this->_md_modelPath;
                
                $GLOBALS['MAIN_BASEPATH'] = MAIN_APP_PATH;
            }
            
            $this->_controllerPath = $this->_md_controllerPath;
            $this->_modelPath = $this->_md_modelPath;
        }
        
        if (in_array(strtolower($this->_url[0]), self::$projectsUrls)) {
            $this->_controllerPath = $this->_projects_controllerPath;
            $this->_modelPath = $this->_projects_modelPath;
        }

        $file = $this->_controllerPath . $this->_url[0] . '.php';

        if (file_exists($file)) {
            require_once ($file);
            $this->_controller = new $this->_url[0];
            $this->_controller->loadModel($this->_url[0], $this->_modelPath);
        } else {
            $firstLetterLower = strtolower(substr($this->_url[0], 0, 1));
            $lastLetters = substr($this->_url[0], 1, 25);
            $path = $this->_controllerPath . $firstLetterLower . $lastLetters . '.php';

            if (file_exists($path)) {
                require_once ($path);
                $this->_controller = new $this->_url[0];
                $this->_controller->loadModel($this->_url[0], $this->_modelPath);
            } else {
                $firstLetterUpper = strtoupper(substr($this->_url[0], 0, 1));
                $lastLowerLetters = substr($this->_url[0], 1, 25);
                $pathUpper = $this->_controllerPath . $firstLetterUpper . $lastLowerLetters . '.php';

                if (file_exists($pathUpper)) {
                    require_once ($pathUpper);
                    $this->_controller = new $this->_url[0];
                    $this->_controller->loadModel($this->_url[0], $this->_modelPath);
                } else {
                    $this->errorFile();
                    return false;
                }
            }
        }
    }

    /**
     * If a method is passed in the GET url paremter
     * 
     *  http://localhost/controller/method/(param)/(param)/(param)
     *  url[0] = Controller
     *  url[1] = Method
     *  url[2] = Param
     *  url[3] = Param
     *  url[4] = Param
     */
    private function _callControllerMethod() {
        
        $length = count($this->_url);

        // Make sure the method we are calling exists
        if ($length > 1 && !method_exists($this->_controller, $this->_url[1])) {
            $this->errorFile();
        } elseif ($length == 1 && !method_exists($this->_controller, 'index')) {
            $this->errorFile();
        }
        
        // Determine what to load
        switch ($length) {
            case 7:
                //Controller->Method(Param1, Param2, Param3, Param4, Param5)
                $this->_controller->{$this->_url[1]}($this->_url[2], $this->_url[3], $this->_url[4], $this->_url[5], $this->_url[6]);
                break;
            
            case 6:
                //Controller->Method(Param1, Param2, Param3, Param4)
                $this->_controller->{$this->_url[1]}($this->_url[2], $this->_url[3], $this->_url[4], $this->_url[5]);
                break;

            case 5:
                //Controller->Method(Param1, Param2, Param3)
                $this->_controller->{$this->_url[1]}($this->_url[2], $this->_url[3], $this->_url[4]);
                break;

            case 4:
                //Controller->Method(Param1, Param2)
                $this->_controller->{$this->_url[1]}($this->_url[2], $this->_url[3]);
                break;

            case 3:
                //Controller->Method(Param1, Param2)
                $this->_controller->{$this->_url[1]}($this->_url[2]);
                break;

            case 2:
                //Controller->Method(Param1, Param2)
                $this->_controller->{$this->_url[1]}();
                break;

            default:
                $this->_controller->index();
                break;
        }
    }

    /**
     * Display an error page if nothing exists
     * 
     * @return boolean
     */
    
    private function errorFile() {
        
        $server_protocol = (isset($_SERVER['SERVER_PROTOCOL'])) ? $_SERVER['SERVER_PROTOCOL'] : null;

        if (substr(php_sapi_name(), 0, 3) == 'cgi') {
            header("Status: 404 Not Found", true);
        } elseif ($server_protocol == 'HTTP/1.1' || $server_protocol == 'HTTP/1.0') {
            header($server_protocol . " 404 Not Found", true, 404);
        } else {
            header("HTTP/1.1 404 Not Found", true, 404);
        }
       
        require_once ($this->_controllerPath . $this->_errorFile);
        
        $this->_controller = new Err();
        $this->_controller->index(1);
        exit;
    }

}