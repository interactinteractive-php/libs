<?php 
/**
 * Config Class
 *
 * @package     IA PHPframework
 * @subpackage	Libraries
 * @category	Config
 * @author	B.Och-Erdene
 * @link	http://www.interactive.mn/PHPframework/Config
 */

class Config extends Controller {
    
    private static $cacheTime = 144000000;
    public static $configArr = array();
    public static $allConfigCodeArr = array();

    public function __construct() {
         parent::__construct();
    }
    
    public static function get($code = null, $criteria = null, $likecriteria = null) 
    {    
        global $db;
        
        $where = null;
                
        if ($code) {
            $code = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');
            $where .= " LOWER(CFG.CODE) = '".Str::lower($code)."'";
        }   
        
        if ($criteria) {
            $criteria = htmlspecialchars($criteria, ENT_QUOTES, 'UTF-8');
        }
        
        if ($likecriteria) {
            $where .= " AND (LOWER(VAL.CRITERIA) LIKE '%".Str::lower($criteria)."%' OR VAL.CRITERIA IS NULL)"; 
        } elseif ($criteria) {
            $where .= " AND (LOWER(VAL.CRITERIA) = '".Str::lower($criteria)."' OR VAL.CRITERIA IS NULL)"; 
        }
        
        if ($where) {
            
            $sessionValues = Session::get(SESSION_PREFIX.'sessionValues');
            
            if (Lang::isUseMultiLang()) {
                
                $langCode = Lang::getCode();
                $selectColumn = "FNC_TRANSLATE('$langCode', VAL.TRANSLATION_VALUE, 'CONFIG_VALUE', VAL.CONFIG_VALUE) AS CONFIG_VALUE";
                
            } else {
                $selectColumn = 'VAL.CONFIG_VALUE';
            }
            
            if ($sessionCompanyDepartmentId = issetParam($sessionValues['sessioncompanydepartmentid'])) {
                $where .= " AND (VAL.COMPANY_DEPARTMENT_ID = $sessionCompanyDepartmentId OR VAL.COMPANY_DEPARTMENT_ID IS NULL)"; 
            }
            
            $field = $db->GetRow("
                SELECT 
                    $selectColumn  
                FROM CONFIG CFG 
                    INNER JOIN CONFIG_VALUE VAL ON VAL.CONFIG_ID = CFG.ID 
                WHERE $where 
                ORDER BY VAL.CRITERIA ASC");

            return $field ? $field['CONFIG_VALUE'] : '';
        }
        
        return null;
    }
    
    public static function getFromCache($code = null, $criteria = null) {     
        
        $lowerCode = strtolower($code);
        $confData = Config::setCache();     

        if (isset($confData[$lowerCode])) {
            
            $confRow = $confData[$lowerCode];
            
            if (!isset($confRow['criteria'])) {
                return $confRow['value'];
            }
            
        } elseif ($lowerCode == 'javaversion') {
            
            $cache = phpFastCache();
            $conf = $cache->get('sysConfig');
            
            $getJavaVersion = WebService::runSerializeResponse(GF_SERVICE_ADDRESS, 'getVersion');
                
            if (isset($getJavaVersion['version']) && $getJavaVersion['version']) {
                $conf['javaversion']['value'] = (float) $getJavaVersion['version'];
            } else {
                $conf['javaversion']['value'] = 0;
            }
            
            $cache->set('sysConfig', $conf, self::$cacheTime);
            
            return $conf['javaversion']['value'];
            
        } elseif ($lowerCode == 'isldapmodifypassword') {
            
            $cache = phpFastCache();
            $conf = $cache->get('sysConfig');
            
            $isLdapModifyPassword = self::isCreatedMetaDataById('16651115931889'); //MODIFY_PASSWORD_LDAP_USER
                
            if ($isLdapModifyPassword) {
                $conf['isldapmodifypassword']['value'] = 1;
            } else {
                $conf['isldapmodifypassword']['value'] = 0;
            }
            
            $cache->set('sysConfig', $conf, self::$cacheTime);
            
            return $conf['isldapmodifypassword']['value'];
        }
        
        return null;
    }
    
    public static function getFromCacheDefault($code = null, $criteria = null, $default = null) {
        
        $lowerCode = strtolower($code);
        $confData  = Config::setCache();

        if (array_key_exists($lowerCode, $confData)) {
            
            $confRow = $confData[$lowerCode];
            
            if (!isset($confRow['criteria'])) {
                return $confRow['value'];
            }
        }
        
        return $default;
    }
    
    public static function isCode($code = null) {
        
        $lowerCode = strtolower($code);
        $confData  = Config::setCodeCache();

        if (array_key_exists($lowerCode, $confData)) {
            return true;
        }
        
        return false;
    }
    
    public static function setCache() {
        
        if (self::$configArr) {
            return self::$configArr;
        }
        
        $cache = phpFastCache();
        $conf = $cache->get('sysConfig');
        
        if ($conf == null) {
            
            global $db;
            
            $data = $db->GetAll("
                SELECT 
                    LOWER(CF.CODE) AS CODE, 
                    CV.CONFIG_VALUE, 
                    LOWER(CV.CRITERIA) AS CRITERIA 
                FROM CONFIG CF 
                    INNER JOIN CONFIG_VALUE CV ON CV.CONFIG_ID = CF.ID 
                WHERE CF.CODE IS NOT NULL 
                GROUP BY 
                    CF.CODE, CV.CONFIG_VALUE, CV.CRITERIA 
                ORDER BY 
                    CASE WHEN CV.CRITERIA IS NULL THEN '1' ELSE CV.CRITERIA END ASC, 
                    CF.CODE ASC");
            
            $conf = array();
            
            if ($data) {
                
                foreach ($data as $row) {
                    $codeLower = trim($row['CODE']);
                    
                    if (isset($conf[$codeLower])) {
                        if ($row['CRITERIA']) {
                            $conf[$codeLower]['criteria'][$row['CRITERIA']] = $row['CONFIG_VALUE'];
                        }
                        
                    } else {
                        if ($codeLower == 'login_failed_track_type') {
                            $row['CONFIG_VALUE'] = strtolower($row['CONFIG_VALUE']);
                        }
                        $conf[$codeLower]['value'] = $row['CONFIG_VALUE'];
                    }
                }
            }
            
            $cache->set('sysConfig', $conf, self::$cacheTime);
        }
        
        self::$configArr = $conf;
        
        return self::$configArr;
    }
    
    public static function setCodeCache() {
        
        if (self::$allConfigCodeArr) {
            return self::$allConfigCodeArr;
        }
        
        $cache = phpFastCache();
        $conf = $cache->get('sysConfigAllCode');
        
        if ($conf == null) {
            
            global $db;
            
            $data = $db->GetAll("SELECT LOWER(CODE) AS CODE FROM CONFIG WHERE CODE IS NOT NULL GROUP BY CODE");
            $conf = array();
            
            if ($data) {
                foreach ($data as $row) {
                    $conf[$row['CODE']] = 1;
                }
            }
            
            $cache->set('sysConfigAllCode', $conf, self::$cacheTime);
        }
        
        self::$allConfigCodeArr = $conf;
        
        return self::$allConfigCodeArr;
    }
    
    public static function isCreatedMetaDataById($id) {
        
        try {
            global $db;
            
            $row = $db->GetRow("SELECT META_DATA_ID FROM META_DATA WHERE META_DATA_ID = ".$db->Param(0), array($id));
            
            if ($row) {
                return true;
            }
            
            return false;
            
        } catch (Exception $ex) {
            
            return false;
        }
    }

}
