<?php if(!defined('_VALID_PHP')) exit('Direct access to this location is not allowed.');

class AssetNew {
    
    /* Үүнийг ашиглахгүй, хэсэг хугацааны дараа устгана. jquery.easyui.min.js рүү нэгтгэсэн */
    public function metaJs()
    {
        return array(
            'assets/custom/addon/plugins/jquery-easyui/datagrid-cellediting.js',
            'assets/custom/addon/plugins/jquery-easyui/datagrid-scrollview.js'
        );
    }
    
    public function metaOtherJs()
    {
        global $lang; 
        
        return array(
            'custom/addon/plugins/jquery-easyui/jquery.easyui.min.js',
            'custom/addon/plugins/jquery-easyui/locale/easyui-lang-' . $lang->getCode() . '.js',
            'core/js/plugins/addon/phpjs/phpjs.min.js'
        );
    }
    
    public function metaCss()
    {
        return array(
            'custom/css/fileexplorer.css',
            'custom/addon/plugins/jquery-easyui/themes/metro/easyui.css',
            'custom/addon/plugins/jstree/dist/themes/default/style.min.css'
        );
    }
    
    public function highchartJs()
    {
        return array(
            'custom/addon/plugins/highstock/js/highstock.js',
            'custom/addon/plugins/highstock/js/modules/exporting.js'
        );
    }
    
    public function amChartJs()
    {
        return array(
            'assets/custom/addon/plugins/amcharts4/core.js',
            'assets/custom/addon/plugins/amcharts4/charts.js',
            'assets/custom/addon/plugins/amcharts4/themes/animated.js',
            'assets/custom/addon/plugins/amcharts4/themes/material.js',
            'assets/custom/addon/plugins/amcharts4/themes/dark.js',
            'assets/custom/addon/plugins/amcharts/amcharts/amChartMinify.js',
            'middleware/assets/js/dashboard/charts_amcharts.js',

            /*echarts */
            'assets/custom/addon/plugins/echarts/echarts.js'
        );
    }
    
    public function amChartCss()
    {
        return array('assets/custom/addon/plugins/amcharts/amcharts/plugins/export/export.css');
    }
    
    public function authJs()
    {
        return array(
            'middleware/assets/js/addon/auth.js'
        );
    }

    public function authMetaJs()
    {
        return array(
            'middleware/assets/js/mdbp.js',
            'middleware/assets/js/mdexpression.js',
            'assets/custom/addon/plugins/jquery-easyui/datagrid-cellediting.js'
        );
    }
    
    public function lifeCycleCss()
    {
        return array(
            'custom/addon/plugins/jsplumb/css/style.css',
            'custom/addon/plugins/kwicks/step.css'
        );
    }
    
    public function lifeCycleJs()
    {
        return array('custom/addon/plugins/jsplumb/jsplumb.min.js');
    }
    
    public function treeViewBootstrapCss()
    {
        return array(
            'custom/addon/plugins/bootstrap-treeview-1.2.0/dist/bootstrap-treeview.min.css'
        );
    }
    
    public function treeViewBootstrapJs()
    {
        return array(
            'custom/addon/plugins/bootstrap-treeview-1.2.0/dist/bootstrap-treeview.min.js'
        );
    }
    
    public function editorCss() 
    {
        return array('global/css/report_template.css');        
    }
    
    public function calendarCss()
    {
        return array(
            'custom/addon/plugins/fullcalendar/fullcalendar.min.css',
            'global/css/calendar.css'
        );
    }
    
    public function calendarJs()
    {
        return array(
            'assets/custom/addon/plugins/fullcalendar/lib/moment.min.js',
            'assets/custom/addon/plugins/fullcalendar/fullcalendar.min.js',
            'assets/custom/addon/plugins/fullcalendar/lang/mn.js',
            'middleware/assets/js/calendar/mdcalendar.js'
        );
    }
    
}