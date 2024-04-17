<?php 

/**
 * Language Class
 *
 * @package     IA PHPframework
 * @subpackage	Libraries
 * @category	Language
 * @author	B.Och-Erdene
 * @link	http://www.interactive.mn/PHPframework/Language
 */

class Lang extends Controller {

    private static $isLoad = array();
    private static $language = array();
    private static $isMultiLangLoad = false;
    private static $isMultiLang = false;
    private static $defaultLangCode = null;
    private static $langCode = null;
    public static $memoryLangCode = null;
    private static $langList = array();
    private static $langListLoad = false;

    public function __construct() {
        parent::__construct();
    }

    /**
     * Load a language file
     *
     * @access	public
     * @param	mixed	the name of the language file to be loaded. Can be an array
     * @param	string	the language (english, etc.)
     * @param	bool	return loaded array of translations
     * @param 	bool	add suffix to $langfile
     * @param 	string	alternative path to look for language file
     * @return	mixed
     */
    public static function load($langFile = '', $return = false, $languageCode = null)
    {   
        $langCode = is_null($languageCode) ? Lang::getCode() : $languageCode;
        
        if (isset(self::$isLoad[$langCode])) {
            return true;
        }
        
        $langSuffix = Lang::getSuffix();
        
        $langPhpFile = $langFile.'_lang.php';
        $langIniFile = $langFile.'_lang'.$langSuffix.'.ini';
        
        if (defined('MAIN_APP_PATH') && MAIN_APP_PATH) {
            $basePath = MAIN_APP_PATH;
        } else {
            $basePath = BASEPATH;
        }
        
        if (file_exists($basePath.'lang/'.$langCode.'/'.$langIniFile)) {
            
            $iniArr = parse_ini_file($basePath.'lang/'.$langCode.'/'.$langIniFile, true, INI_SCANNER_RAW);
            
            if (isset($iniArr['lang'])) {
                $lang = $iniArr['lang'];
            }
            
        } elseif (file_exists($basePath.'lang/'.$langCode.'/'.$langPhpFile)) {
            
            require_once $basePath.'lang/'.$langCode.'/'.$langPhpFile;
            
        } else {
            return true;
        }

        if (!isset($lang)) {
            return;
        }

        if ($return == true) {
            return $lang;
        }
        
        self::$language = $lang + self::$language;
        self::$isLoad[$langCode] = true;
        
        return true;
    }

    /**
     * Fetch a single line of text from the language array
     *
     * @access	public
     * @param	string $line the language line
     * @return	string
     */
    public static function line($code = '') 
    {
        $codeLower = Str::lower($code);
        return !isset(self::$language[$codeLower]) ? $code : self::$language[$codeLower];
    }

    public static function lineEmpty($code = '')
    {
        $codeLower = Str::lower($code);
        return !isset(self::$language[$codeLower]) ? '' : self::$language[$codeLower];
    }    
    
    public static function isExisting($code = '') 
    {   
        if (empty($code)) {
            return false;
        }
        $code = Str::lower($code);
        return !isset(self::$language[$code]) ? false : true;
    }
    
    public static function lineCode($code = '', $languageCode = null) {
        $codeLower = Str::lower($code);
        
        if (!is_null($languageCode)) {
            self::load('main', false, $languageCode);
        }

        $lang = self::$language;
        $globeText = !isset($lang[$codeLower]) ? $code : $lang[$codeLower];
        
        unset(self::$isLoad['mn']);
        unset(self::$isLoad['en']);
        
        return $globeText;
    }
    
    public static function lineVar($code = '', $variables = array(), $default = null) 
    {
        $codeLower = Str::lower($code);
        
        if (!isset(self::$language[$codeLower])) {
            return $default ? $default : $code;
        } else {
            $globeText = self::$language[$codeLower];
            
            if ($variables) {
                foreach ($variables as $key => $val) {
                    $globeText = str_ireplace('['.$key.']', $val, $globeText);
                }
            }
            
            return $globeText;
        }
    }
    
    public static function lineDefault($code, $default = null)
    {
        if (self::isExisting($code)) {
            return self::line($code);
        }
        
        return $default;
    }
    
    public static function eitherOne($first, $second, $default = null)
    {
        if (self::isExisting($first)) {
            return self::line($first);
        }
        
        if (self::isExisting($second)) {
            return self::line($second);
        }
        
        return $default;
    }

    public static function getCode() 
    {
        if (self::$isMultiLangLoad == false) {
            
            self::$isMultiLangLoad == true;
            self::$isMultiLang = Config::getFromCache('MULTI_LANG') ? true : false;
            self::$defaultLangCode = Config::getFromCache('LANG');
            
            if (Session::isCheck(SESSION_PREFIX.'langshortcode')) {
                self::$langCode = Session::get(SESSION_PREFIX.'langshortcode');
            } else {
                self::$langCode = Config::getFromCache('LANG');
            }
        }
        
        if (self::$memoryLangCode) {
            return self::$memoryLangCode;
        }
        
        return self::$langCode;
    }
    
    public static function getSuffix() 
    {
        $sdbun = Session::unitName();
        
        if ($sdbun) {
            return '_' . $sdbun;
        }
        
        return null;
    }
    
    public static function getLanguageName() 
    {
        if (Session::isCheck(SESSION_PREFIX.'langcode')) {
            return Session::get(SESSION_PREFIX.'langcode');
        }
        if ($langName = Config::getFromCache('LANG_NAME')) {
            return $langName;
        }
        
        return 'mongolian';
    }
    
    public static function isUseMultiLang() {
        
        if (self::$isMultiLangLoad == false) {
            
            self::$isMultiLangLoad == true;
            self::$isMultiLang = Config::getFromCache('MULTI_LANG') ? true : false;
            self::$defaultLangCode = Config::getFromCache('LANG');
            
            if (Session::isCheck(SESSION_PREFIX.'langshortcode')) {
                self::$langCode = Session::get(SESSION_PREFIX.'langshortcode');
            } else {
                self::$langCode = Config::getFromCache('LANG');
            }
            
            if (self::$isMultiLang && (int) Session::get(SESSION_PREFIX . 'langCount') == 1) {
                self::$isMultiLang = false;
            }
        }
        
        return self::$isMultiLang;
    }
    
    public static function getDefaultLangCode() {
        
        if (self::$isMultiLangLoad == false) {
            
            self::$isMultiLangLoad == true;
            self::$isMultiLang = Config::getFromCache('MULTI_LANG') ? true : false;
            self::$defaultLangCode = Config::getFromCache('LANG');
            
            if (Session::isCheck(SESSION_PREFIX.'langshortcode')) {
                self::$langCode = Session::get(SESSION_PREFIX.'langshortcode');
            } else {
                self::$langCode = Config::getFromCache('LANG');
            }
        }
        
        return self::$defaultLangCode;
    }
    
    public static function loadjs($langfile = 'main', $languageCode = null) {
        
        $langSuffix = Lang::getSuffix();
        $langfile = str_replace('.js', '', $langfile).'_lang'.$langSuffix.'.js';

        $langCode = is_null($languageCode) ? Lang::getCode() : $languageCode;

        if (file_exists(BASEPATH.'lang/'.$langCode.'/'.$langfile)) {
            return 'lang/'.$langCode.'/'.$langfile;
        } else {
            return;
        }
    }
    
    public static function getMinActiveLanguage()
    {   
        $html = '';
        
        if (self::isUseMultiLang()) {
                
            global $db;

            $data = $db->GetAll(
                "SELECT 
                    LANGUAGE_CODE, 
                    LANGUAGE_NAME, 
                    LOWER(SHORT_CODE) AS SHORT_CODE 
                FROM REF_LANGUAGE 
                WHERE IS_ACTIVE = 1  
                ORDER BY DISPLAY_ORDER ASC");

            if ($data) {
                $html .= '<li class="dropdown dropdown-language">';

                $currentLang = $listLang = '';

                foreach ($data as $row) {
                    if ($row['SHORT_CODE'] == Lang::getCode()) {
                        $currentLang = '<img src="assets/core/global/img/flags/'.$row['SHORT_CODE'].'.png">
                                        <span class="langname"></span>';
                    } else {
                        $listLang .= '<li>
                                        <a href="profile/changelang/'.$row['SHORT_CODE'].'">
                                        <img src="assets/core/global/img/flags/'.$row['SHORT_CODE'].'.png"> '.$row['LANGUAGE_NAME'].'</a>
                                      </li>';
                    }
                }

                $html .= '<a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                            '.$currentLang.'
                            
                          </a>
                          <ul class="dropdown-menu">
                            '.$listLang.'
                          </ul>
                          </li>';
            }
        }
        
        return $html;
    }
    
    public static function getLanguageList() {

        if (!self::$langListLoad) {
            
            $langCount = Session::get(SESSION_PREFIX . 'langCount');
            $cacheName = 'getActiveLanguageList';
            $selectQry = "SELECT 
                        LANGUAGE_CODE, 
                        LANGUAGE_NAME, 
                        LOWER(SHORT_CODE) AS SHORT_CODE
                    FROM REF_LANGUAGE 
                    WHERE IS_ACTIVE = 1 
                    ORDER BY DISPLAY_ORDER ASC";
            
            if ($langCount) {
                if ($langCount == '1') {
                    return self::$langList;
                } else {
                    $departmentId = Session::get(SESSION_PREFIX . 'userKeyDepartmentId');
                    $cacheName = 'getActiveLanguageList_' . $departmentId;
                    $selectQry = "SELECT 
                        T0.LANGUAGE_CODE, 
                        T0.LANGUAGE_NAME, 
                        LOWER(T0.SHORT_CODE) AS SHORT_CODE
                    FROM REF_LANGUAGE T0 
                        INNER JOIN ORG_DEPARTMENT_LANGUAGE T1 ON T1.LANGUAGE_ID = T0.LANGUAGE_ID 
                    WHERE T0.IS_ACTIVE = 1 
                        AND T1.DEPARTMENT_ID = $departmentId 
                    ORDER BY T0.DISPLAY_ORDER ASC";
                }
            }
            
            $cache = phpFastCache(); 
            $data = $cache->get($cacheName);

            if ($data == null) {

                global $db;

                $data = $db->GetAll($selectQry);
                
                $arr = array();
                $objectName = 'GLOBE_DICTIONARY';

                if (DB_DRIVER == 'oci8') {
                
                    $rs = $db->Execute("SELECT * FROM $objectName WHERE 1 = 0");
                    $checkFields = Arr::objectToArray($rs->_fieldobjs);

                } elseif (DB_DRIVER == 'postgres9') {

                    $rs = $db->MetaColumns('public.' . $objectName);
                    $checkFields = [];
                    
                    if (is_array($rs)) {
                        
                        foreach ($rs as $row) {

                            $typeName = 'varchar';

                            if ($row->type == 'numeric') {
                                $typeName = 'NUMBER';
                            } elseif ($row->type == 'text' || $row->type == 'clob') {
                                $typeName = 'CLOB';
                            } elseif ($row->type == 'timestamp') {
                                $typeName = 'DATE'; 
                            }

                            $checkFields[] = [
                                'name'       => strtoupper($row->name), 
                                'max_length' => 4000, 
                                'type'       => $typeName, 
                                'scale'      => 1
                            ];
                        }
        
                    } else {
                        $result = $db->GetAll($rs->sql);

                        if ($result) {

                            foreach ($result as $row) {

                                $typeName = 'varchar';

                                if ($row['TYPNAME'] == 'numeric') {
                                    $typeName = 'NUMBER';
                                } elseif ($row['TYPNAME'] == 'text') {
                                    $typeName = 'CLOB';
                                } elseif ($row['TYPNAME'] == 'timestamp') {
                                    $typeName = 'DATE'; 
                                }

                                $checkFields[] = [
                                    'name'       => strtoupper($row['ATTNAME']), 
                                    'max_length' => 4000, 
                                    'type'       => $typeName, 
                                    'scale'      => 1
                                ];
                            }
                        }
                    }
                }
                
                foreach ($data as $row) {
                    foreach ($checkFields as $c => $checkField) {
                        if ($checkField['name'] == strtoupper($row['LANGUAGE_CODE'])) {
                            $arr[] = $row;
                            unset($checkFields[$c]);
                            break;
                        }
                    }
                }
            
                $cache->set($cacheName, $arr, 86400);
            }
            
            self::$langList = $data;
            self::$langListLoad = true;
        }
        
        return self::$langList;
    }
    
    public static function getActiveLanguage()
    {   
        $html = '';
        
        if (self::isUseMultiLang()) {
                
            $data = self::getLanguageList();

            if ($data) {
                $html .= '<li class="dropdown dropdown-lang dropdown-dark nav-item">';

                $currentLang = $listLang = '';

                foreach ($data as $row) {
                    
                    if ($row['SHORT_CODE'] == Lang::getCode()) {
                        
                        $currentLang = '<img src="assets/core/global/img/flags/'.$row['SHORT_CODE'].'.png">';
                        $currentLang .= '<span class="language-name">'.$row['SHORT_CODE'].'</span>';
                        
                    } else {
                        $listLang .= '<li>
                            <a href="profile/changelang/'.$row['SHORT_CODE'].'" class="dropdown-item">
                                <img src="assets/core/global/img/flags/'.$row['SHORT_CODE'].'.png"> '.$row['LANGUAGE_NAME'].'
                            </a>
                        </li>';
                    }
                }

                $html .= '<a href="javascript:;" class="dropdown-toggle navbar-nav-link header-lang" data-toggle="dropdown" data-close-others="true">
                        '.$currentLang.'
                    </a>
                    <ul class="dropdown-menu dropdown-menu-default dropdown-menu-right">
                        '.$listLang.'
                    </ul>
                </li>';
            }
        }
        
        return $html;
    }
    
    public static function changeLanguage($langCode, $previousUrl)
    {
        global $db;
        
        $row = $db->GetRow(
            "SELECT 
                LANGUAGE_ID, 
                LANGUAGE_CODE, 
                LANGUAGE_NAME, 
                LOWER(SHORT_CODE) AS SHORT_CODE
            FROM REF_LANGUAGE
            WHERE IS_ACTIVE = 1 
                AND LOWER(SHORT_CODE) = ".$db->Param(0), 
            array(Str::lower(Input::param($langCode))) 
        );
        
        if ($row) {
            
            Session::init();
            
            Session::set(SESSION_PREFIX.'langid', $row['LANGUAGE_ID']);
            Session::set(SESSION_PREFIX.'langcode', $row['LANGUAGE_CODE']);
            Session::set(SESSION_PREFIX.'langshortcode', $row['SHORT_CODE']);
            
            if ($sessionUserId = Ue::sessionUserId()) {
                
                $db->AutoExecute('UM_SYSTEM_USER', array('LANGUAGE_ID' => $row['LANGUAGE_ID']), 'UPDATE', 'USER_ID = '.$sessionUserId);
                
                if ($appUserSessionId = Ue::appUserSessionId()) {
                    $db->AutoExecute('UM_USER_SESSION', array('LANGUAGE_CODE' => $row['SHORT_CODE']), 'UPDATE', "SESSION_ID = '$appUserSessionId'");
                }
            }
        }
        
        Message::add('s', '', $previousUrl);
    }
    
    public static function changeLanguageWithoutrefresh($langCode) {
        global $db;

        $row = $db->GetRow("
            SELECT 
                LANGUAGE_ID, 
                LANGUAGE_CODE, 
                LANGUAGE_NAME, 
                LOWER(SHORT_CODE) AS SHORT_CODE
            FROM REF_LANGUAGE
            WHERE IS_ACTIVE = 1 
                AND LOWER(SHORT_CODE) = ".$db->Param(0), 
            array(Str::lower(Input::param($langCode)))
        );

        if ($row) {
            Session::init();
            Session::set(SESSION_PREFIX . 'langid', $row['LANGUAGE_ID']);
            Session::set(SESSION_PREFIX . 'langcode', $row['LANGUAGE_CODE']);
            Session::set(SESSION_PREFIX . 'langshortcode', $row['SHORT_CODE']);

            if ($sessionUserId = Ue::sessionUserId()) {
                $updateData = array(
                    'LANGUAGE_ID' => $row['LANGUAGE_ID']
                );
                $db->AutoExecute('UM_SYSTEM_USER', $updateData, 'UPDATE', "USER_ID = " . $sessionUserId);
            }
        }
    }

}
