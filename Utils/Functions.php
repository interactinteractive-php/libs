<?php if (!defined('_VALID_PHP')) exit('Direct access to this location is not allowed.');

class Functions {

    private static $getDataViewCommand = 'PL_MDVIEW_004';

    public static function runDataView($systemMetaGroupId, $criteria = array(), $isShowQuery = 0, $paging = array()) {
        return self::runIt($systemMetaGroupId, $criteria, $isShowQuery, $paging);
    }

    public static function runDataViewWithoutLogin($systemMetaGroupId, $criteria = array(), $isShowQuery = 0, $paging = array()) {
        return self::runIt($systemMetaGroupId, $criteria, $isShowQuery, $paging, true);
    }

    private static function runIt($systemMetaGroupId, $criteria = array(), $isShowQuery = 0, $paging = array(), $isWithoutLogin = false) {
        $param = array(
            'systemMetaGroupId' => $systemMetaGroupId,
            'showQuery' => $isShowQuery,
            'criteria' => $criteria,
            'paging' => $paging,
        );

        $result = array();

        if ($isWithoutLogin) {
            $param['ignorePermission'] = '1';
        }
        
        $dataResult = WebService::runResponse(GF_SERVICE_ADDRESS, self::$getDataViewCommand, $param);
        
        if ($dataResult['status'] === 'success') {
            if ($isShowQuery !== '1') {
                $result['paging'] = $dataResult['result']['paging'];
                $result['aggregatecolumns'] = $dataResult['result']['aggregatecolumns'];
                unset($dataResult['result']['paging']);
                unset($dataResult['result']['aggregatecolumns']);
            }
            
            $result['result'] = $dataResult['result'];
            return $result;
        } else {
            return $dataResult;    
        }
    }

    public static function dataToPagingResult($result) {
        if ($result && isset($result['result']) && isset($result['paging']) && isset($result['paging']['totalcount'])) {
            $resultArray = array();
            $resultArray['draw'] = Input::post('draw');
            $resultArray['iTotalRecords'] = $result['paging']['totalcount'];
            $resultArray['iTotalDisplayRecords'] = $result['paging']['totalcount'];
            $resultArray['recordsTotal'] = $result['paging']['totalcount'];
            $resultArray['recordsFiltered'] = $result['paging']['pagesize'];
            if (isset($result['result'])) {
                if (!is_array($result['result'])) {
                    $resultArray['data'] = array($result['result']);
                } else {
                    $resultArray['data'] = $result['result'];
                }
            } else {
                $resultArray['data'] = array();
            }

            if (isset($result['aggregatecolumns'])) {
                $resultArray['footerList'] = array(
                    $result['aggregatecolumns']
                );
            }
            return $resultArray;
        } else if (is_array($result)) {
            if (isset($result['errorMessage']) || isset($result['errorMessages'])) {
                return $result;
            } else {
                return TRUE;
            }
        } else {
            return FALSE;
        }
    }

    public static function getPaging() {
        $paramMerged = self::initPagingDtoByRequest();
        $pagingDto = $paramMerged['pagingDto'];
        $page = $pagingDto['offset'];
        $rows = $pagingDto['pageSize'];
        $paging = array(
            'offset' => $page,
            'pageSize' => $rows,
        );

        if ($pagingDto['sortColumnNames'] != '') {
            $paging['sortColumnNames'][$pagingDto['sortColumnNames']] = array(
                'sortType' => $pagingDto['sortType']
            );
        }

        return $paging;
    }

    public static function initPagingDtoByRequest($pageSize = null) {
        $page = 10;
        if (!is_null($pageSize)) {
            $page = $pageSize;
        }
        $result = array('pagingDto' => array('offset' => 1,
                'pageSize' => $page,
                'sortColumnNames' => 'sortColumn',
                'sortType' => 'asc',
                'totalCount' => $page,
                'isSortAsc' => true));
        if (is_numeric(Input::post("sEcho"))) {
            $result['pagingDto']['offset'] = Input::post("sEcho");
        }
        if (is_numeric(Input::post("pageSize"))) {
            $result['pagingDto']['pageSize'] = Input::post("pageSize");
        }
        if (is_string(Input::post("sortColumn"))) {
            $result['pagingDto']['sortColumnNames'] = Input::post("sortColumn");
        }
        if (is_bool(Input::post("isSortAsc"))) {
            $result['pagingDto']['isSortAsc'] = Input::post("isSortAsc");
        }
        if (is_string(Input::post("sortType"))) {
            $result['pagingDto']['sortType'] = Input::post("sortType");
            if (Input::post("sortType") === 'asc') {
                $result['pagingDto']['isSortAsc'] = true;
            } else {
                $result['pagingDto']['isSortAsc'] = false;
            }
        }

        return $result;
    }

    public static function unsetPagingDto($params) {
        unset($params['sEcho']);
        unset($params['pageSize']);
        unset($params['sortColumn']);
        unset($params['isSortAsc']);
        unset($params['sortType']);
        unset($params['draw']);

        return $params;
    }

    public static function getParamFilter($params) {
        (Array) $paramFilter = array();

        foreach ($params as $key => $value) {
            if ($value != '') {
                $paramFilter[$key][] = array(
                    'operator' => 'LIKE',
                    'operand' => '%' . $value . '%'
                );
            }
        }

        return $paramFilter;
    }

    public static function getParamFilterEqual($params) {
        $paramFilter = array();

        foreach ($params as $key => $value) {
            if ($value != '') {
                $paramFilter[$key][] = array(
                    'operator' => '=',
                    'operand' => $value
                );
            }
        }

        return $paramFilter;
    }

    public static function runProcess($commandName, $param = array()) {
        $resultBusinessProcess = WebService::caller('WSDL-DE', SERVICE_FULL_ADDRESS, $commandName, 'return', $param, 'serialize');
        return $resultBusinessProcess;
    }

}
