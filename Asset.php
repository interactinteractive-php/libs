<?php if(!defined('_VALID_PHP')) exit('Direct access to this location is not allowed.');

class Asset {
    
    public function metaJs()
    {
        return array(
            'middleware/assets/js/mdmetadata.js',
            'middleware/assets/js/mdbp.js',
            'middleware/assets/js/mdexpression.js',
            'middleware/assets/js/mddv.js',
            'assets/core/global/plugins/jquery-easyui/datagrid-cellediting.js',
            'assets/core/global/plugins/jquery-easyui/datagrid-scrollview.js'
        );
    }
    
    public function metaOtherJs()
    {
        global $lang; 
        
        return array(
            'global/plugins/jquery-easyui/jquery.easyui.min.js',
            'global/plugins/jquery-easyui/locale/easyui-lang-' . $lang->getCode() . '.js',
            'global/plugins/phpjs/phpjs.min.js',
            'scripts/common/common.js'            
        );
    }
    
    public function metaCss()
    {
        return array(
            'global/css/fileexplorer/fileexplorer.css',
            'global/plugins/jquery-treetable/css/jquery.treegrid.css',
            'global/css/sheettable/sheettable.css',
            'global/plugins/jquery-contextmenu/src/jquery.contextmenu.css',
            'global/plugins/jquery-easyui/themes/metro/easyui.css',
            'global/plugins/jstree/dist/themes/default/style.min.css',
            'global/plugins/fancybox/source/jquery.fancybox.css',
        );
    }
    
    public function highchartJs()
    {
        return array(
            'global/plugins/highstock/js/highstock.js',
            'global/plugins/highstock/js/modules/exporting.js'
        );
    }
    
    public function amChartJs()
    {
        return array(
            'assets/core/global/plugins/amcharts/amcharts/amChartMinify.js',
            'middleware/assets/js/dashboard/charts_amcharts.js'
        );
    }
    
    public function amChartCss()
    {
        return array('assets/core/global/plugins/amcharts/amcharts/plugins/export/export.css');
    }
    
    public function lifeCycleCss()
    {
        return array(
            'global/plugins/jsplumb/css/style.css',
            'global/plugins/kwicks/step.css'
        );
    }
    
    public function lifeCycleJs()
    {
        return array('global/plugins/jsplumb/jsplumb.min.js');
    }
    
    public function treeViewBootstrapCss()
    {
        return array(
            'global/plugins/bootstrap-treeview-1.2.0/dist/bootstrap-treeview.min.css'
        );
    }
    
    public function treeViewBootstrapJs()
    {
        return array(
            'global/plugins/bootstrap-treeview-1.2.0/dist/bootstrap-treeview.min.js'
        );
    }
    
    public function codeMirrorCss() 
    {
        return array(
            'global/plugins/codemirror/lib/codemirror.css'
        );        
    }
    
    public function codeMirrorJs() 
    {
        return array(
            'global/plugins/codemirror/lib/codemirror.min.js'
        );        
    }
    
    public function editorCss() 
    {
        return array('global/css/report_template.css');        
    }
    
    public function calendarCss()
    {
        return array(
            'global/plugins/fullcalendar/fullcalendar.min.css',
            'global/css/calendar.css'
        );
    }
    
    public function calendarJs()
    {
        return array(
            'assets/core/global/plugins/fullcalendar/lib/moment.min.js',
            'assets/core/global/plugins/fullcalendar/fullcalendar.min.js',
            'assets/core/global/plugins/fullcalendar/lang/mn.js',
            'middleware/assets/js/calendar/mdcalendar.js'
        );
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
            'assets/core/global/plugins/jquery-easyui/datagrid-cellediting.js'
        );
    }
    
}