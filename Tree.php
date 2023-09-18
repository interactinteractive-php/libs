<?php if(!defined('_VALID_PHP')) exit('Direct access to this location is not allowed.');

/**
 * Tree Class
 *
 * @package     IA PHPframework
 * @subpackage	Libraries
 * @category	Tree
 * @author	B.Och-Erdene
 * @link	http://www.interactive.mn/PHPframework/Tree
 */
   
class Tree {
    public $treeId = null;
    public $rowId = null;
    public $parentId = null;
    public $rowType = null;
    public $rowName = null;
    public $rowHtml = null;
    public $iconShowType = 'row_type'; //isChild
    public $themeClass = null;
    public $customAttr = null;

    /**
     * Tree::__construct()
     * 
     * @return
     */
    public function __construct()
    {
        $this->iconShowType = 'row_type';
        $this->themeClass = null;
        $this->customAttr = null;
    }

    /**
     * Simple Folder & File Treeview
     * 
     * @param array $datas
     * @param type $parent
     * @param type $depth
     * @param type $number
     * @param type $row_type
     * @return string
     * 
     * @see assets/plugins/jquery-tree/jquery.treeview.css
     * @see assets/plugins/jquery-tree/jquery.treeview.js
     */
    public function simpleFolderView($datas, $parent = 0, $depth = 0, $number = null, $row_type = true)
    {
          $index = 1;
          $ulAttr = $tree = '';

          if ($datas != null) {

              if ($depth == 0) { $ulAttr = ' id="'.$this->treeId.'" class="filetree'.(($this->themeClass!=null)?' '.$this->themeClass:'').'"'; }
              if ($row_type) { $tree .= '<ul'.$ulAttr.'>'; }

              $count = count($datas);

              for ($i = 0, $ni = $count; $i < $ni; $i++) {

                  if (!array_find_val($datas, $this->rowId, $datas[$i][$this->parentId])) {
                      $datas[$i][$this->parentId] = 0;
                  }

                  if ($datas[$i][$this->parentId] == $parent) { 
                      $levelNum = $number.$index;
                      $tree .= '<li>'; 

                      $rowHtml = $this->rowHtml;
                      preg_match_all('/#([^\s]+)#/', $this->rowHtml, $matches);
                      foreach ($matches[1] as $val) {
                          if ($val == 'LEVEL_NUMBER') {
                              $rowHtml = str_replace('#LEVEL_NUMBER#', $levelNum, $rowHtml);
                          } elseif ($val == 'ROW_NAME') {
                              $rowHtml = str_replace('#ROW_NAME#', $datas[$i][$this->rowName], $rowHtml);
                          } elseif ($val == 'ROW_ID') {
                              $rowHtml = str_replace('#ROW_ID#', $datas[$i][$this->rowId], $rowHtml);
                          } else {
                              $rowHtml = str_replace('#'.$val.'#', $datas[$i][$val], $rowHtml);
                          }
                      }
                      if ($this->iconShowType == 'row_type') {
                          if (isset($datas[$i][$this->rowType])) {
                              $icon = (($datas[$i][$this->rowType]=='folder')?'folder':'file');
                          } else {
                              $icon = 'folder';
                          }
                      } else {
                          $icon = ((array_find_val($datas, $this->parentId, $datas[$i][$this->rowId]))?'folder':'file');
                      }
                      $tree .= '<span class="'.$icon.'">'.$rowHtml.'</span>';
                      $tree .= $this->simpleFolderView($datas, $datas[$i][$this->rowId], $depth + 1, $levelNum.'.', ((array_find_val($datas, $this->parentId, $datas[$i][$this->rowId]))?true:false));
                      $tree .= '</li>';
                      $index ++;
                  } 

              }

              if ($row_type) { $tree .= '</ul>'; }
          }
          return $tree;
    }

    /**
     * Simple Navigation Treeview
     * 
     * @param array $datas
     * @param type $parent
     * @param type $depth
     * @param type $number
     * @param type $row_type
     * @return string
     * 
     * @see assets/plugins/jquery-tree/jquery.treeview.css
     * @see assets/plugins/jquery-tree/jquery.treeview.js
     */
    public function simpleNavigation($datas, $parent = 0, $depth = 0, $number = null, $row_type = true)
    {
        $index = 1;
        $ulAttr = $tree = '';

        if ($datas != null) {

            if ($depth == 0) { $ulAttr = ' id="'.$this->treeId.'"'.(($this->themeClass!=null)?' class="'.$this->themeClass.'"':'').''; }
            if ($row_type) { $tree .= '<ul'.$ulAttr.'>'; }

            $count = count($datas);

            for ($i = 0, $ni = $count; $i < $ni; $i++) {

                if (!array_find_val($datas, $this->rowId, $datas[$i][$this->parentId])) {
                    $datas[$i][$this->parentId] = 0;
                }

                if ($datas[$i][$this->parentId] == $parent) { 
                    $levelNum = $number.$index;
                    $tree .= '<li>'; 

                    $rowHtml = $this->rowHtml;
                    preg_match_all('/#([^\s]+)#/', $this->rowHtml, $matches);
                    foreach ($matches[1] as $val) {
                        if ($val == 'LEVEL_NUMBER') {
                            $rowHtml = str_replace('#LEVEL_NUMBER#', $levelNum, $rowHtml);
                        } elseif ($val == 'ROW_NAME') {
                            $rowHtml = str_replace('#ROW_NAME#', $datas[$i][$this->rowName], $rowHtml);
                        } elseif ($val == 'ROW_ID') {
                            $rowHtml = str_replace('#ROW_ID#', $datas[$i][$this->rowId], $rowHtml);
                        } else {
                            $rowHtml = str_replace('#'.$val.'#', $datas[$i][$val], $rowHtml);
                        }
                    }
                    $tree .= $rowHtml;
                    $tree .= $this->simpleNavigation($datas, $datas[$i][$this->rowId], $depth + 1, $levelNum.'.', ((array_find_val($datas, $this->parentId, $datas[$i][$this->rowId]))?true:false));
                    $tree .= '</li>';
                    $index ++;
                } 

            }

            if ($row_type) { $tree .= '</ul>'; }
        }
        return $tree;
    }

    /**
    * Treeview
    * 
    * @param array $datas
    * @param type $parent
    * @param type $depth
    * @param type $number
    * @param type $row_type
    * @return string
    * 
    * @see assets/core/global/plugins/jstree/dist/themes/default/style.min.css
    * @see assets/core/global/plugins/jstree/dist/jstree.min.js
    */
    public function treeView($datas, $parent = 0, $depth = 0, $number = null, $row_type = true)
    {
        global $lang;

        $index = 1;
        $ulAttr = $tree = '';

        if ($datas != null) {

            if ($depth == 0) { $ulAttr = ' id="'.$this->treeId.'"'; }
            if ($row_type) { $tree .= '<ul'.$ulAttr.'>'; }

            $count = count($datas);

            for ($i = 0, $ni = $count; $i < $ni; $i++) {

                if (!array_find_val($datas, $this->rowId, $datas[$i][$this->parentId])) {
                    $datas[$i][$this->parentId] = 0;
                }

                if ($datas[$i][$this->parentId] == $parent) { 
                    $levelNum = $number.$index;
                    $customAttr = isset($datas[$i][$this->customAttr]) ? ' data-li-path="' . $datas[$i][$this->customAttr] . '"' : '';
                    $tree .= '<li' . $customAttr . '>'; 

                    $rowHtml = $this->rowHtml;
                    preg_match_all('`#([^#]*)#`', $this->rowHtml, $matches);
                    foreach ($matches[1] as $val) {
                        if ($val == 'LEVEL_NUMBER') {
                            $rowHtml = str_replace('#LEVEL_NUMBER#', $levelNum, $rowHtml);
                        } elseif ($val == 'ROW_NAME') {
                            $rowHtml = str_replace('#ROW_NAME#', $datas[$i][$this->rowName], $rowHtml);
                        } elseif ($val == 'ROW_ID') {
                            $rowHtml = str_replace('#ROW_ID#', $datas[$i][$this->rowId], $rowHtml);
                        } else {
                            $rowHtml = str_replace('#'.$val.'#', $lang->line($datas[$i][$val]), $rowHtml);
                        }
                    }
                    $tree .= $rowHtml;
                    $tree .= $this->treeView($datas, $datas[$i][$this->rowId], $depth + 1, $levelNum.'.', ((array_find_val($datas, $this->parentId, $datas[$i][$this->rowId]))?true:false));
                    $tree .= '</li>';
                    $index ++;
                } 

            }

            if ($row_type) { $tree .= '</ul>'; }
        }
        return $tree;
    }

}