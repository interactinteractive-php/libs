<?php if (!defined('_VALID_PHP')) exit('Direct access to this location is not allowed.');

/**
 * WebService Class
 *
 * @package     IA PHPframework
 * @subpackage	Libraries
 * @category	WebService
 * @author	B.Och-Erdene
 * @link	http://www.interactive.mn/PHPframework/WebService
 */

class WebService {

    public static $getErrorMessage = null;
    public static $soapErrorMessage = 'Сервис асаагүй байна түр хүлээнэ үү.';
    public static $soapErrorNonNormal = 'Сервис хэвийн бус ажиллаж байна. Та дахин оролдоно уу.';
    public static $shortWsdl = 'erp-services/SoapWS';
    private static $defaultSessionId = '65178215-7896-4513-8e26-896df9cb36ad';
    public static $isDefaultSessionId = false;
    public static $fullUrl = false;
    public static $isCustomer = false;
    public static $isProcess = false;
    public static $userKeyId = null;
    public static $addonHeaderParam = array();
    private static $osName = null;
    private static $platformName = null;
    private static $ipAddress = null;
    private static $userAgent = null;

    public function getErrorMessage() {
        return self::$getErrorMessage;
    }

    public function isException() {
        if (empty(self::$getErrorMessage)) {
            return false;
        }
        return true;
    }

    public static function setErrorMessage($e) {
        self::$getErrorMessage = $e;
    }
    
    public static function setUserKeyId($userKeyId) {
        self::$userKeyId = $userKeyId;
    }
    
    public static function setClientUserInfo() {
        
        if (!self::$osName) {
            
            includeLib('Detect/Browser');
            $browser = new Browser();
            
            self::$ipAddress = get_client_ip();
            self::$osName = $browser->getPlatform();
            self::$platformName = $browser->getBrowser();
            self::$userAgent = $browser->getUserAgent();
        }
    }

    public static function soapCallAddr($serviceAddr, $shortWsdl, $operation, $param = null) {   

        try {    
            libxml_disable_entity_loader(false); 
            $client = new SoapClientNG($serviceAddr . $shortWsdl . '?wsdl', array('exceptions' => true, 'trace' => true, 'encoding' => 'utf-8', 'keep_alive' => false));

            if (empty($param)) {
                $param = array();
            } else {
                $param = array($operation => $param);
            }
            $response = $client->__soapCall($operation, $param);
            
            return $response;
            
        } catch (\SoapFault $e) {
            
            if (isset($e)) {
                if (strtolower($e->faultcode) == 'wsdl') {
                    self::setErrorMessage(self::$soapErrorMessage);
                } else {
                    @file_put_contents('log/getLastResponse.txt', $client->__getLastResponse());
                    self::setErrorMessage($e->getMessage());
                }
            }
            return null;
        }
    }

    public function soapCallListAddr($serviceAddr, $shortWsdl, $operation, $rowname, $param = null) {
        $data = self::soapCallAddr($serviceAddr, $shortWsdl, $operation, $param);
        return self::wsObjectToArray($data, $rowname);
    }

    public static function caller($typeCode, $wsUrl, $methodName, $rowName = 'return', $params = null, $requestType = 'run', $isTest = false) {
        
        $typeCode = strtolower($typeCode);
        
        if ($typeCode == 'wsdl') {

            $data = self::wsdlCall($wsUrl, $methodName, $params);
            
            return self::wsObjectToArray($data, $rowName);
            
        } elseif ($typeCode == 'wsdl-de' || empty($typeCode)) {
            
            $wsUrl = defined('SERVICE_FULL_ADDRESS') ? SERVICE_FULL_ADDRESS : $wsUrl;
            
            if ($configServiceAddress = self::getConfigServiceAddress($methodName)) {
                $wsUrl = $configServiceAddress['serviceFullAddr'];
            }
                
            if ($requestType == 'run') {
                
                $dataElement = self::dataElementHeaderParam($methodName, $params, $isTest);
                $data = self::wsdlCall($wsUrl, 'run', $dataElement);

                if ($data) {

                    if (isset($data->pDataElement)) {
                        
                        $newArr = array();
                        Arr::convertDeToArray($data->pDataElement, $newArr);
                        return $newArr['response'];
                        
                    } else {
                        if (count(get_object_vars($data)) === 0) {
                            self::setErrorMessage(self::$soapErrorNonNormal);
                            return array('status' => 'error', 'text' => self::getErrorMessage());
                        } else {
                            return $data;
                        }
                    }
                } else {
                    if (self::isException()) {
                        return array('status' => 'error', 'text' => self::getErrorMessage());
                    } else {
                        self::setErrorMessage('Error');
                        return array('status' => 'error', 'text' => self::getErrorMessage());
                    }
                }
                
            } elseif ($requestType == 'json') {
                
                $dataElement = self::jsonHeaderParam($methodName, $params, $isTest);
                $data = self::wsdlCall($wsUrl, 'runJson', $dataElement);

                if ($data) {

                    if (isset($data->return)) {

                        $newArr = json_decode(str_replace("\n", "", $data->return), true);
                        return $newArr['response'];
                        
                    } else {
                        if (count(get_object_vars($data)) === 0) {
                            self::setErrorMessage(self::$soapErrorNonNormal);
                            return array('status' => 'error', 'text' => self::getErrorMessage());
                        } else {
                            return $data;
                        }
                    }
                } else {
                    if (self::isException()) {
                        return array('status' => 'error', 'text' => self::getErrorMessage());
                    } else {
                        self::setErrorMessage('Error');
                        return array('status' => 'error', 'text' => self::getErrorMessage());
                    }
                }
                
            } elseif ($requestType == 'serialize') {
                
                $dataElement = self::serializeHeaderParam($methodName, $params, $isTest);
                $data = self::wsdlCall($wsUrl, 'runSerialize', $dataElement);

                if ($data) {

                    if (isset($data->return)) {

                        $newArr = @unserialize($data->return);
                        
                        if (isset($newArr['response'])) {
                            return $newArr['response'];
                        } else {
                            return null;
                        }
                        
                    } else {
                        if (count(get_object_vars($data)) === 0) {
                            self::setErrorMessage(self::$soapErrorNonNormal);
                            return array('status' => 'error', 'text' => self::getErrorMessage());
                        } else {
                            return $data;
                        }
                    }
                } else {
                    if (self::isException()) {
                        return array('status' => 'error', 'text' => self::getErrorMessage());
                    } else {
                        self::setErrorMessage('Error');
                        return array('status' => 'error', 'text' => self::getErrorMessage());
                    }
                }
                
            } elseif ($requestType == 'array') {
                
                $dataElement = self::serializeHeaderParam($methodName, $params, $isTest);
                $data = self::wsdlCall($wsUrl, 'runArray', $dataElement);

                if ($data) {

                    if (isset($data->return)) {
                        
                        eval('$newArr = '.$data->return.';');
                        
                        return $newArr['response'];
                        
                    } else {
                        if (count(get_object_vars($data)) === 0) {
                            self::setErrorMessage(self::$soapErrorNonNormal);
                            return array('status' => 'error', 'text' => self::getErrorMessage());
                        } else {
                            return $data;
                        }
                    }
                } else {
                    if (self::isException()) {
                        return array('status' => 'error', 'text' => self::getErrorMessage());
                    } else {
                        self::setErrorMessage('Error');
                        return array('status' => 'error', 'text' => self::getErrorMessage());
                    }
                }
            }
        }
    }

    public static function wsdlCall($serviceAddr, $methodName, $param = null) {
        try {
            
            libxml_disable_entity_loader(false); 
            $client = new SoapClientNG($serviceAddr, array('exceptions' => true, 'trace' => true, 'encoding' => 'UTF-8', 'keep_alive' => false));
            
            if (empty($param)) {
                $param = array();
            } else {
                $param = array($methodName => $param);
            }
            
            $response = $client->__soapCall($methodName, $param);

            return $response;
            
        } catch (\SoapFault $e) {
            
            if (isset($e)) {
                if (strtolower($e->faultcode) == 'wsdl') {
                    self::setErrorMessage(self::$soapErrorMessage);
                } else {
                    self::setErrorMessage($e->getMessage());
                }
            }
            return null;
        }
    }
    
    public static function getShortWsdlAddress() {
        return str_replace(array(GF_SERVICE_ADDRESS, '?wsdl'), '', SERVICE_FULL_ADDRESS);
    }
    
    public static function getConfigServiceAddress($command) {
        
        $command = strtolower($command);
        $config = array();
        $url = null;
        
        if ($command == 'pl_mdview_004') {
            $url = Config::getFromCache('LIST_SERVICE_FULL_ADDRESS');
        } elseif (self::$isProcess) {
            $url = Config::getFromCache('PROCESS_SERVICE_FULL_ADDRESS');
        }
        
        if ($url) {
            
            $urlArr = parse_url($url);
            $port = isset($urlArr['port']) ? ':'.$urlArr['port'] : '';
            
            $config['serviceAddr'] = $urlArr['scheme'].'://'.$urlArr['host'].$port.'/';
            $config['shortWsdl'] = ltrim($urlArr['path'], '/');
            $config['serviceFullAddr'] = $config['serviceAddr'].$config['shortWsdl'].'?wsdl';
        }

        return $config;
    }

    public function runResponse($serviceAddr, $command, $param = array(), $unitName = '', $shortWsdl = null, $rowname = 'return', $isTest = false) {
        $dataElement = self::dataElementHeaderParam($command, $param, $isTest);
        
        if ($shortWsdl === null) {
            $shortWsdl = self::getShortWsdlAddress();
        }
        
        if ($configServiceAddress = self::getConfigServiceAddress($command)) {
            $serviceAddr = $configServiceAddress['serviceAddr'];
            $shortWsdl = $configServiceAddress['shortWsdl'];
        }
        
        $data = self::soapCallAddr($serviceAddr, $shortWsdl, 'run', $dataElement);
     
        if ($data) {
            if (isset($data->pDataElement)) {
                
                $newArr = array();
                Arr::convertDeToArray($data->pDataElement, $newArr);

                return $newArr['response'];
                
            } else {
                if (count(get_object_vars($data)) === 0) {
                    self::setErrorMessage(self::$soapErrorNonNormal);
                    return array('status' => 'error', 'text' => self::getErrorMessage());
                } else {
                    return $data;
                }
            }
        } else {
            if (self::isException()) {
                return array('status' => 'error', 'text' => self::getErrorMessage());
            } else {
                self::setErrorMessage('Error');
                return array('status' => 'error', 'text' => self::getErrorMessage());
            }
        }
    }
    
    public function runDataElementResponse($serviceAddr, $dataElement = array(), $unitName = '', $shortWsdl = null, $rowname = 'return', $isTest = false) {
        
        if ($shortWsdl === null) {
            $shortWsdl = self::getShortWsdlAddress();
        }
        
        $data = self::soapCallAddr($serviceAddr, $shortWsdl, 'run', $dataElement);
     
        if ($data) {
            if (isset($data->pDataElement)) {
                
                $newArr = array();
                Arr::convertDeToArray($data->pDataElement, $newArr);

                return $newArr['response'];
                
            } else {
                if (count(get_object_vars($data)) === 0) {
                    self::setErrorMessage(self::$soapErrorNonNormal);
                    return array('status' => 'error', 'text' => self::getErrorMessage());
                } else {
                    return $data;
                }
            }
        } else {
            if (self::isException()) {
                return array('status' => 'error', 'text' => self::getErrorMessage());
            } else {
                self::setErrorMessage('Error');
                return array('status' => 'error', 'text' => self::getErrorMessage());
            }
        }
    }
    
    public function runJsonResponse($serviceAddr, $command, $param = array(), $unitName = '', $shortWsdl = null, $rowname = 'return', $isTest = false) {
        $dataElement = self::jsonHeaderParam($command, $param, $isTest);
        
        if ($shortWsdl === null) {
            $shortWsdl = self::getShortWsdlAddress();
        }
        
        if ($configServiceAddress = self::getConfigServiceAddress($command)) {
            $serviceAddr = $configServiceAddress['serviceAddr'];
            $shortWsdl = $configServiceAddress['shortWsdl'];
        }
        
        $data = self::soapCallAddr($serviceAddr, $shortWsdl, 'runJson', $dataElement);
     
        if ($data) {
            if (isset($data->return)) {
                
                $jsonStr = str_replace("\n", "", $data->return);
                $jsonStr = str_replace('\\', '', $jsonStr);
                
                $newArr = json_decode($jsonStr, true);
                
                return isset($newArr['response']) ? $newArr['response'] : null;
                
            } else {
                if (count(get_object_vars($data)) === 0) {
                    self::setErrorMessage(self::$soapErrorNonNormal);
                    return array('status' => 'error', 'text' => self::getErrorMessage());
                } else {
                    return $data;
                }
            }
        } else {
            if (self::isException()) {
                return array('status' => 'error', 'text' => self::getErrorMessage());
            } else {
                self::setErrorMessage('Error');
                return array('status' => 'error', 'text' => self::getErrorMessage());
            }
        }
    }
    
    public function runByteJsonResponse($serviceAddr, $command, $param = array(), $unitName = '', $shortWsdl = null, $rowname = 'return', $isTest = false) {
        $dataElement = self::dataElementHeaderParam($command, $param, $isTest);
        
        if ($shortWsdl === null) {
            $shortWsdl = self::getShortWsdlAddress();
        }
        
        if ($configServiceAddress = self::getConfigServiceAddress($command)) {
            $serviceAddr = $configServiceAddress['serviceAddr'];
            $shortWsdl = $configServiceAddress['shortWsdl'];
        }
        
        $data = self::soapCallAddr($serviceAddr, $shortWsdl, 'runByteJson', $dataElement);
     
        if ($data) {
            
            if (isset($data->return)) {
                
                includeLib('Compress/Compression');
                
                $decompressContent = Compression::decompress($data->return);
            
                return json_decode($decompressContent, true);
                
            } else {
                if (count(get_object_vars($data)) === 0) {
                    self::setErrorMessage(self::$soapErrorNonNormal);
                    return array('status' => 'error', 'text' => self::getErrorMessage());
                } else {
                    return $data;
                }
            }
            
        } else {
            if (self::isException()) {
                return array('status' => 'error', 'text' => self::getErrorMessage());
            } else {
                self::setErrorMessage('Error');
                return array('status' => 'error', 'text' => self::getErrorMessage());
            }
        }
    }
    
    public static function runSerializeResponse($serviceAddr, $command, $param = array(), $shortWsdl = null, $rowName = 'return', $isTest = false) {

        $dataElement = self::serializeHeaderParam($command, $param, $isTest);
        
        if ($shortWsdl === null) {
            $shortWsdl = self::getShortWsdlAddress();
        }
        
        if ($configServiceAddress = self::getConfigServiceAddress($command)) {
            $serviceAddr = $configServiceAddress['serviceAddr'];
            $shortWsdl = $configServiceAddress['shortWsdl'];
        }
        
        $data = self::soapCallAddr($serviceAddr, $shortWsdl, 'runSerialize', $dataElement);
        
        if ($data) {
            if (isset($data->return)) {
                
                $newArr = unserialize($data->return);
                return $newArr['response'];
                
            } else {
                if (count(get_object_vars($data)) === 0) {
                    self::setErrorMessage(self::$soapErrorNonNormal);
                    return array('status' => 'error', 'text' => self::getErrorMessage());
                } else {
                    return $data;
                }
            }
        } else {
            if (self::isException()) {
                return array('status' => 'error', 'text' => self::getErrorMessage());
            } else {
                self::setErrorMessage('Error');
                return array('status' => 'error', 'text' => self::getErrorMessage());
            }
        }
    }
    
    public function runArrayResponse($serviceAddr, $command, $param = array(), $shortWsdl = null, $rowname = 'return', $isTest = false) {

        $dataElement = self::serializeHeaderParam($command, $param, $isTest);
        
        if ($shortWsdl === null) {
            $shortWsdl = self::getShortWsdlAddress();
        }
        
        if ($configServiceAddress = self::getConfigServiceAddress($command)) {
            $serviceAddr = $configServiceAddress['serviceAddr'];
            $shortWsdl = $configServiceAddress['shortWsdl'];
        }
        
        $data = self::soapCallAddr($serviceAddr, $shortWsdl, 'runArray', $dataElement);
        
        if ($data) {
            if (isset($data->return)) {

                eval('$newArr = '.str_replace("\','", "\'','", $data->return).';');
                        
                return $newArr['response'];
                
            } else {
                if (count(get_object_vars($data)) === 0) {
                    self::setErrorMessage(self::$soapErrorNonNormal);
                    return array('status' => 'error', 'text' => self::getErrorMessage());
                } else {
                    return $data;
                }
            }
        } else {
            if (self::isException()) {
                return array('status' => 'error', 'text' => self::getErrorMessage());
            } else {
                self::setErrorMessage('Error');
                return array('status' => 'error', 'text' => self::getErrorMessage());
            }
        }
    }
    
    public function run($runMode, $command, $param = array(), $serviceAddr = null) {
        
        switch ($runMode) {
            
            case 'array':
                
                if ($serviceAddr) {
                    self::$isDefaultSessionId = true; 
                    $data = self::runArrayResponse($serviceAddr, $command, $param);
                } else {
                    $data = self::runArrayResponse(GF_SERVICE_ADDRESS, $command, $param);      
                }
                
            break;   
            
            case 'serialize':
                
                if ($serviceAddr) {
                    self::$isDefaultSessionId = true; 
                    $data = self::runSerializeResponse($serviceAddr, $command, $param);
                } else {
                    $data = self::runSerializeResponse(GF_SERVICE_ADDRESS, $command, $param);      
                }
                
            break;   
            
            case 'json':
                
                if ($serviceAddr) {
                    self::$isDefaultSessionId = true; 
                    $data = self::runJsonResponse($serviceAddr, $command, $param);
                } else {
                    $data = self::runJsonResponse(GF_SERVICE_ADDRESS, $command, $param);      
                }
                
            break;   
            
            case 'dataElement':
                
                if ($serviceAddr) {
                    self::$isDefaultSessionId = true; 
                    $data = self::runResponse($serviceAddr, $command, $param);
                } else {
                    $data = self::runResponse(GF_SERVICE_ADDRESS, $command, $param);      
                }
                
            break;   
        }
        
        return $data;
    }
    
    public function runSerializeDefaultSession($serviceAddr, $command, $param = array(), $shortWsdl = null, $rowname = 'return', $isTest = false) {

        $dataElement = self::serializeHeaderParamDefaultSession($command, $param, $isTest);
        
        if ($shortWsdl === null) {
            $shortWsdl = self::getShortWsdlAddress();
        }
        
        if ($configServiceAddress = self::getConfigServiceAddress($command)) {
            $serviceAddr = $configServiceAddress['serviceAddr'];
            $shortWsdl = $configServiceAddress['shortWsdl'];
        }
        
        $data = self::soapCallAddr($serviceAddr, $shortWsdl, 'runSerialize', $dataElement);
        
        if ($data) {
            if (isset($data->return)) {
                
                $newArr = unserialize($data->return);
                return $newArr['response'];
                
            } else {
                if (count(get_object_vars($data)) === 0) {
                    self::setErrorMessage(self::$soapErrorNonNormal);
                    return array('status' => 'error', 'text' => self::getErrorMessage());
                } else {
                    return $data;
                }
            }
        } else {
            if (self::isException()) {
                return array('status' => 'error', 'text' => self::getErrorMessage());
            } else {
                self::setErrorMessage('Error');
                return array('status' => 'error', 'text' => self::getErrorMessage());
            }
        }
    }

    public function wsObjectToArray($data, $rowname) {
        if (is_object($data)) {
            $array = objectToArray($data);
            if (isset($array[$rowname])) {
                if (is_array($array[$rowname])) {
                    if (!array_key_exists(0, $array[$rowname])) {
                        $array = array(0 => $array[$rowname]);
                        return $array;
                    } else {
                        return $array[$rowname];
                    }
                } else {
                    $array = array(0 => array($rowname => $array[$rowname]));
                    return $array;
                }
            } else {
                return $array;
            }
        } else {
            return array(
                'text' => self::getErrorMessage()
            );
        }
    }

    public function convertInputType($value, $inputType) {
        switch ($inputType) {
            case 'long':
                return empty($value) ? Input::param($value) : Input::paramFloat($value);
                break;
            case 'decimal':
                return empty($value) ? Input::param($value) : Number::decimal(Input::param($value));
                break;
            default:
                return Input::param($value);
                break;
        }
    }

    public function convertDeParamType($value, $inputType) {
        
        switch ($inputType) {
            
            case 'long': 
                $values = array('vid1', 'vid2', 'vid3', 'seq');
                return empty($value) ? Input::param($value) : (in_array($value, $values) ? $value : Input::param($value));
                break;
            case 'bigdecimal':
            case 'decimal':
            case 'number':
                return empty($value) ? Input::param($value) : Number::decimal(Input::param($value));
                break;
            case 'time':
                return empty($value) ? Input::param($value) : '1999-01-01 '.Input::param($value).':00';
                break;
            case 'boolean':
                return ($value != '') ? Input::param($value) : '0';
                break; 
            case 'multicomma':
                return is_array($value) ? Input::param(Arr::implode_r(',', $value, true)) : Input::param($value);
                break; 
            case 'multi':
                return is_array($value) ? ((Arr::implode_r(',', $value, true)) ? $value : null) : Input::param($value);
                break; 
            case 'clob':
            case 'expression_editor': 
            case 'graph': 
                return Security::jsReplacer($value);
                break;
            case 'combo_with_popup':
                $val = Input::param($value);
                return ($val == '-0') ? null : $val;
                break;
            default:
                return Input::param($value);
                break;
        }
    }
    
    public static function pushLangCriteria($command, $params) {
        
        if ($command == 'PL_MDVIEW_004') {
            
            if (is_array($params) && array_key_exists('criteria', $params)) {

                $langCriteria['langCode'][] = array(
                    'operator' => '=',
                    'operand' => Lang::getCode()  
                );
                $params['criteria'] = array_merge($params['criteria'], $langCriteria);

            } else {

                $params['criteria'] = array(
                    'langCode' => array(
                        array(
                            'operator' => '=',
                            'operand' => Lang::getCode()
                        )
                    )
                );
            }
        }
        
        return $params;
    }
    
    public static function getToken() {
        return Hash::encryption(Date::currentDate('Y-m-d H:i:s'));
    }
    
    public static function dataElementHeaderParam($command, $params, $isTest = false) {
        
        $sessionUpdated = 'true';
        
        if (self::$isDefaultSessionId) {
            $appUserSessionId = self::$defaultSessionId;
        } else {
            $appUserSessionId = Ue::appUserSessionId();
            
            if (!$appUserSessionId) {
                $appUserSessionId = self::$defaultSessionId;
            } else {
                $sessionUpdated = 'false';
            }
        }
        
        self::setClientUserInfo();
        
        if ($command == 'PL_MDVIEW_004') {
            $params['__isUseReport'] = 1;
        }
        
        if (Input::numeric('isNotUseReport') == 1) {
            unset($params['__isUseReport']);
        }
            
        $dataElement = array(
            'pDataElement' => array(
                'key' => 'request',
                'elements' => array(
                    array(
                        'key' => 'unitName',
                        'value' => Session::unitName()
                    ),
                    array(
                        'key' => 'command',
                        'value' => $command
                    ),
                    array(
                        'key' => 'parameters',
                        'elements' => Arr::arrayToDataElement(self::pushLangCriteria($command, $params))
                    ), 
                    array(
                        'key' => 'sessionId',
                        'value' => $appUserSessionId
                    ),
                    array(
                        'key' => 'sessionUpdated',
                        'value' => $sessionUpdated 
                    ),
                    array(
                        'key' => 'languageCode',
                        'value' => Lang::getCode() 
                    ),
                    array(
                        'key' => 'sessionToken',
                        'value' => self::getToken() 
                    ),
                    array(
                        'key' => 'isTest',
                        'value' => $isTest
                    ),
                    array(
                        'key' => 'isCustomer',
                        'value' => self::$isCustomer 
                    ),
                    array(
                        'key' => 'userInfo',
                        'elements' => array(
                            array(
                                'key' => 'ipAddress',
                                'value' => self::$ipAddress
                            ),
                            array(
                                'key' => 'osName',
                                'value' => self::$osName
                            ),
                            array(
                                'key' => 'platformName',
                                'value' => self::$platformName
                            ),
                            array(
                                'key' => 'userAgent',
                                'value' => $_SERVER['HTTP_USER_AGENT']
                            )
                        )
                    )
                )
            )
        );

        return $dataElement;
    }
    
    public function jsonHeaderParam($command, $params, $isTest = false) { 
        
        $sessionUpdated = 'true';
        
        if (self::$isDefaultSessionId) {
            $appUserSessionId = self::$defaultSessionId;
        } else {
            $appUserSessionId = Ue::appUserSessionId();
            
            if (!$appUserSessionId) {
                $appUserSessionId = self::$defaultSessionId;
            } else {
                $sessionUpdated = 'false';
            }
        }
        
        self::setClientUserInfo();
        
        if ($command == 'PL_MDVIEW_004') {
            $params['__isUseReport'] = 1;
        }
        
        if (Input::numeric('isNotUseReport') == 1) {
            unset($params['__isUseReport']);
        }

        $paramData = array(
            'request' => array(
                'unitName'       => Session::unitName(), 
                'command'        => $command, 
                'parameters'     => $params, 
                'sessionId'      => $appUserSessionId, 
                'sessionUpdated' => $sessionUpdated, 
                'languageCode'   => Lang::getCode(),
                'sessionToken'   => self::getToken(),
                'isTest'         => $isTest, 
                'isCustomer'     => self::$isCustomer, 
                'userInfo'       => array(
                    'ipAddress'    => self::$ipAddress, 
                    'osName'       => self::$osName, 
                    'platformName' => self::$platformName, 
                    'userAgent'    => self::$userAgent
                ) 
            )
        );

        return array('pJsonString' => json_encode($paramData, JSON_UNESCAPED_UNICODE));
    }
    
    public static function serializeHeaderParam($command, $params, $isTest = false) {     
        
        $sessionUpdated = 'true';
        
        if (self::$isDefaultSessionId) {
            $appUserSessionId = self::$defaultSessionId;
        } else {
            $appUserSessionId = Ue::appUserSessionId();
            
            if (!$appUserSessionId) {
                $appUserSessionId = self::$defaultSessionId;
            } else {
                $sessionUpdated = 'false';
            }
        }
        
        self::setClientUserInfo();
        
        $paramData = array(
            'request' => array(
                'unitName'       => Session::unitName(), 
                'command'        => $command, 
                'parameters'     => array(), 
                'sessionId'      => $appUserSessionId, 
                'sessionUpdated' => $sessionUpdated,
                'languageCode'   => Lang::getCode(),
                'sessionToken'   => self::getToken(),
                'isTest'         => $isTest, 
                'isCustomer'     => self::$isCustomer
            )
        );
        
        if (self::$addonHeaderParam) {
            $paramData['request'] = array_merge($paramData['request'], self::$addonHeaderParam);
            
            if (isset(self::$addonHeaderParam['_runTest'])) {
                $paramData['request']['_runTest'] = self::$addonHeaderParam['_runTest'];
            }
        }
        
        if ($command == 'PL_MDVIEW_004') {
            $params['__isUseReport'] = 1;
        }
        
        if (Input::numeric('isNotUseReport') == 1) {
            unset($params['__isUseReport']);
        }
        
        $paramData['request']['parameters'] = $params;
        $paramData['request']['userInfo'] = array(
            'ipAddress'    => self::$ipAddress, 
            'osName'       => self::$osName, 
            'platformName' => self::$platformName, 
            'userAgent'    => self::$userAgent
        );

        return array('pPhpSerializedString' => serialize($paramData));
    }
    
    public function arrayHeaderParam($command, $params, $isTest = false) {     
        
        $sessionUpdated = 'true';
        
        if (self::$isDefaultSessionId) {
            $appUserSessionId = self::$defaultSessionId;
        } else {
            $appUserSessionId = Ue::appUserSessionId();
            
            if (!$appUserSessionId) {
                $appUserSessionId = self::$defaultSessionId;
            } else {
                $sessionUpdated = 'false';
            }
        }
        
        self::setClientUserInfo();
        
        if ($command == 'PL_MDVIEW_004') {
            $params['__isUseReport'] = 1;
        }
        
        if (Input::numeric('isNotUseReport') == 1) {
            unset($params['__isUseReport']);
        }
        
        $paramData = array(
            'request' => array(
                'unitName'       => Session::unitName(), 
                'command'        => $command, 
                'parameters'     => $params, 
                'sessionId'      => $appUserSessionId, 
                'sessionUpdated' => $sessionUpdated,
                'languageCode'   => Lang::getCode(),
                'sessionToken'   => self::getToken(),
                'isTest'         => $isTest, 
                'isCustomer'     => self::$isCustomer, 
                'userInfo'       => array(
                    'ipAddress'    => self::$ipAddress, 
                    'osName'       => self::$osName, 
                    'platformName' => self::$platformName, 
                    'userAgent'    => self::$userAgent
                )
            )
        );
        
        return array('pArrayString' => $paramData);
    }
    
    public function serializeHeaderParamDefaultSession($command, $params, $isTest = false) {     
        
        self::setClientUserInfo();
        
        if ($command == 'PL_MDVIEW_004') {
            $params['__isUseReport'] = 1;
        }
        
        if (Input::numeric('isNotUseReport') == 1) {
            unset($params['__isUseReport']);
        }
        
        $paramData = array(
            'request' => array(
                'command'        => $command, 
                'parameters'     => $params, 
                'sessionId'      => self::$defaultSessionId, 
                'sessionUpdated' => 'true',
                'languageCode'   => Lang::getCode(),
                'sessionToken'   => self::getToken(),
                'isTest'         => $isTest, 
                'isCustomer'     => self::$isCustomer, 
                'userInfo'       => array(
                    'ipAddress'    => self::$ipAddress, 
                    'osName'       => self::$osName, 
                    'platformName' => self::$platformName, 
                    'userAgent'    => self::$userAgent
                )
            )
        );
        
        return array('pPhpSerializedString' => serialize($paramData));
    }
    
    public function serializeRestParam($command, $params) {    
        
        $sessionUpdated = 'true';
        
        if (self::$isDefaultSessionId) {
            $appUserSessionId = self::$defaultSessionId;
        } else {
            $appUserSessionId = Ue::appUserSessionId();
            
            if (!$appUserSessionId) {
                $appUserSessionId = self::$defaultSessionId;
            } else {
                $sessionUpdated = 'false';
            }
        }
        
        self::setClientUserInfo();
        
        if ($command == 'PL_MDVIEW_004') {
            $params['__isUseReport'] = 1;
        }
        
        if (Input::numeric('isNotUseReport') == 1) {
            unset($params['__isUseReport']);
        }
        
        $paramData = array(
            'request' => array(
                'unitName'       => Session::unitName(), 
                'command'        => $command, 
                'parameters'     => $params, 
                'sessionId'      => $appUserSessionId, 
                'sessionUpdated' => $sessionUpdated, 
                'languageCode'   => Lang::getCode(),
                'sessionToken'   => self::getToken(),
                'isCustomer'     => self::$isCustomer, 
                'userInfo'       => array(
                    'ipAddress'    => self::$ipAddress, 
                    'osName'       => self::$osName, 
                    'platformName' => self::$platformName, 
                    'userAgent'    => self::$userAgent
                )
            )
        );
        return serialize($paramData);
    }
    
    public function getResponseMessage($data) {
        
        $message = isset($data['text']) ? $data['text'] : '';
        $message .= self::errorReport($data);

        return Str::quoteToHtmlChar(Str::removeNL(mb_substr($message, 0, 2000)));
    }
    
    public function errorReport($data) {

        $message = '';

        if (isset($data['result'])) {
            $message .= '<ul>';
            foreach ($data['result'] as $k => $v) {
                if (is_array($v)) {
                    $message .= '<li>' . self::errorChildReport($v) . '</li>';
                } else {
                    $message .= '<li><b>' . $k . '</b> <i class="fa fa-arrow-right"></i> ' . $v . '</li>';
                }
            }
            $message .= '</ul>';
        }

        return $message;
    }

    public function errorChildReport($data) {

        $message = '<ul>';

        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $message .= '<li>' . self::errorChildReport($v) . '</li>';
            } else {
                $message .= '<li><b>' . $k . '</b> <i class="fa fa-arrow-right"></i> ' . $v . '</li>';
            }
        }

        $message .= '</ul>';

        return $message;
    }

//    DE error return
//    param 1: Array soap response required
//    param 2: String Details name required
    public function showErrorDaEl($response, $dtlName = '') {
        $res = $errRes = array();
        $dtlName = Str::lower($dtlName);

        if (self::isException()) {
            return array(
                'status' => 'error',
                'message' => self::getErrorMessage()
            );
        } elseif ($response['status'] === 'success') {
            return array(
                'status' => 'success',
                'message' => Lang::line('msg_save_success')
            );
        } else {
            if (isset($response['result'][$dtlName])) {
                if (is_array($response['result'][$dtlName])) {
                    foreach ($response['result'][$dtlName] as $key => $row)
                        $errRes[$key] = Arr::arrayToDataElement($row);
                    $res = array(
                        'status' => 'error',
                        'text' => $response['text'],
                        'message' => $errRes
                    );
                } elseif (is_string($response['result'][$dtlName]))
                    $res = array(
                        'status' => 'error',
                        'message' => $response['text'] . "<br>" . $response['result'][$dtlName]
                    );
            } elseif (isset($response['text'])) {
                $headerErr = '';
                if(isset($response['result'])) {
                    foreach ($response['result'] as $k => $v)
                        $headerErr .=  '<strong>' . $k . '</strong> = ' . $v . '<br>';
                }
                $res = array(
                    'status' => 'error',
                    'message' => $response['text'] . '<br>' . $headerErr
                );
            } else
                $res = array(
                    'status' => 'error',
                    'message' => 'Алдаа гарлаа'
                );

            return $res;
        }
    }

    public function getValue($data) {
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                if (is_array($val)) {
                    return self::getValue($val);
                } else {
                    return $val;
                }
            }
        } else {
            return $data;
        }
    }

    public function returnValue($param) {
        if ($param['status'] == 'success') {
            return array(
                'status' => 'success',
                'result' => self::getValue($param['result']),
            );
        } else {
            return array(
                'status' => 'error',
                'message' => $param['text'] . " <br /> " . self::errorReport($param)
            );
        }
    }
    
    public function runRest($url, $methodName, $params) {
        $lastParams = self::serializeRestParam($methodName, $params);
        
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $url.'runSerialize');
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_handle, CURLOPT_POST, true);
        curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $lastParams);
        curl_setopt($curl_handle, CURLOPT_HEADER, false);
        curl_setopt($curl_handle, CURLOPT_HTTPHEADER, array(                                                                          
            'Content-Type: application/xml'                                                                                                                                                    
        ));      

        $buffer = curl_exec($curl_handle);
        curl_close($curl_handle);
        
        $result = unserialize($buffer);

        if (isset($result['status']) && $result['status'] == 'success') {
            return $result;
        } else {
            return 'error';
        }
    }
    
    public function requestPost($url, $params = array()) {
        
        $curl_handle = curl_init();
        
        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_handle, CURLOPT_POST, true);
        curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curl_handle, CURLOPT_HEADER, false);
        curl_setopt($curl_handle, CURLOPT_HTTPHEADER, array(           
            'User-Agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36', 
            'Content-Type: application/xml'                                                                                                                                                    
        ));    

        $buffer = curl_exec($curl_handle);       
        
        if ($buffer === false) {
            echo 'Curl error: ' . curl_error($curl_handle);        
        }
        
        curl_close($curl_handle);

        return $buffer;
    }
    
    public function curlRequest($url, $params = array(), $isResponseJson = false) {
        
        try {

            $ch = curl_init($url);
            
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params, JSON_UNESCAPED_UNICODE));
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); 
            curl_setopt($ch, CURLOPT_TIMEOUT, 300);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'User-Agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36', 
                'Accept: application/json', 
                'Content-Type: application/json;charset=UTF-8'
            ));

            $response = curl_exec($ch);
            
            if (curl_errno($ch)) {
                
                $msg = curl_error($ch);
                curl_close($ch);
                
                return array('status' => 'error', 'message' => $msg);
            }

            curl_close($ch); 
            
            if ($isResponseJson) {
                $response = remove_utf8_bom($response);
            } else {
                $response = json_decode(remove_utf8_bom($response), true);
            }
            
        } catch (Exception $ex) {
            
            $response = array('status' => 'error', 'message' => $ex->getMessage());
        }
        
        return $response;
    }
    
    public function redirectPost($url, array $data, array $headers = null) {
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true, 
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HEADER => false, 
            CURLOPT_SSL_VERIFYHOST => false, 
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded'
            )
        ));     

        $response = curl_exec($curl);       
        $err = curl_error($curl);
        
        curl_close($curl);

        if ($err) {
            var_dump('Error: ' . $err);
        } else {
            return $response;
        }
    }
    
    public function callSoapClient($serviceAddr, $operation, $param = null, $soapOption = array('exceptions' => 1)) {
        
        try {
            $client = new \SoapClient($serviceAddr, $soapOption);

            $param = (empty($param)) ? array() : array($param);
            
            $response = $client->__soapCall($operation, $param);

            if (is_soap_fault($response)) {
                return null;
            }

            return $response;
            
        } catch (\SoapFault $e) {
            if (isset($e)) {
                self::setErrorMessage($e->getMessage());
            }
            return null;
        }
    }
    
    public function getJsonByCurl($url) {
        
        try {

            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json;charset=UTF-8'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

            $response = curl_exec($ch);
            curl_close($ch); 
            
        } catch (Exception $ex) {
            $response = json_encode(array('status' => 'error', 'message' => $ex->getMessage()));
        }
        
        return $response;
    }
    
    public static function curlQueue($url, $params = array()) {
        ignore_user_abort(true);
        $ch = curl_init();
        $defaults = array( 
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_URL => $url,
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_NOSIGNAL => 1, 
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_TIMEOUT_MS => 300,
            CURLOPT_POSTFIELDS => json_encode($params, JSON_UNESCAPED_UNICODE)
        );
        curl_setopt_array($ch, $defaults);
        curl_exec($ch);
        curl_close($ch);
    }
    
    public static function soapHealthCheck($url) {
        if ($url && @file_get_contents($url, false, stream_context_create(array('http' => array('timeout' => 2))))) {
            return true;
        }
        return false;
    }
    
}

class SoapClientNG extends \SoapClient {

    public function __doRequest($req, $location, $action, $version, $one_way = 0) {
 
        $xml = explode("\r\n", parent::__doRequest($req, $location, $action, $version, $one_way));
        
        $bom = pack('H*', 'EFBBBF');
        
        $response = preg_replace('/^(\x00\x00\xFE\xFF|\xFF\xFE\x00\x00|\xFE\xFF|\xFF\xFE|\xEF\xBB\xBF)/', '', $xml[0]);
        $response = preg_replace("/^$bom/", '', $response);
        $response = preg_replace('/[\x00-\x08\x0B-\x1F]/', ' ', $response);
    
        return $response;
    }
}
