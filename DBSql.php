<?php 
/**
 * DBSql Class
 *
 * @package     IA PHPframework
 * @subpackage	Libraries
 * @category	DBSql
 * @author	B.Och-Erdene
 * @link	http://www.interactive.mn/PHPframework/DBSql
 */

class DBSql extends Controller {

    public function __construct() {
        parent::__construct();
    }
    
    public static function create_database($database, $charset = null, $if_not_exists = true){}
    
    public static function drop_database($database){}
            
    public static function create_table($table, $fields){}
    
    public static function execute($scripts)
    {
        global $db;
        $db->Execute($scripts);
    }
    
    public static function drop_table($db, $table)
    {
        try {
            $db->Execute('DROP TABLE IF EXISTS '.$table.' CASCADE CONSTRAINTS');
            return true;
        } catch (exception $e) {
            return false;
        }
    }
    
    public static function table_exists($db, $table)
    {
        try {
            $db->Execute("SELECT * FROM $table WHERE 1 = 0");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public static function rename_table($table, $new_table_name){}
    
    public static function add_fields($table, $fields){}
    
    public static function modify_fields($table, $fields){}
        
    public static function field_exists($db, $table, $columns){}
    
    public static function drop_fields($table, $fields){}
    
    protected static function alter_fields($type, $table, $fields){}
    
    public static function create_index($table, $index_columns, $index_name = '', $index = ''){}
    
    public static function drop_index($table, $index_name){}
    
    public static function add_foreign_key($table, $foreign_key){}
    
    public static function drop_foreign_key($table, $fk_name){}
    
    public static function truncate_table($table){}
    
    public static function dataViewQueryBindParams($request)
    {
        $bindParams = array();
        
        if (isset($request['query']) && isset($request['parameters']) && is_array($request['parameters'])) {
            
            global $db;
            
            $query  = $request['query'];
            $params = $request['parameters'];
            
            $k = count($params);
            $n = count($params) - 1;

            foreach ($params as $row) {
                
                $param     = $params[$n];
                
                $dataType  = $param['datatype'];
                $value     = $param['value'];
                
                $paramName = 'dvParam'.$k.'b';
                $paramPh   = (DB_DRIVER == 'postgres9') ? '$' . $k : $db->Param($paramName);
                
                $query = str_replace('?'.$k, $paramPh, $query);
                
                if ($value !== '' && $value != '') {
                    
                    if ($dataType == 'date') { 

                        $bindParams = array($paramName => $db->addQ(Date::formatter($value, 'Y-m-d'))) + $bindParams;

                    } elseif ($dataType == 'datetime') { 

                        $bindParams = array($paramName => $db->addQ(Date::formatter($value, 'Y-m-d H:i:s'))) + $bindParams;

                    } elseif ($dataType == 'array') { 

                        $commaArrs = explode(',', $value);
                        $inClause = '';

                        foreach ($commaArrs as $c => $commaVal) {

                            if ($commaVal != '') {

                                $inClauseParam = 'dvInParam'.$k.$c;

                                $inClause .= $db->Param($inClauseParam) . ', ';

                                $bindParams = array($inClauseParam => $db->addQ($commaVal)) + $bindParams;
                            }
                        }

                        $inClause = rtrim($inClause, ', ');

                        $query = str_replace($paramPh, '('.$inClause.')', $query);

                    } else {
                        $bindParams = array($paramName => $db->addQ($value)) + $bindParams;
                    }
                    
                } else {
                    $bindParams = array($paramName => null) + $bindParams;
                }

                $k--;
                $n--;
            }
            
            return array('query' => $query, 'bindParams' => $bindParams);
            
        } elseif (isset($request['query'])) {
            
            return array('query' => $request['query'], 'bindParams' => $bindParams);
        }
        
        return array('query' => $request, 'bindParams' => $bindParams);
    }
    
    public static function getQueryNamedParams($queryString)
    {
        preg_match_all('/:[_A-Za-z0-9]+/', $queryString, $matches);
        
        if (isset($matches[0][0])) {
            
            $matches = $matches[0];
            $arr = array();
            
            usort($matches, function($a, $b) {
                return strlen($a) < strlen($b);
            });
            
            foreach ($matches as $match) {
                $matchLower = strtolower($match);
                
                if ($matchLower != ':hh24' && $matchLower != ':mi' && $matchLower != ':hh' && $matchLower != ':ss') {
                    $arr[] = $match;
                }
            }
            
            return $arr;
        }
        
        return array();
    }

}
