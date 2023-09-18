<?php if (!defined('_VALID_PHP')) exit('Direct access to this location is not allowed.');

/**
 * Form Class
 *
 * @package     IA PHPframework
 * @subpackage	Libraries
 * @category	Form
 * @author	B.Och-Erdene
 * @link	http://www.interactive.mn/PHPframework/Form
 */
class Form {

    /**
     * create
     * This method return the form element <form...
     * @param   array(id, name, class, onsubmit, method, action, enctype, autocomplete)
     * @return  string
     */
    
    public static function arrayToAttribute($attributes = array()) {
        
        //$attr = str_replace('=', '="', http_build_query($attributes, null, '" ', PHP_QUERY_RFC3986));
        $attr = null;
        
        foreach ($attributes as $attribute => $value) {
            $attr .= $attribute . '="' . $value . '" ';

        }
        return $attr;
    }
    
    public static function create($attributes = array()) {
        $output = '<form '.self::arrayToAttribute($attributes).'>';
        return $output;
    }

    public static function close() {
        $o = '</form>';
        return $o;
    }

    /**
     * text
     * This method returns a input text element.
     * @param   array(id, name, class, onclick, value, length, width, disable, readonly, required, data-required, placeholder, autocomplete, data-format, tabindex)
     * @return  string
     */
    
    public static function text($attributes = array()) {
        $output = '<input type="text" '.self::arrayToAttribute($attributes).'/>';
        return $output;
    }

    /**
     * password
     * This method returns a input text element.
     * @param   array(id, name, class, onclick, value, length, width, disable, readonly, required, data-required, placeholder)
     * @return  string
     */
    
    public static function password($attributes = array()) {
        $output = '<input type="password" '.self::arrayToAttribute($attributes).'/>';
        return $output;
    }

    /**
     * textArea
     * This method creates a textarea element
     * @param   array(id, name, class, onclick, columns, rows, disabled, style)
     * @return  string
     */
    public static function textArea($attributes = array()) {
        
        if (isset($attributes['value'])) {
            $value = $attributes['value'];
            unset($attributes['value']);
        } else {
            $value = '';
        }
        
        $attrs = self::arrayToAttribute($attributes);
        $output = '<textarea '.$attrs.'>'.$value.'</textarea>';
        
        return $output;
    }

    /**
     * select
     * This method returns a select html element.
     * It can be given a param called value which then will be preselected
     * data has to be array(k=>v)
     * @param   array(id, name, class, onclick, onchange, disabled, required, text, data-live-search)
     * @return  string
     */
    public static function select($params = array()) {
        
        $isReturnArray = (isset($params['isReturnArray']) && $params['isReturnArray']) ? true : false;
        $op_text = '';
        
        $o = '<select';
        $o .= (isset($params['id'])) ? ' id="' . $params['id'] . '"' : '';
        $o .= (isset($params['name'])) ? ' name="' . $params['name'] . '"' : '';
        $o .= (isset($params['title'])) ? ' title="' . $params['title'] . '"' : '';
        $o .= (isset($params['class'])) ? ' class="' . $params['class'] . '"' : ' class="form-input select"';
        $o .= (isset($params['onclick'])) ? ' onclick="' . $params['onclick'] . '"' : '';
        $o .= (isset($params['onchange'])) ? ' onchange="' . $params['onchange'] . '"' : '';
        $o .= (isset($params['width'])) ? ' style="width:' . $params["width"] . 'px;"' : '';
        $o .= (isset($params['widthPercent'])) ? ' style="width:' . $params["widthPercent"] . '%;"' : '';
        $o .= (isset($params['disabled'])) ? ' disabled="' . $params['disabled'] . '"' : '';
        $o .= (isset($params['readonly'])) ? ' readonly="' . $params['readonly'] . '"' : '';
        $o .= (isset($params['data-path'])) ? ' data-path="' . $params['data-path'] . '"' : '';
        $o .= (isset($params['data-field-name'])) ? ' data-field-name="'.$params['data-field-name'].'"' : '';
        $o .= (isset($params['data-col-path'])) ? ' data-col-path="'.$params['data-col-path'].'"' : '';
        $o .= (isset($params['data-in-param'])) ? ' data-in-param="' . $params['data-in-param'] . '"' : '';
        $o .= (isset($params['data-in-lookup-param'])) ? ' data-in-lookup-param="' . $params['data-in-lookup-param'] . '"' : '';
        $o .= (isset($params['data-out-param'])) ? ' data-out-param="' . $params['data-out-param'] . '"' : '';
        $o .= (isset($params['data-out-group'])) ? ' data-out-group="' . $params['data-out-group'] . '"' : '';
        if (isset($params['required']) && $params['required'] == 'required') {
            $o .= ' required="required"';
        }
        $o .= (isset($params['style'])) ? ' style="' . $params['style'] . '"' : '';
        $o .= (isset($params['multiple'])) ? " multiple" : '';
        $o .= (isset($params['data-live-search'])) ? ' data-live-search="' . $params['data-live-search'] . '"'  : '';
        $o .= (isset($params['data-selected-text-format'])) ? ' data-selected-text-format="' . $params['data-selected-text-format'] . '"'  : '';        
        $o .= (isset($params['data-metadataid'])) ? ' data-metadataid="' . $params['data-metadataid'] . '"' : '';
        $o .= (isset($params['data-name'])) ? ' data-name="'.$params['data-name'].'"' : '';
        $o .= (isset($params['data-edit-value'])) ? ' data-edit-value="'.$params['data-edit-value'].'"' : '';
        $o .= (isset($params['data-row-data'])) ? ' data-row-data="'.$params['data-row-data'].'"' : '';
        $o .= (isset($params['data-isclear'])) ? ' data-isclear="' . $params['data-isclear'] . '"' : '';
        $o .= (isset($params['tabindex'])) ? ' tabindex="' . $params['tabindex'] . '"' : '';
        $o .= (isset($params['pftranslationjson'])) ? ' data-pftranslationjson="1"' : '';
        
        $op = '';
        if (isset($params['text'])) {
            if ($params['text'] != 'notext') {
                $placeholder = $params['text'];
                $op = '<option value="">' . $placeholder . '</option>';
            } 
        } else {
            $placeholder = '- '.Lang::line('select_btn').' -';
            $op = '<option value="">' . $placeholder . '</option>';
        }
        if (!empty($op)) {
            $o .= ' data-placeholder="' . $placeholder . '"';
        } else {
            $o .= (isset($params['data-placeholder'])) ? ' data-placeholder="' . $params['data-placeholder'] . '"' : '';
        }
        $o .= '>';
        
        $o .= $op;
        
        if (isset($params['data']) && is_array($params['data']) && $params['data'] != null && $params['op_text'] != '') {
            
            if (isset($params['pftranslationjson'])) {
                
                $separator = Mdcommon::$separator.Mdcommon::$separator;
                
                foreach ($params['data'] as $k => $v) {
                    
                    $ltext = $v[$params['op_text']];
                    $lval = $v[$params['op_value']];
                    $jsonArr = json_decode($v['pftranslationjson'], true);
                    $translatioJson = '';
                    
                    if (isset($jsonArr['value'][$params['pftranslationjson']])) {
                        $translatioJson = $separator . htmlentities(str_replace('&quot;', '\\&quot;', json_encode($jsonArr['value'][$params['pftranslationjson']], JSON_UNESCAPED_UNICODE)), ENT_QUOTES, 'UTF-8');
                    } 
                    
                    if (isset($params['value']) && $params['value'] == $v[$params['op_value']]) {
                        $o .= '<option value="'.$lval.$translatioJson.'" selected="selected">' . $ltext . '</option>';
                    } else {
                        $o .= '<option value="'.$lval.$translatioJson.'">' . $ltext . '</option>';
                    }
                }
                
            } elseif (isset($params['translationText']) && $params['translationText']) {
                
                foreach ($params['data'] as $k => $v) {

                    $ltext = str_replace(array("'", '"'), array("&#39;", "&#34;"), Lang::line($v[$params['op_text']]));
                    $lval = $v[$params['op_value']];
                    $lid = '';
                    
                    if (isset($params['op_id'])) {
                        $lid = ' id="'.$v[$params['op_id']].'"';
                    }

                    if (isset($params['op_param']) && $params['op_param'] != '') {	
                        $lid .= ' param="'.$v[$params['op_param']].'"';
                    }

                    if (isset($params['op_custom_attr'])) {
                        foreach ($params['op_custom_attr'] as $attr) {
                            $lid .= ' '.$attr['attr'].'="'.issetParam($v[$attr['key']]).'"';
                        }
                    }

                    if (isset($params['value'])) {
                        if (strpos($params['op_value'], '|') !== false) {
                            if ($lval == $params['value']) {
                                $o .= '<option value="' . $lval . '" selected="selected"'.$lid.'>' . $ltext . '</option>';
                            } else {
                                $o .= '<option value="' . $lval . '"'.$lid.'>' . $ltext . '</option>';
                            }
                        } elseif ($params['value'] == $v[$params['op_value']]) {
                            $o .= '<option value="' . $lval . '" selected="selected"'.$lid.'>' . $ltext . '</option>';
                        } else {
                            $o .= '<option value="' . $lval . '"'.$lid.'>' . $ltext . '</option>';
                        }
                    } else {
                        if (isset($params['selected'])) {
                            if ($params['selected'] == 'all') {
                                $o .= '<option value="' . $lval . '" selected="selected"'.$lid.'>' . $ltext . '</option>';
                            } else {
                                $o .= '<option value="' . $lval . '"'.$lid.'>' . $ltext . '</option>';
                            }
                        } else {
                            $o .= '<option value="' . $lval . '"'.$lid.'>' . $ltext . '</option>';
                        }
                    }
                }
                
            } else {
                
                if (strpos($params['op_text'], '|') !== false) {
                    $exp = explode('|', $params['op_text']);
                } 
                
                if (strpos($params['op_value'], '|') !== false) {
                    $val_exp = explode('|', $params['op_value']);
                }
                
                foreach ($params['data'] as $k => $v) {
                    
                    $ltext = $lval = $lid = '';
                    
                    if (isset($exp)) {
                        foreach ($exp as $vt) {
                            if ($vt == ' ') {
                                $ltext .= ' ';
                            } elseif ($vt == '(') {
                                $ltext .= '(';
                            } elseif ($vt == ')') {
                                $ltext .= ')';
                            } elseif ($vt == '.') { 
                                $ltext .= '.';
                            } elseif ($vt == '-') { 
                                $ltext .= '-';
                            } elseif ($vt == '_') { 
                                $ltext .= '_';
                            } elseif ($vt == '->') { 
                                $ltext .= '->';
                            } else {
                                $ltext .= str_replace(array("'", '"'), array("&#39;", "&#34;"), $v[$vt]);
                            }
                        }
                    } else {
                        $ltext = str_replace(array("'", '"'), array("&#39;", "&#34;"), $v[$params['op_text']]);
                    }

                    if (isset($val_exp)) {
                        foreach ($val_exp as $vv) {
                            if ($vv == ' ') {
                                $lval .= ' ';
                            } elseif ($vv == '(') {
                                $lval .= '(';
                            } elseif ($vv == ')') {
                                $lval .= ')';
                            } elseif ($vv == '.') {
                                $lval .= '.';
                            } elseif ($vv == '-') {
                                $lval .= '-';
                            } elseif ($vv == '_') {
                                $lval .= '_';
                            } elseif ($vv == '->') {
                                $lval .= '->';
                            } else {
                                $lval .= $v[$vv];
                            }
                        }
                    } else {
                        $lval = $v[$params['op_value']];
                    }

                    if (isset($params['op_id'])) {
                        $lid = ' id="'.$v[$params['op_id']].'"';
                    }

                    if (isset($params['op_param']) && $params['op_param'] != '') {	
                        $lid .= ' param="'.$v[$params['op_param']].'"';
                    }

                    if (isset($params['op_custom_attr'])) {
                        foreach ($params['op_custom_attr'] as $attr) {
                            
                            if ($attr['key'] == 'bgColor') {
                                $lid .= ' '.$attr['attr'].'="background-color: '.issetParam($v[$attr['key']]).'"';
                            } else {
                                $lid .= ' '.$attr['attr'].'="'.issetParam($v[$attr['key']]).'"';
                            }
                        }
                    }

                    if (isset($params['value'])) {
                        if (isset($val_exp)) {
                            if ($lval == $params['value']) {
                                $op_text = $ltext;
                                $o .= '<option value="' . $lval . '" selected="selected"'.$lid.'>' . $ltext . '</option>';
                            } else {
                                $o .= '<option value="' . $lval . '"'.$lid.'>' . $ltext . '</option>';
                            }
                        } elseif ($params['value'] == $v[$params['op_value']]) {
                            
                            $op_text = $ltext;
                            
                            if ($isReturnArray && isset($params['data-name'])) {
                                $op_text = $v[$params['data-name']];
                            }
                            
                            $o .= '<option value="' . $lval . '" selected="selected"'.$lid.'>' . $ltext . '</option>';
                        } else {
                            $o .= '<option value="' . $lval . '"'.$lid.'>' . $ltext . '</option>';
                        }
                    } else {
                        if (isset($params['selected'])) {
                            if ($params['selected'] == 'all') {
                                $o .= '<option value="' . $lval . '" selected="selected"'.$lid.'>' . $ltext . '</option>';
                            } else {
                                $o .= '<option value="' . $lval . '"'.$lid.'>' . $ltext . '</option>';
                            }
                        } else {
                            $o .= '<option value="' . $lval . '"'.$lid.'>' . $ltext . '</option>';
                        }
                    }
                }
            }
        }
        
        $o .= '</select>';
        
        if ($isReturnArray) {
            return array('control' => $o, 'op_text' => $op_text);
        }
        
        return $o;
    }

    /**
     * multiselect
     * This method returns a select html element.
     * It can be given a param called value which then will be preselected
     * data has to be array(k=>v)
     * @param   array(id, name, class, onclick, disabled, required, multiple, value, text, nonulloption)
     * @return  string
     */
    public static function multiselect($params = array()) {
        
        $isReturnArray = (isset($params['isReturnArray']) && $params['isReturnArray']) ? true : false;
        $op_text = '';
        
        $o = '<select';
        $o .= (isset($params['id'])) ? " id='{$params['id']}'" : '';
        $o .= (isset($params['name'])) ? " name='{$params['name']}'" : '';
        $o .= (isset($params['class'])) ? " class='{$params['class']}'" : '';
        $o .= (isset($params['onclick'])) ? " onclick='{$params['onclick']}'" : '';
        $o .= (isset($params['width'])) ? " style='width:{$params['width']}px;'" : '';
        $o .= (isset($params['disabled'])) ? " disabled='{$params['disabled']}'" : '';
        $o .= (isset($params['required'])) ? " required='{$params['required']}'" : '';
        $o .= (isset($params['style'])) ? ' style="' . $params['style'] . '"' : '';
        $o .= (isset($params['data-name'])) ? ' data-name="'.$params['data-name'].'"' : '';
        $o .= (isset($params['data-path'])) ? ' data-path="'.$params['data-path'].'"' : '';
        $o .= (isset($params['data-field-name'])) ? ' data-field-name="'.$params['data-field-name'].'"' : '';
        $o .= (isset($params['data-row-data'])) ? ' data-row-data="'.$params['data-row-data'].'"' : '';
        $o .= (isset($params['multiple'])) ? " multiple='{$params['multiple']}'" : '';
        $o .= (isset($params['data-in-param'])) ? ' data-in-param="' . $params['data-in-param'] . '"' : '';
        $o .= (isset($params['data-in-lookup-param'])) ? ' data-in-lookup-param="' . $params['data-in-lookup-param'] . '"' : '';
        $o .= (isset($params['data-out-param'])) ? ' data-out-param="' . $params['data-out-param'] . '"' : '';
        $o .= (isset($params['data-out-group'])) ? ' data-out-group="' . $params['data-out-group'] . '"' : '';
        $o .= (isset($params['data-metadataid'])) ? ' data-metadataid="' . $params['data-metadataid'] . '"' : ''; 
        $o .= (isset($params['data-isclear'])) ? ' data-isclear="' . $params['data-isclear'] . '"' : ''; 
        $o .= (isset($params['data-edit-value'])) ? ' data-edit-value="' . $params['data-edit-value'] . '"' : ''; 
        $o .= (isset($params['pftranslationjson'])) ? ' data-pftranslationjson="1"' : '';
        
        $o .= ' data-placeholder="' . (isset($params['text']) ? $params['text'] : '- ' . Lang::line('select_btn') . ' -') . '">';
        if (!isset($params['nonulloption'])) {
            $o .= '<option value=""></option>';
        }
        
        if (isset($params['data']) && is_array($params['data']) && $params['data'] != null) {
            
            if (isset($params['value']) && $params['value'] != null) {
                $arr = explode(',', $params['value']);
                reset($arr);
            }
            
            if (isset($params['pftranslationjson'])) {
                
                $separator = Mdcommon::$separator.Mdcommon::$separator;
                
                foreach ($params['data'] as $k => $v) {
                    
                    $ltext = $v[$params['op_text']];
                    $lval = $v[$params['op_value']];
                    $jsonArr = json_decode($v['pftranslationjson'], true);
                    $translatioJson = '';
                    
                    if (isset($jsonArr['value'][$params['pftranslationjson']])) {
                        $translatioJson = $separator . htmlentities(str_replace('&quot;', '\\&quot;', json_encode($jsonArr['value'][$params['pftranslationjson']], JSON_UNESCAPED_UNICODE)), ENT_QUOTES, 'UTF-8');
                    } 
                    
                    if (isset($params['value']) && isset($arr)) {
                        $selected = (in_array($v[$params['op_value']], $arr)) ? ' selected="selected"' : '';
                    } else {
                        $selected = null;
                    }
                    
                    $o .= '<option' . $selected . ' value="'.$lval.$translatioJson.'">' . $ltext . '</option>';
                }
                
            } else {
                
                foreach ($params['data'] as $k => $v) {
                    $exp = explode('|', $params['op_text']);
                    $ltext = '';
                    foreach ($exp as $vt) {
                        if ($vt == ' ') {
                            $ltext .= ' ';
                        } elseif ($vt == '(') {
                            $ltext .= '(';
                        } elseif ($vt == ')') {
                            $ltext .= ')';
                        } elseif ($vt == '.') { 
                            $ltext .= '.';
                        } elseif ($vt == '-') { 
                            $ltext .= '-';
                        } elseif ($vt == '_') { 
                            $ltext .= '_';
                        } elseif ($vt == '->') { 
                            $ltext .= '->';
                        } else {
                            $ltext .= str_replace(array("'", '"', '<', '>'), array('&#39;', '&#34;', '&lt;', '&gt;'), $v[$vt]);
                        }
                    }
                    
                    $lval = '';
                    
                    if (strpos($params['op_value'], '|') !== false) {
                        $val_exp = explode('|', $params['op_value']);
                        foreach ($val_exp as $vv) {
                            $lval .= $v[$vv] . '|';
                        }
                        $lval = rtrim($lval, '|');
                    } else {
                        $lval = $v[$params['op_value']];
                    }

                    $lid = '';

                    if (isset($params['op_custom_attr'])) {
                        foreach ($params['op_custom_attr'] as $attr) {
                            $lid .= ' '.$attr['attr'].'="'.issetParam($v[$attr['key']]).'"';
                        }
                    }

                    if (isset($params['value']) && isset($arr)) {
                        $selected = (in_array($v[$params['op_value']], $arr)) ? ' selected="selected"'.$lid : '';
                        $op_text = $ltext;
                    } else {
                        $selected = null;
                    }
                    $o .= '<option' . $selected . ' value="' . $lval . '"'.$lid.'>' . $ltext . '</option>';
                }
            }
        }
        
        $o .= '</select>';
        
        if ($isReturnArray) {
            return array('control' => $o, 'op_text' => $op_text);
        }
        
        return $o;
    }

    /**
     * checkbox
     * This method creates a checkbox element
     * @param   array(id, name, class, onclick, disabled)
     * @return  string
     */
    
    public static function checkbox($attributes = array()) {
        
        if (isset($attributes['saved_val']) && $attributes['saved_val'] == $attributes['value']) {
            unset($attributes['saved_val']);
            $addonAttr = ' checked="checked"';
        } else {
            $addonAttr = '';
        }
        
        $attrs = self::arrayToAttribute($attributes);
        $output = '<input type="checkbox" '.$attrs.$addonAttr.'/>';
        
        return $output;
    }

    /**
     * checkboxMulti
     * This method returns multiple checkbox elements in order given in an array
     * For checking of checkbox pass checked
     * Each checkbox should look like array(0=>array('id'=>'1', 'name'=>'cb[]', 'value'=>'x', 'label'=>'label_text' ))
     * @param   array(array(id, name, value, class, checked, disabled))
     * @return  string
     */
    public static function checkboxMulti($params = array()) {
        $o = '';
        if (!empty($params)) {
            $x = 0;
            foreach ($params as $k => $v) {
                $v['id'] = (isset($v['id'])) ? $v['id'] : "cb_id_{$x}_" . rand(1000, 9999);
                $o .= '<input type="checkbox"';
                $o .= (isset($v['id'])) ? " id='{$v['id']}'" : '';
                $o .= (isset($v['name'])) ? " name='{$v['name']}'" : '';
                $o .= (isset($v['value'])) ? " value='{$v['value']}'" : '';
                $o .= (isset($v['class'])) ? " class='{$v['class']}'" : '';
                $o .= (isset($v['checked'])) ? " checked='checked'" : '';
                $o .= (isset($v['disabled'])) ? " disabled='{$v['disabled']}'" : '';
                $o .= ' /> ';
                $o .= (isset($v['label'])) ? "<label for='{$v['id']}'>{$v['label']}</label> " : '';
                $x++;
            }
        }
        return $o;
    }

    /**
     * radioMulti
     * This method returns radio elements in order given in an array
     * For selection pass checked
     * Each radio should look like array(0=>array('id'=>'1', 'name'=>'rd[]', 'value'=>'x', 'label'=>'label_text' ))
     * @param   array(array(id, name, value, class, checked, disabled, label))
     * @return  string
     */
    public static function radioMulti($params = array(), $checkedValue = null, $isReturnArray = false) {
        $o = $op_text = '';
        if (!empty($params)) {
            $x = 0;
            foreach ($params as $k => $v) {
                $v['id'] = (isset($v['id'])) ? $v['id'] : "rd_id_{$x}_" . rand(1000, 9999);
                $o .= (isset($v['label'])) ? '<label '.((isset($v['labelclass'])) ? ' class="'.$v['labelclass'].'"' : 'class="radio"').'>' : '';
                $o .= '<input type="radio"';
                $o .= ' id="'.$v['id'].'"';
                $o .= (isset($v['name'])) ? ' name="'.$v['name'].'"' : '';
                $o .= (isset($v['class'])) ? ' class="'.$v['class'].'"' : "";
                if (isset($v['value'])) {
                    $o .= ' value="'.$v['value'].'"';
                    if (isset($checkedValue) && $v['value'] == $checkedValue) {
                        $o .= ' checked="checked"';
                        $op_text = $v['label'];
                    }
                }
                $o .= (isset($v['disabled'])) ? ' disabled="'.$v['disabled'].'"' : '';
                $o .= ' /> ';
                $o .= (isset($v['label'])) ? $v['label'] . " </label> " : '';
                $x++;
            }
        }
        
        if ($isReturnArray) {
            return array('control' => $o, 'op_text' => $op_text);
        } else {
            return $o;
        }
    }

    /**
     * This method returns a button element given the params for settings
     * @param   array(id, name, class, onclick, value, disabled, style)
     * @return  string
     */
    public static function button($attributes = array(), $show = true) {
        if ($show) {
        
            if (isset($attributes['value'])) {
                $value = $attributes['value'];
                unset($attributes['value']);
            } else {
                $value = '';
            }

            $attrs = self::arrayToAttribute($attributes);
            $output = '<button type="button" '.$attrs.'>'.$value.'</button>';

            return $output;
        }
        return null;
    }

    /**
     * This method returns a submit button element given the params for settings
     * @param   array(id, name, class, onclick, value, disabled, style)
     * @return  string
     */
    public static function submit($attributes = array()) {
        
        if (isset($attributes['value'])) {
            $value = $attributes['value'];
            unset($attributes['value']);
        } else {
            $value = '';
        }

        $attrs = self::arrayToAttribute($attributes);
        $output = '<button type="submit" '.$attrs.'>'.$value.'</button>';

        return $output;
    }

    /**
     * This method returns a hidden input elements given its params
     * @param   array(id, name, class, value)
     * @return  string
     */
    
    public static function hidden($attributes = array()) {
        $output = '<input type="hidden" '.self::arrayToAttribute($attributes).'/>';
        return $output;
    }

    /**
     * label
     * 
     * @param   array(id, name, class, width, for, text, required)
     * @return  string
     */
    public static function label($attributes = array()) {
        
        $addonAttr = '';
        
        if (isset($attributes['required']) && $attributes['required'] == 'required') {
            unset($attributes['required']);
            $addonAttr = ' <span class="required">*</span>';
        } 
        
        if (isset($attributes['text'])) {
            
            $addonAttr .= $attributes['text'];
            unset($attributes['text']);
            
            if (isset($attributes['no_colon'])) {
                unset($attributes['no_colon']);
                $addonAttr .= '';
            } else {
                $addonAttr .= '<span class="label-colon">:</span>';
            }  
        } 
        
        $attrs = self::arrayToAttribute($attributes);
        $output = '<label '.$attrs.'>'.$addonAttr.'</label>';
        
        return $output;
    }

    /**
     * selectData
     * This method returns a select html element.
     * It can be given a param called value which then will be preselected
     * data has to be array(k=>v)
     * @param   array(id, name, class, onclick, onchange, disabled, required)
     * @return  string
     */
    public static function selectData($params = array()) {
        $o = '<select';
        $o .= (isset($params['id'])) ? ' id="' . $params['id'] . '"' : '';
        $o .= (isset($params['name'])) ? ' name="' . $params['name'] . '"' : '';
        $o .= (isset($params['class'])) ? ' class="' . $params['class'] . '"' : " class='form-input select'";
        $o .= (isset($params['id'])) ? " id='{$params['id']}'" : '';
        $o .= (isset($params['name'])) ? " name='{$params['name']}'" : '';
        $o .= (isset($params['class'])) ? " class='{$params['class']}'" : "";
        $o .= (isset($params['onclick'])) ? " onclick='{$params['onclick']}'" : '';
        $o .= (isset($params['onchange'])) ? ' onchange="' . $params['onchange'] . '"' : '';
        $o .= (isset($params['width'])) ? ' style="width:' . $params["width"] . 'px;"' : '';
        $o .= (isset($params['disabled'])) ? " disabled='{$params['disabled']}'" : '';
        $o .= (isset($params['required'])) ? ' required="' . $params['required'] . '"' : '';
        $o .= (isset($params['style'])) ? ' style="' . $params['style'] . '"' : '';
        $o .= (isset($params['tabindex'])) ? ' tabindex="' . $params['tabindex'] . '"' : '';
        $o .= '>';
        $o .= '<option value="">' . (isset($params['text']) ? $params['text'] : '- ' . Lang::line('select_btn') . ' -') . '</option>';
        $o .= (isset($params['data'])) ? $params['data'] : '';
        $o .= '</select>';
        return $o;
    }

    /**
     * chosenselect
     * This method returns a select html element.
     * It can be given a param called value which then will be preselected
     * data has to be array(k=>v)
     * @param   array(id, name, class, onclick, onchange, disabled, required)
     * @return  string
     */
    public static function chosenselect($params = array()) {
        $o = '<select';
        $o .= (isset($params['id'])) ? " id='{$params['id']}'" : '';
        $o .= (isset($params['name'])) ? " name='{$params['name']}'" : '';
        $o .= (isset($params['class'])) ? " class='{$params['class']}'" : "";
        $o .= (isset($params['onclick'])) ? " onclick='{$params['onclick']}'" : '';
        $o .= (isset($params['onchange'])) ? ' onchange="' . $params['onchange'] . '"' : '';
        $o .= (isset($params['width'])) ? " style='width:{$params['width']}px;'" : '';
        $o .= (isset($params['disabled'])) ? " disabled='{$params['disabled']}'" : '';
        $o .= (isset($params['style'])) ? ' style="' . $params['style'] . '"' : '';
        $o .= (isset($params['required'])) ? " required='{$params['required']}'" : '';
        $o .= ' data-placeholder="' . (isset($params['text']) ? $params['text'] : '- ' . Lang::line('select_btn') . ' -') . '">';
        $o .= '<option value=""></option>';
        if (isset($params['data']) && is_array($params['data'])) {
            if ($params['data'] != null) {
                foreach ($params['data'] as $k => $v) {
                    if (isset($params['value']) && $params['value'] === $v[$params['op_value']]) {
                        $o .= '<option value="' . $v[$params['op_value']] . '" selected="selected">' . $v[$params['op_text']] . '</option>';
                    } else {
                        $o .= '<option value="' . $v[$params['op_value']] . '">' . $v[$params['op_text']] . '</option>';
                    }
                }
            }
        }
        $o .= '</select>';
        return $o;
    }
    
    /**
     * file
     * This method returns a input text element.
     * @param   array(id, name, class, onclick, value, length, width, disable, readonly, required, data-required, placeholder, autocomplete, tabindex)
     * @return  string
     */
    public static function file($attributes = array()) {
        $output = '<input type="file" '.self::arrayToAttribute($attributes).'/>';
        return $output;
    }
    
    public static function comboGroupNotChildMetas($params = array(), $mainMetaDataId, $allMetas, $paramPath, $paramId, $selectedVal = '') {

        $o = '<select';
        $o .= (isset($params['id'])) ? ' id="' . $params['id'] . '"' : '';
        $o .= (isset($params['name'])) ? ' name="' . $params['name'] . '"' : '';
        $o .= (isset($params['class'])) ? ' class="' . $params['class'] . '"' : ' class="form-input select"';
        $o .= (isset($params['onclick'])) ? ' onclick="' . $params['onclick'] . '"' : '';
        $o .= (isset($params['onchange'])) ? ' onchange="' . $params['onchange'] . '"' : '';
        $o .= (isset($params['width'])) ? ' style="width:' . $params["width"] . 'px;"' : '';
        $o .= (isset($params['disabled'])) ? ' disabled="' . $params['disabled'] . '"' : '';
        $o .= (isset($params['required'])) ? ' required="' . $params['required'] . '"' : '';
        $o .= (isset($params['style'])) ? ' style="' . $params['style'] . '"' : '';
        $o .= ' data-placeholder="' . (isset($params['text']) ? $params['text'] : '- ' . Lang::line('select_btn') . ' -') . '">';
        $o .= (isset($params['data-name'])) ? ' data-name="'.$params['data-name'].'"' : '';
        $o .= '>';
        if (isset($params['text'])) {
            if ($params['text'] != 'notext') {
                $o .= '<option value="">' . $params['text'] . '</option>';
            } 
        } else {
            $o .= '<option value="">- ' . Lang::line('select_btn') . ' -</option>';
        }

        if ($allMetas) {
            $selectedVal = Str::lower($selectedVal);
            foreach ($allMetas['paramPath'] as $pk => $prow) {
                $paramRealPath = Input::param($prow);
                if (strpos($paramRealPath, $paramPath.".") === false) {
                    if (Input::param($allMetas['metaType'][$prow][0]) !== Mdmetadata::$metaGroupMetaTypeId) {
                        $selected = '';
                        if ($selectedVal == Str::lower($paramRealPath)) {
                            $selected = ' selected="selected"';
                        }
                        $o .= '<option value="'.$allMetas['paramSrcAttr'][$prow][0].'|'.$allMetas['paramTrgAttr'][$prow][0].'|'.$paramRealPath.'"'.$selected.'>'.$paramRealPath.'</option>';
                    }
                }
            }
        }
        
        $o .= '</select>';
        
        return $o;
    }
    
    public static function comboStructureChildMetas($params = array(), $mainMetaDataId, $allMetas, $paramPath, $paramId, $refParamName, $selectedVal = '') {

        $o = '<select';
        $o .= (isset($params['id'])) ? ' id="' . $params['id'] . '"' : '';
        $o .= (isset($params['name'])) ? ' name="' . $params['name'] . '"' : '';
        $o .= (isset($params['class'])) ? ' class="' . $params['class'] . '"' : ' class="form-input select"';
        $o .= (isset($params['onclick'])) ? ' onclick="' . $params['onclick'] . '"' : '';
        $o .= (isset($params['onchange'])) ? ' onchange="' . $params['onchange'] . '"' : '';
        $o .= (isset($params['width'])) ? ' style="width:' . $params["width"] . 'px;"' : '';
        $o .= (isset($params['disabled'])) ? ' disabled="' . $params['disabled'] . '"' : '';
        $o .= (isset($params['required'])) ? ' required="' . $params['required'] . '"' : '';
        $o .= (isset($params['style'])) ? ' style="' . $params['style'] . '"' : '';
        $o .= ' data-placeholder="' . (isset($params['text']) ? $params['text'] : '- ' . Lang::line('select_btn') . ' -') . '">';
        $o .= (isset($params['data-name'])) ? ' data-name="'.$params['data-name'].'"' : '';
        $o .= '>';
        if (isset($params['text'])) {
            if ($params['text'] != 'notext') {
                $o .= '<option value="">' . $params['text'] . '</option>';
            } 
        } else {
            $o .= '<option value="">- ' . Lang::line('select_btn') . ' -</option>';
        }

        if ($allMetas) {
            $selectedVal = Str::lower($selectedVal);
            foreach ($allMetas as $prow) {
                $paramRealPath = $refParamName.'.'.$prow['META_DATA_CODE'];
                $selected = '';
                if ($selectedVal == Str::lower($paramRealPath)) {
                    $selected = ' selected="selected"';
                }
                $o .= '<option value="noid|'.$prow['META_DATA_ID'].'|'.$paramRealPath.'"'.$selected.'>'.$paramRealPath.'</option>';
            }
        }
        
        $o .= '</select>';
        
        return $o;
    }
    
    public static function comboOneGroupChildMetas($params = array()) {

        $o = '<select';
        $o .= (isset($params['id'])) ? ' id="' . $params['id'] . '"' : '';
        $o .= (isset($params['name'])) ? ' name="' . $params['name'] . '"' : '';
        $o .= (isset($params['class'])) ? ' class="' . $params['class'] . '"' : ' class="form-input select"';
        $o .= (isset($params['onclick'])) ? ' onclick="' . $params['onclick'] . '"' : '';
        $o .= (isset($params['onchange'])) ? ' onchange="' . $params['onchange'] . '"' : '';
        $o .= (isset($params['width'])) ? ' style="width:' . $params["width"] . 'px;"' : '';
        $o .= (isset($params['disabled'])) ? ' disabled="' . $params['disabled'] . '"' : '';
        $o .= (isset($params['required'])) ? ' required="' . $params['required'] . '"' : '';
        $o .= (isset($params['style'])) ? ' style="' . $params['style'] . '"' : '';
        $o .= ' data-placeholder="' . (isset($params['text']) ? $params['text'] : '- ' . Lang::line('select_btn') . ' -') . '">';
        $o .= (isset($params['data-name'])) ? ' data-name="'.$params['data-name'].'"' : '';
        $o .= '>';
        if (isset($params['text'])) {
            if ($params['text'] != 'notext') {
                $o .= '<option value="">' . $params['text'] . '</option>';
            } 
        } else {
            $o .= '<option value="">- ' . Lang::line('select_btn') . ' -</option>';
        }

        if ($params['data']) {
            
            $loopData = $params['data'];
            $i = 0;
            
            foreach ($loopData['metaType'] as $k => $row) {
                if ($row[0] != Mdmetadata::$metaGroupMetaTypeId) {
                    
                    $selected = '';
                    
                    if (isset($params['value']) && $params['value'] == $loopData['paramName'][$k][0].'-'.$loopData['paramTrgAttr'][$k][0]) {
                        $selected = ' selected="selected"';
                    }
                    
                    $metaRow = Mdmetadata::getMetaData($loopData['paramTrgAttr'][$k][0]);
                    $o .= '<option value="'.$loopData['paramName'][$k][0].'-'.$loopData['paramTrgAttr'][$k][0].'"'.$selected.'>'.$loopData['paramName'][$k][0].' - '.$metaRow['META_DATA_NAME'].'</option>';
                }
                $i++;
            }
        }
        
        $o .= '</select>';
        
        return $o;
    }

}
