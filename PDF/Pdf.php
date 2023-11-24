<?php

if (!defined('_VALID_PHP')) exit('Direct access to this location is not allowed.');

/**
 * Pdf Class
 *
 * @package     IA PHPframework
 * @subpackage	Libraries
 * @category	Pdf
 * @author	S.Satjan <satjan@interactive.mn>
 * @link	http://www.interactive.mn/PHPframework/Pdf
 */
use Knp\Snappy\Pdf as SnappyPdf;

class Pdf {

    // <editor-fold defaultstate="collapsed" desc="Knp snappy https://github.com/KnpLabs/snappy">
    public static function createSnappyPdf($orientation = 'Portrait', $pageSize = 'A4', $title = 'Veritech ERP') {

        set_time_limit(0);
        ini_set('memory_limit', '-1');

        require_once(BASEPATH . LIBS . 'PDF/knp-snappy/vendor/autoload.php');
        require_once(BASEPATH . LIBS . 'PDF/knp-snappy/vendor/knplabs/knp-snappy/config/snappy_config.php');

        $pdf = new SnappyPdf();
        
        $envPath = getenv('WKHTMLTOPDF_PATH');
        $path = $envPath ? $envPath : BASEPATH . LIBS . 'PDF/knp-snappy/vendor/bin/wkhtmltopdf';
        
        $pdf->setBinary($path);
        
        $top    = (Input::isEmpty('top') == false) ? Input::post('top') : KNP_PDF_MARGIN_TOP;
        $left   = (Input::isEmpty('left') == false) ? Input::post('left') : KNP_PDF_MARGIN_LEFT;
        $right  = (Input::isEmpty('right') == false) ? Input::post('right') : KNP_PDF_MARGIN_RIGHT;
        $bottom = (Input::isEmpty('bottom') == false) ? Input::post('bottom') : KNP_PDF_MARGIN_BOTTOM;
        $pageSize = strtoupper($pageSize);
        
        $options = array(
            'title'            => $title,
            'orientation'      => $orientation,
            'page-size'        => !empty($pageSize) ? $pageSize : 'A4',
            'encoding'         => 'UTF-8',
            'no-outline'       => true,
            'images'           => true,
            'margin-top'       => $top,
            'margin-left'      => $left,
            'margin-right'     => $right,
            'margin-bottom'    => $bottom,
            'header-font-name' => KNP_PDF_FONT_NAME_MAIN,
            'header-font-size' => KNP_PDF_FONT_SIZE_MAIN,
            'footer-font-name' => KNP_PDF_FONT_NAME_DATA,
            'footer-font-size' => KNP_PDF_FONT_SIZE_DATA,
            'footer-line'      => true,
            'footer-right'     => '[page] / [toPage]'
        );
        
        $css = '<style type="text/css">';
        $css .= file_get_contents('assets/custom/css/print/snappyPrint.min.css');
        $css .= '</style>';  
        
        if (Input::post('isIgnoreFooter') == 1) {
            $options['no-footer-line'] = true;
            $options['footer-right'] = false;
        }      
        
        if ($pageSize == 'LETTER' && $orientation == 'Landscape') {
            
            $width = Input::post('width');
            $height = Input::post('height');
            
            if ($width && $height) {
                $options['page-width'] = $height;
                $options['page-height'] = $width;
            }
        }
        
        if (Input::post('isSmartShrinking') == 1) {
            $options['zoom'] = 1.25; //default 1
        }
        
        if (Input::isEmpty('headerHtml') === false) {
            $options['no-footer-line'] = true;
            $options['header-spacing'] = 1;
            $options['header-html'] = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>'.$css.'</head><body>' . html_entity_decode(Input::post('headerHtml'), ENT_QUOTES, 'UTF-8') . '</body></html>';
            $options['header-html'] = self::getReplacedHtml($options['header-html']);
            $options['header-html'] = self::headerFooterSizeReplace($options['header-html']);
        }
        
        if (Input::isEmpty('footerHtml') === false) {
            
            $options['no-footer-line'] = true;     
            $options['footer-right'] = false; 
            
            $footerHtml = html_entity_decode(Input::post('footerHtml'), ENT_QUOTES, 'UTF-8');
            
            if (strpos($footerHtml, '[page]') !== false) {
                $headerScript = "<script>
                    function subst() {
                        var vars = {};
                        var query_strings_from_url = document.location.search.substring(1).split('&');
                        for (var query_string in query_strings_from_url) {
                            if (query_strings_from_url.hasOwnProperty(query_string)) {
                                var temp_var = query_strings_from_url[query_string].split('=', 2);
                                vars[temp_var[0]] = decodeURI(temp_var[1]);
                            }
                        }
                        var css_selector_classes = ['page', 'frompage', 'topage', 'webpage', 'section', 'subsection', 'date', 'isodate', 'time', 'title', 'doctitle', 'sitepage', 'sitepages'];
                        for (var css_class in css_selector_classes) {
                            if (css_selector_classes.hasOwnProperty(css_class)) {
                                var element = document.getElementsByClassName(css_selector_classes[css_class]);
                                for (var j = 0; j < element.length; ++j) {
                                    element[j].textContent = vars[css_selector_classes[css_class]];
                                }
                            }
                        }";
                
                if (strpos($footerHtml, 'lastpage-hide') !== false) {
                    
                    $headerScript .= "
                        if (vars['page'] == vars['topage']) {
                            var elements = document.getElementsByClassName('lastpage-hide');
                            while (elements.length > 0) {
                                elements[0].parentNode.removeChild(elements[0]);
                            }
                        }";
                }
                
                $headerScript .= '} </script>';
                
                $footerHtml = str_replace(
                    array('[page]', '[topage]', '[sysdatetime]', '[sysdate]'), 
                    array('<span class="page"></span>', '<span class="topage"></span>', Date::currentDate(), Date::currentDate('Y-m-d')), 
                    $footerHtml
                );
                
            } else {
                $headerScript = '<script>function subst(){}</script>';
            }
            
            $footerHtml = self::getReplacedHtml($footerHtml);
            $footerHtml = self::headerFooterSizeReplace($footerHtml);
            
            $options['footer-html'] = "<!DOCTYPE html><html><head><meta charset=\"UTF-8\">$headerScript$css</head><body onload=\"subst()\">" . $footerHtml . "</body></html>";
        }

        $pdf->setOptions($options);
        $pdf->setTimeout(1800);

        return $pdf;
    }
    
    public function webUrlToPdf($options) {

        set_time_limit(0);
        ini_set('memory_limit', '-1');

        require_once(BASEPATH . LIBS . 'PDF/knp-snappy/vendor/autoload.php');
        require_once(BASEPATH . LIBS . 'PDF/knp-snappy/vendor/knplabs/knp-snappy/config/snappy_config.php');

        $pdf = new SnappyPdf();
        
        $envPath = getenv('WKHTMLTOPDF_PATH');
        $path = $envPath ? $envPath : BASEPATH . LIBS . 'PDF/knp-snappy/vendor/bin/wkhtmltopdf';

        $pdf->setBinary($path);

        $pdf->setOptions($options);
        $pdf->setTimeout(600);

        return $pdf;
    }

    public static function setSnappyOutput(SnappyPdf $pdf, $htmlContent, $fileName, $urlReplace = true) {
        header('Content-Disposition: attachment; filename="' . (!is_null($fileName) ? $fileName : 'veritechErp') . '.pdf"');
        self::setCommonHeader();
        $replacedHtml = $urlReplace ? self::getReplacedHtml($htmlContent) : $htmlContent;

        echo $pdf->getOutputFromHtml($replacedHtml); exit;
    }

    public static function generateFromHtml(SnappyPdf $pdf, $htmlContent, $fileName, array $options = array(), $overwrite = false, $urlReplace = true) {
        $replacedHtml = $urlReplace ? self::getReplacedHtml($htmlContent) : $htmlContent;
        $pdf->generateFromHtml($replacedHtml, $fileName . '.pdf', $options, $overwrite);
    }
    
    private static function getReplacedHtml($htmlContent) {
        
        $site_url = defined('BASEPATH') ? BASEPATH . '/' : '';
        $local_url = defined('LOCAL_URL') ? LOCAL_URL : URL;
        
        $replacedHtml = str_replace(
            array(
                '"storage/uploads/', 
                "'storage/uploads/", 
                'contenteditable="true"', 
                "contenteditable='true'", 
                "'api/svg_barcode.php", 
                URL
            ), 
            array(
                '"'.$site_url.'storage/uploads/', 
                "'".$site_url."storage/uploads/", 
                '', 
                '', 
                "'".$local_url."api/svg_barcode.php", 
                $site_url
            ), 
            $htmlContent
        );
        
        return $replacedHtml;
    }
    
    private static function headerFooterSizeReplace($htmlContent) {
        $htmlContent = str_replace('12pt', '13pt', $htmlContent);
        $htmlContent = str_replace('11pt', '12pt', $htmlContent);
        $htmlContent = str_replace('10pt', '11pt', $htmlContent);
		
        return $htmlContent;
    }

    // </editor-fold>
    // <editor-fold defaultstate="collapsed" desc="mPDF https://mpdf.github.io/">
    public function createMPdf($orientation = 'Portrait', $pageSize = 'A4') {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        require_once(BASEPATH . LIBS . "PDF/mpdf/mpdf.php");

        $author = 'Veritech ERP';
        $top    = !is_null(Input::post('top')) ? Input::post('top') : 10;
        $left   = !is_null(Input::post('left')) ? Input::post('left') : 10;
        $right  = !is_null(Input::post('right')) ? Input::post('right') : 10;
        $bottom = !is_null(Input::post('bottom')) ? Input::post('bottom') : 25;
        $pageSize = strtoupper($pageSize);
        
        $pdf = new mPDF('utf-8', $pageSize, 0, '', $left, $right, $top, $bottom, 9, 9, $orientation);

        $pdf->SetAuthor($author);
        $pdf->SetCreator($author);
        $pdf->SetTitle($author);
        $pdf->SetSubject($author);
        $pdf->SetKeywords($author);
        $pdf->SetMargins($left, $right, $top);
        $pdf->SetAutoPageBreak(TRUE, 15);
        $pdf->setFooter('{PAGENO}/{nbpg}');
        $pdf->AddPage($orientation);
        $pdf->SetFont('arial', '', 8);

        return $pdf;
    }

    public function setMpdfOutput($pdf, $htmlContent, $fileName) {
        self::setCommonHeader();

        $pdf->WriteHTML($htmlContent);

        $pdf->Output((!is_null($fileName) ? $fileName : 'file') . ' - ' . Date::currentDate('YmdHi') . '.pdf', 'D');
    }

    // </editor-fold>
    // <editor-fold defaultstate="collapsed" desc="Common">
    private static function setCommonHeader() {
        header('Content-Type: application/pdf');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Set-Cookie: fileDownload=true; path=/');
    }
    // </editor-fold>

    public function createSnappyPdfResolverMerge($orientation = 'Portrait', $pageSize = 'A4') {

        set_time_limit(0);
        ini_set('memory_limit', '-1');

        require_once(BASEPATH . LIBS . 'PDF/knp-snappy/vendor/autoload.php');
        require_once(BASEPATH . LIBS . 'PDF/knp-snappy/vendor/knplabs/knp-snappy/config/snappy_config2.php');

        $pdf = new SnappyPdf();
        
        $envPath = getenv('WKHTMLTOPDF_PATH');
        $path = $envPath ? $envPath : BASEPATH . LIBS . 'PDF/knp-snappy/vendor/bin/wkhtmltopdf';
        
        $pdf->setBinary($path);

        $top    = (Input::isEmpty('top') == false) ? Input::post('top') : KNP_PDF_MARGIN_TOP;
        $left   = (Input::isEmpty('left') == false) ? Input::post('left') : KNP_PDF_MARGIN_LEFT;
        $right  = (Input::isEmpty('right') == false) ? Input::post('right') : KNP_PDF_MARGIN_RIGHT;
        $bottom = (Input::isEmpty('bottom') == false) ? Input::post('bottom') : KNP_PDF_MARGIN_BOTTOM;
        $pageSize = strtoupper($pageSize);
        
        $options = array(
            'title'            => 'Veritech ERP',
            'orientation'      => $orientation,
            'page-size'        => !empty($pageSize) ? $pageSize : 'A4',
            'encoding'         => 'UTF-8',
            'no-outline'       => true,
            'images'           => true,
            'margin-top'       => $top,
            'margin-left'      => $left,
            'margin-right'     => $right,
            'margin-bottom'    => $bottom,
            'header-font-name' => KNP_PDF_FONT_NAME_MAIN,
            'header-font-size' => KNP_PDF_FONT_SIZE_MAIN,
            'footer-font-name' => KNP_PDF_FONT_NAME_DATA,
            'footer-font-size' => KNP_PDF_FONT_SIZE_DATA,
            'footer-line'      => true,
            'footer-right'     => '[page] / [toPage]'
        );
        
        if (Input::post('isIgnoreFooter') == 1) {
            $options['no-footer-line'] = true;
            $options['footer-right'] = false;
        }
        
        if (Input::isEmpty('headerHtml') === false) {
            $options['no-footer-line'] = true;
            $options['header-spacing'] = 1;
            $options['header-html'] = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>' . html_entity_decode(Input::post('headerHtml'), ENT_QUOTES, 'UTF-8') . '</body></html>';
        }
        
        if (Input::isEmpty('footerHtml') === false) {   
            
            $options['no-footer-line'] = true;     
            
            $footerHtml = html_entity_decode(Input::post('footerHtml'), ENT_QUOTES, 'UTF-8');
            
            if (strpos($footerHtml, '[page]') !== false) {
                
                $headerScript = "<script>
                    function subst() {
                        var vars = {};
                        var query_strings_from_url = document.location.search.substring(1).split('&');
                        for (var query_string in query_strings_from_url) {
                            if (query_strings_from_url.hasOwnProperty(query_string)) {
                                var temp_var = query_strings_from_url[query_string].split('=', 2);
                                vars[temp_var[0]] = decodeURI(temp_var[1]);
                            }
                        }
                        var css_selector_classes = ['page', 'frompage', 'topage', 'webpage', 'section', 'subsection', 'date', 'isodate', 'time', 'title', 'doctitle', 'sitepage', 'sitepages'];
                        for (var css_class in css_selector_classes) {
                            if (css_selector_classes.hasOwnProperty(css_class)) {
                                var element = document.getElementsByClassName(css_selector_classes[css_class]);
                                for (var j = 0; j < element.length; ++j) {
                                    element[j].textContent = vars[css_selector_classes[css_class]];
                                }
                            }
                        }";
                
                if (strpos($footerHtml, 'lastpage-hide') !== false) {
                    
                    $headerScript .= "
                        if (vars['page'] == vars['topage']) {
                            var elements = document.getElementsByClassName('lastpage-hide');
                            while (elements.length > 0) {
                                elements[0].parentNode.removeChild(elements[0]);
                            }
                        }";
                }
                
                $headerScript .= '} </script>';
                
                $footerHtml = str_replace(
                    array('[page]', '[topage]', '[sysdatetime]', '[sysdate]'), 
                    array('<span class="page"></span>', '<span class="topage"></span>', Date::currentDate(), Date::currentDate('Y-m-d')), 
                    $footerHtml
                );
                
            } else {
                $headerScript = '<script>function subst(){}</script>';
            }
            
            $options['footer-html'] = "<!DOCTYPE html><html><head><meta charset=\"UTF-8\">$headerScript</head><body onload=\"subst()\">" . $footerHtml . "</body></html>";
        }

        $pdf->setOptions($options);
        $pdf->setTimeout(1800);

        return $pdf;
    }    
    
}
