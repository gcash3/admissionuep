<?php
class HTML {
    static function contentheader($title='Title', $subtitle='', $rightheader='') {
        $html  = "<section class='content-header'><h1>$title<small>$subtitle</small></h1>";
        $html .= $rightheader;
        $html .= "</section>";
        return $html;
    }
    
    static function pagetree() {
        global $APP_CURRENTPAGE, $APP_MODULES, $APP_CURRENTPAGEDETAILS, $APP_SESSION;
        $parent = getparentmenu($APP_CURRENTPAGE, '', $APP_MODULES);        
        $parent = getmenudetails($parent, $APP_MODULES);
        
        $rights = '';
        $pagerights = $APP_SESSION->getModuleAccess();
        if ($APP_CURRENTPAGEDETAILS[1]) {
            $accesslist = Tools::explodepagerights(@$APP_CURRENTPAGEDETAILS[6] . '', true);
            $colors['C'] = 'label-info';
            $colors['R'] = 'label-primary';
            $colors['U'] = 'label-success';
            $colors['D'] = 'label-danger';
            $colors['V'] = 'bg-maroon';
            $rights      = '';
            foreach ($accesslist as $rightscode => $rightsdescription) {
                $cursor = 'pointer';
                if (stripos($pagerights, $rightscode) === false) {
                    $class = 'bg-gray text-green';
                    $rightsdescription .= ' (No Access)';
                    $cursor = 'not-allowed';
                }
                else 
                    $class   = @$colors[$rightscode];
                if ($class == '')
                    $class = 'bg-maroon';
                $rights .= "<span style='cursor:$cursor' class='label $class' title='$rightsdescription' data-toggle='tooltip'>$rightscode</span> ";
            }
        }
                
        $html  = '';
        $html .= "<ol class='breadcrumb'>";
        $html .= "<li><a href='" . APP_BASE ."dashboard'><i class='fa fa-dashboard'></i> Home</a></li>";
        if (is_array($parent))
            $html .= "<li><a href='#'>$parent[2]</a></li>";
        $html .= "<li class='active'>$APP_CURRENTPAGEDETAILS[2]</li>";
        if ($rights)
            $html .= "<li>" . '<span class="label bg-black" title="Your current privilege on this page">' . HTML::icon('user-o'). '</span>&nbsp;' .  $rights . "</li>";
        $html .= "</ol>";
        return $html;
    }

    static function box($title='Title', $content='Content', $footer='',  $boxclass='info', $collapsable=true, $closable=false,$bodyclass='table-responsive',$bodyid='',$boxtools='') {
        $bodyid = $bodyid ? "id='$bodyid'" : '';
        $html = "<div class='box box-$boxclass'>";
        if ($title != null)  {
            $html .= "<div class='box-header with-border'><h3 class='box-title'>$title</h3><div class='box-tools pull-right'>";
            if ($boxtools)
                $html .= $boxtools;
            if ($collapsable)
                $html .= "<button type='button' class='btn btn-box-tool' data-widget='collapse' data-toggle='tooltip' title='Collapse'><i class='fa fa-minus'></i></button>";
            if ($closable)
                $html .= "<button type='button' class='btn btn-box-tool' data-widget='remove' data-toggle='tooltip' title='Remove'><i class='fa fa-times'></i></button>";
            $html .= "</div></div>";
        }
        $html .= "<div class='box-body $bodyclass' $bodyid>$content</div>";
        if ($footer)
            $html .= "<div class='box-footer'>$footer</div>";
        $html .= "</div>";
        return $html;
    }  

    static function callout($title, $message, $class='danger') {
        return "<div class='callout callout-$class'><h4>$title</h4><p>$message</p></div>";    
    }

    static function alert($title, $message, $class='danger', $closable=true, $messageid='') {
        $html = "<div class='alert alert-$class" . ($closable ? " alert-dismissible" : '') ."'>";
        if ($closable)
            $html .= "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
        if ($title)
            $html .= "<h4>$title</h4>";
        $html .= "<span id='$messageid'>$message</span>";
        $html .= "</div>";
        return $html;
    }

    static function _alert($title, $message, $class='danger') {
        return HTML::callout($title, $message, $class);
    }

    static function sidebarmenus($currentpage, $modules, $parent) {
        $html = '';
        foreach ($modules as $key => $module) {
            if (isset($module[4]) && is_array($module[4])) {
                $class = ($parent == $key) ? ' active' : '';
                /*
                echo "<li class=\"treeview$class\">";
                echo '<a href="#">';
                echo "<i class=\"{$module[3]}\"></i> <span>{$module[2]}</span>";
                echo "<i class=\"fa fa-angle-left pull-right\"></i>";
                echo "</a>";
                echo "<ul class=\"treeview-menu\">";
                HTML::sidebarmenus($currentpage, $module[4], $parent);
                echo "</ul>";
                echo "</li>";
                */

                $child = HTML::sidebarmenus($currentpage, $module[4], $parent);
                if ($child) {
                    $html .= "<li class=\"treeview$class\">";
                    $html .= '<a href="#">';
                    $html .= "<i class=\"{$module[3]}\"></i> <span>{$module[2]}</span>";
                    $html .= "<i class=\"fa fa-angle-left pull-right\"></i>";
                    $html .= "</a>";
                    $html .= "<ul class=\"treeview-menu\">";
                    $html .= $child;
                    $html .= "</ul>";
                    $html .= "</li>";                                    
                }
                
            }
            else {
                $html .= HTML::sidebarmenuitem($currentpage, $key, $module);
            }
        }
        return $html;
    }

   static function sidebarmenuitem($currentpage, $key, $module) {
        global $APP_SESSION;
        global $APP_DEVMODULES;
        if (!$module[0])
            return;
        $class = ($key == $currentpage) ? ' class = "active"' : '';
        $access = $APP_SESSION->getModuleAccess($key);
        $tooltip = @$module[5];
        $title = $tooltip ? " title='$tooltip'" : '';
        $target = @$module[8] && (@$module[8] != '_self') ? " target='{@$module[8]}'" : '';

        if (($access && $module[1]) || !$module[1] || !DEBUG_CHECKACCESS) {
            $base = APP_BASE;
            if ((substr($key,0,4) == 'http') || (stripos('./', substr($key,0,1)) !== false))
                $base = '';
            $href = "$base$key";
        }
        else
            return;
        $html = '';
        if ((in_array($key, $APP_DEVMODULES) == false ) || (!APP_PRODUCTION && !APP_DEMO)) {
            $html .= "<li$class>";
            $html .= "<a href='$href'$title$target>";
            $html .= "<i class=\"{$module[3]}\"></i> <span>{$module[2]}</span>";
            $html .= "</a>";
            $html .= "</li>";
        }
        return $html;
    }
    
    static function sidebarmenuitemwithaccess($currentpage, $key, $module) {
        global $APP_SESSION;
        if (!$module[0])
            return false;
        $access = $APP_SESSION->getModuleAccess($key);
        if (($access && $module[1]) || !$module[1] || !DEBUG_CHECKACCESS)
            return true;
        else
            return false;
    }
    

    static function text($name, $label, $value, $placeholder='', $required=false, $readonly=false, $disabled=false, $type='text', $class='form-control', $attributes='') {
        $required = $required ? ' required' : '';
        $readonly = $readonly ? ' readonly' : '';
        $disabled = $disabled ? ' disabled ' : '';
		$value = utf8_encode($value);
        if (substr($name,-2,2) != '[]')
            $id = "id='$name'";
        else
            $id = '';
        return "<input type='$type' class='$class' $id name='$name' placeholder='$placeholder' $disabled$readonly$required value='$value' $attributes>";        
    }
    
    static function textarea($name, $label, $value, $placeholder='', $required=false, $readonly=false, $disabled=false, $class='form-control') {
        $required = $required ? ' required' : '';
        $readonly = $readonly ? ' readonly' : '';
        $disabled = $disabled ? ' disabled ' : '';
        return "<textarea class='$class' id='$name' name='$name' placeholder='$placeholder' $disabled$readonly$required>$value</textarea>";        
    }
    

    static function hidden($name, $value) {
        return "<input type='hidden' id='$name' name='$name' value='$value'>";        
    }

    static function hforminputtext($name, $label, $value, $placeholder='', $required=false, $readonly=false, $disabled=false, $labelsize=2, $fieldsize=10, $type='text', $class='', $otherhtml='') {
        $html  = "<div class='form-group $class'>";
        $html .= HTML::labelinput($label, $labelsize, $name);
        $html .= $fieldsize ? "<div class='col-lg-$fieldsize'>" : '';
        $html .= HTML::text($name, $label, $value, $placeholder, $required, $readonly, $disabled, $type);
        $html .= $fieldsize ? "</div>" : '';
        $html .= "$otherhtml</div> ";
        return $html;
    }

    static function labelinput($label, $labelsize='', $for='') {
        $labelclass = $labelsize ? " class='col-lg-$labelsize" : '';
        $for = $for ? " for='$for'" : '';
        return $label ? "<label $for $labelclass control-label'>$label&nbsp;</label>" : '';
    }
    
    static function hforminputtextwithbutton($name, $label, $value, $placeholder='', $required=false, $readonly=false, $disabled=false, $labelsize=2, $fieldsize=3, $type='text', $class='', $otherhtml='', $button='<i class="fa fa-search"></i>') {
        $html  = "<div class='form-group $class'>";
        $html .= "<label for='$name' class='col-lg-$labelsize control-label'>$label</label>";
        $html .= "<div class='col-lg-$fieldsize'>";
        $html .= "<div class='input-group'>";
        $html .= HTML::text($name, $label, $value, $placeholder, $required, $readonly, $disabled, $type);
        $html .= "<div class='input-group-addon'>$button</div>";
        $html .= "</div>";
        $html .= "</div>";
        $html .= "$otherhtml</div> ";
        return $html;
    }    
    
    static function hformtextarea($name, $label, $value, $placeholder='', $required=false, $readonly=false, $disabled=false, $labelsize=2, $fieldsize=10, $type='text', $class='', $otherhtml='') {
        $html  = "<div class='form-group $class'>";
        $html .= HTML::labelinput($label, $labelsize, $name);
        $html .= $fieldsize ? "<div class='col-lg-$fieldsize'>" : '';
        $html .= HTML::textarea($name, $label, $value, $placeholder, $required, $readonly, $disabled);
        $html .= $fieldsize ? "</div>" : '';
        $html .= "$otherhtml</div> ";
        return $html;
    }    
    
    static function hforminputfile($name, $label, $filetypes='', $labelsize=2, $fieldsize=10, $class='', $otherhtml='') {
        if ($filetypes == 'msoffice') {
            $filetypes = 'application/msword,application/ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,.xltx application/vnd.openxmlformats-officedocument.spreadsheetml.template,application/vnd.openxmlformats-officedocument.presentationml.slideshow,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/pdf';
        }
        elseif ($filetypes == 'image') {
            $filetypes = 'image/bmp,image/jpeg,image/png';            
        }
        $html  = "<div class='form-group $class'>";
        $html .= HTML::labelinput($label, $labelsize, $name);
        $html .= $fieldsize ? "<div class='col-lg-$fieldsize'>" : '';
        $html .= "<input type='file' accept='$filetypes' name='$name' id='$name'>";
        $html .= $fieldsize ? "</div>" : '';
        $html .= "$otherhtml</div> ";
        return $html;
    }
    

    static function hformdiv($label, $div, $labelsize=2, $fieldsize=10, $class='', $id='', $contentid='') {
        $html  = "<div class='form-group $class' id='$id'>";
        $html .= HTML::labelinput($label, $labelsize);
        $html .= $fieldsize ? "<div class='col-lg-$fieldsize' id='$contentid'>" : '';
        $html .= $div;
        $html .= $fieldsize ? "</div>" : '';
        $html .= "</div> ";
        return $html;
    }
    
    static function hforminputdate($name, $label, $value, $placeholder='', $required=false, $readonly=false, $disabled=false, $labelsize=2, $fieldsize=10, $otherhtml='') {
        return HTML::hforminputtext($name, $label, $value, $placeholder, $required, $readonly, $disabled, $labelsize, $fieldsize, 'text', 'date', $otherhtml);
    }

    static function hformpassword($name, $label, $value, $placeholder='', $required=false, $readonly=false, $disabled=false, $labelsize=2, $fieldsize=10, $otherhtml='') {
        return HTML::hforminputtext($name, $label, $value, $placeholder, $required, $readonly, $disabled, $labelsize, $fieldsize, 'password', '', $otherhtml);
    }

    static function hformemail($name, $label, $value, $placeholder='', $required=false, $readonly=false, $disabled=false, $labelsize=2, $fieldsize=10, $otherhtml='') {
        return HTML::hforminputtext($name, $label, $value, $placeholder, $required, $readonly, $disabled, $labelsize, $fieldsize, 'email', $otherhtml);
    }

    static function hformnumber($name, $label, $value, $placeholder='', $required=false, $readonly=false, $disabled=false, $labelsize=2, $fieldsize=10, $otherhtml='') {
        return HTML::hforminputtext($name, $label, $value, $placeholder, $required, $readonly, $disabled, $labelsize, $fieldsize, 'number', $otherhtml);
    }
    
    static function hformurl($name, $label, $value, $placeholder='', $required=false, $readonly=false, $disabled=false, $labelsize=2, $fieldsize=10, $otherhtml='') {
        return HTML::hforminputtext($name, $label, $value, $placeholder, $required, $readonly, $disabled, $labelsize, $fieldsize, 'url', $otherhtml);
    }

    static function selectoptions($value, $arraysource, $withblank=true, $blanklabel=' ') {
        $html = '';
        if ($withblank)
            $html .= "<option value=''>$blanklabel</option>";
        $first = current($arraysource);
        $withgrouping = is_array($first) && @$first[0];
        if ($withgrouping) {
            $currentgroup = $first[1];
            $html .= '<optgroup label="' . $first[1] . '">';
        }
        foreach ($arraysource as $index => $element) {
            if (is_array($value))
                $selected = (in_array($index, $value)) ? 'selected' : '';    
            else {
                $selected = ($value == $index) && ($value != '') ? 'selected' : '';
            }
            $optgroup = '';
            $datatag = '';
            if ($withgrouping) {
                if (is_array($element)) {
                    $option = $element[0];
                    $optgroup = @$element[1];
                    if ($optgroup != $currentgroup) {
                        $html .= '</optgroup><optgroup label="' . $optgroup . '">';
                    }
                    $currentgroup = $optgroup;
                    foreach ($element as $dataname => $datavalue) {
                        if (substr($dataname,0,5) == 'data-')
                            $datatag .= "$dataname='$datavalue' ";
                    }
                }
            }
            else {
                if (is_array($element)) {
                    $option = $element[1];
                    foreach ($element as $dataname => $datavalue) {
                        if (substr($dataname,0,5) == 'data-')
                            $datatag .= "$dataname='$datavalue' ";
                    }
                }
                else {
                    $option = $element;
                }
            }
            $html .= "<option value='$index' $selected $datatag>$option</option>";
        }
        if ($withgrouping) 
            $html .= '</optgroup>';
        return $html;
    }

    static function select($name, $value, $arraysource, $required=false, $readonly=false, $disabled=false, $class='form-control', $style='', $size=0) {
        $required = $required ? ' required' : '';
        $readonly = $readonly ? ' readonly' : '';
        $disabled = $disabled  || $readonly ? ' disabled ' : '';
        $size = $size ? " size=$size" : '';
        if (substr($name,-2,2) != '[]')
            $id = "id='$name'";
        else
            $id = "id='" . str_replace('[]','', $name) . mt_rand() . "'";
        $html = "<select class='$class' $id name='$name' $disabled$required style='$style' $size>";
        $html .= HTML::selectoptions($value, $arraysource);
        $html .= '</select>';        
        return $html;
    }
    
    static function hformselect($name, $label, $value, $arraysource, $required=false, $readonly=false, $disabled=false, $labelsize=2, $fieldsize=10, $class='',$otherhtml='', $style='', $size=0) {
        $html  = "<div class='form-group $class'>";
        $html .= HTML::labelinput($label, $labelsize, $name);
        $html .= $fieldsize ? "<div class='col-lg-$fieldsize'>" : '';
        $html .= HTML::select($name, $value, $arraysource, $required, $readonly, $disabled, "form-control $class", $style, $size);
        $html .= $fieldsize ? "</div>" : '';
        $html .= $otherhtml;
        $html .= "</div> ";
        return $html;
        }
        
    static function hformlistbox($name, $label, $value, $arraysource, $required=false, $readonly=false, $disabled=false, $labelsize=2, $fieldsize=10, $class='',$otherhtml='') {
        $html  = "<div class='form-group $class'>";
        $html .= HTML::labelinput($label, $labelsize, $name);
        $html .= $fieldsize ? "<div class='col-lg-$fieldsize'>" : '';
        $html .= HTML::select($name, $value, $arraysource, $required, $readonly, $disabled, "form-control $class",'', 5);
        $html .= $fieldsize ? "</div>" : '';
        $html .= $otherhtml;
        $html .= "</div> ";
        return $html;
        }        
        
    static function hformselect2($name, $label, $value, $arraysource, $required=false, $readonly=false, $disabled=false, $labelsize=2, $fieldsize=10, $class='',$otherhtml='') {
        $html  = "<div class='form-group $class'>";
        $html .= "<label for='$name' class='col-lg-$labelsize control-label'>$label</label>";
        $html .= "<div class='col-lg-$fieldsize'>";
        $html .= HTML::select($name, $value, $arraysource, $required, $readonly, $disabled, "form-control select2 $class", 'width:100%');
        $html .= "</div>";
        $html .= $otherhtml;
        $html .= "</div> ";
        return $html;
        }
        

    static function selectmultiple($name, $value, $arraysource, $required=false, $readonly=false, $disabled=false, $class='form-control', $style='') {
        $required = $required ? ' required' : '';
        $readonly = $readonly ? ' readonly' : '';
        $disabled = $disabled  || $readonly ? ' disabled ' : '';
        $html = "<select class='$class' id='$name' name='{$name}[]' $disabled$required multiple='multiple' style='$style'>";
        $html .= HTML::selectoptions($value, $arraysource);
        $html .= '</select>';        
        return $html;
    }    

    static function hformselectmultiple($name, $label, $value, $arraysource, $required=false, $readonly=false, $disabled=false, $labelsize=2, $fieldsize=10, $class='',$otherhtml='') {
        $html  = "<div class='form-group $class'>";
        $html .= HTML::labelinput($label, $labelsize, $name);
        $html .= $fieldsize ? "<div class='col-lg-$fieldsize'>" : '';
        $html .= HTML::selectmultiple($name, $value, $arraysource, $required, $readonly, $disabled, "form-control $class");
        $html .= $fieldsize ? "</div>" : '';
        $html .= $otherhtml;
        $html .= "</div> ";
        return $html;
        }        

    static function hformselect2multiple($name, $label, $value, $arraysource, $required=false, $readonly=false, $disabled=false, $labelsize=2, $fieldsize=10, $class='',$otherhtml='') {
        $html  = "<div class='form-group $class'>";
        $html .= HTML::labelinput($label, $labelsize, $name);
        $html .= $fieldsize ? "<div class='col-lg-$fieldsize'>" : '';
        $html .= HTML::selectmultiple($name, $value, $arraysource, $required, $readonly, $disabled, "form-control select2 $class",'width:100%');
        $html .= $fieldsize ? "</div>" : '';
        $html .= $otherhtml;
        $html .= "</div> ";
        return $html;
        }        
        
        

    static function hformdatepicker($name, $label, $value, $placeholder='', $required=false, $readonly=false, $disabled=false, $labelsize=2, $fieldsize=3, $type='text', $class='date') {
        $required = $required ? ' required' : '';
        $readonly = $readonly ? ' readonly' : '';
        $disabled = $disabled  || $readonly ? ' disabled ' : '';
        $html  = "<div class='form-group $class'>";
        $html .= HTML::labelinput($label, $labelsize, $name);
        $html .= $fieldsize ? "<div class='col-lg-$fieldsize'>" : '';
        $html .= '<div class="input-group date" data-provide="datepicker">';
        $html .= "<input type='$type' class='form-control datepicker' id='$name' name='$name' placeholder='$placeholder' $disabled$required value='$value'>";
        $html .= '<div class="input-group-addon"><i class="fa fa-calendar"></i></div>';
        $html .= "</div>";
        $html .= $fieldsize ? "</div>" : '';
        $html .= "</div> ";
        return $html;
    }

    static function hformradio($name, $label, $checked, $readonly=false, $disabled=false, $labelsize=2, $fieldsize=10, $leftlabel='', $value='on') {
        if (!is_array($name)) {
            $names     = array($name);
            $labels    = array($label);
            $checkeds  = array($checked);
            $readonlys = array($readonly);
            $disableds = array($disabled);
            $values    = array($value);
        }    
        else {
            
            foreach ($name as $index => $element) {
                $names[]     = @$name[$index];
                $labels[]    = @$label[$index];
                $checkeds[]  = @$checked[$index] == true;
                $values[]    = @$value[$index];
                if (is_array($readonly))
                    $readonlys[] = @$readonly[$index] == true;
                else
                    $readonlys[] = $readonly == true;
                if (is_array($disabled))
                    $disableds[] = @$disabled[$index] == true;
                else
                    $disableds[] = @$disabled == true;
            }
        }
        $inline = count($names) > 1 ? 'radio-inline' : '';
        
        $html  = "<div class='form-group'>";
        if ($leftlabel) {
            $html .= HTML::labelinput($leftlabel, $labelsize);
            $html .= $fieldsize ? "<div class='col-lg-$fieldsize'>" : '';
        }
        else
            $html .= $fieldsize ? "<div class='col-lg-offset-$labelsize col-lg-$fieldsize'>" : '';
        $html .= "<div class='checkbox'>";
        foreach ($names as $i => $name) {
            $disabled = $readonlys[$i] || $disableds[$i] ? ' disabled ' : '';
            $checkedattr  = $checkeds[$i]    ? ' checked'   : '';
            $html .= "<label class='$inline'>";
            $html .= "<input type='checkbox' name='$names[$i]' id='$names[$i]' $disabled$checkedattr value='$values[$i]'> $labels[$i]";
            $html .= "</label>";
        }
        $html .= "</div>";
        $html .= $fieldsize ? "</div>" : '';
        $html .= "</div>";
        return $html;
    }    

    static function hformradio2($name, $label, $checked, $readonly=false, $disabled=false, $labelsize=2, $fieldsize=10, $leftlabel='', $value='on') {
        if (!is_array($name)) {
            $names     = array($name);
            $labels    = array($label);
            $checkeds  = array($checked);
            $readonlys = array($readonly);
            $disableds = array($disabled);
            $values    = array($value);
        }    
        else {
            
            foreach ($name as $index => $element) {
                $names[]     = @$name[$index];
                $labels[]    = @$label[$index];
                $checkeds[]  = @$checked[$index] == true;
                $values[]    = @$value[$index];
                if (is_array($readonly))
                    $readonlys[] = @$readonly[$index] == true;
                else
                    $readonlys[] = $readonly == true;
                if (is_array($disabled))
                    $disableds[] = @$disabled[$index] == true;
                else
                    $disableds[] = @$disabled == true;
            }
        }
        $inline = count($names) > 1 ? 'radio-inline' : '';
        
        $html  = "<div class='form-group'>";
        if ($leftlabel) {
            $html .= HTML::labelinput($leftlabel, $labelsize);
            $html .= $fieldsize ? "<div class='col-lg-$fieldsize'>" : '';
        }
        else
            $html .= $fieldsize ? "<div class='col-lg-offset-$labelsize col-lg-$fieldsize'>" : '';
        $html .= "<div class='checkbox'>";
        foreach ($names as $i => $name) {
            $disabled = $readonlys[$i] || $disableds[$i] ? ' disabled ' : '';
            $checkedattr  = $checkeds[$i]    ? ' checked'   : '';
            $html .= "<label class='$inline'>";
            $html .= "<input type='radio' name='$names[$i]' id='$names[$i]' $disabled$checkedattr value='$values[$i]'> $labels[$i]";
            $html .= "</label>";
        }
        $html .= "</div>";
        $html .= $fieldsize ? "</div>" : '';
        $html .= "</div>";
        return $html;
    }    

    static function button($name, $text, $class='default', $type='button', $formnovalidate=false, $disabled=false, $dataconfirmation='') {
        $formnovalidate = $formnovalidate ? 'formnovalidate' : '';
        $disabled = $disabled ? 'disabled' : '';
        $dataconfirmation = $dataconfirmation ? "data-confirmation='$dataconfirmation'" : '';
        if (@$class[0] != ' ')
            $class = "btn-$class";
        return " <button class='btn $class no-print' type='$type' id='$name' name='$name' $formnovalidate $disabled $dataconfirmation>$text</button> ";
    }
    
    static function submitbutton($name, $text, $class='primary', $disabled=false, $dataconfirmation='', $formnovalidate=false) {
        return HTML::button($name, $text, $class, 'submit', $formnovalidate, $disabled, $dataconfirmation);
    }

    static function resetbutton($name, $text, $class='default') {
        return HTML::button($name, $text, $class, 'reset');
    }

    static function cancelbutton($name='Cancel', $text='Cancel', $class='warning') {
        return HTML::button($name, $text, $class, 'submit', true);
    }
    
    static function link($href, $text, $title='', $class='', $id='', $target='') {
        if ($href != '#')
            $href = ($href == null) ? '#' : (substr($href,0,11) != 'javascript:' && substr($href,0,4) != 'http' ? APP_BASE : '') . $href;
        $target = $target ? " target='$target'" : '';
        $id = $id ? " id='$id'" : '';
        return "<a href='$href' class='$class' title='$title'$id$target>$text</a> ";
    }

    static function linkbutton($href, $text, $class='default', $id='', $target='', $title='') {
        return HTML::link($href, $text, $title, "btn btn-$class no-print", $id, $target);
    }

    static function alert_inprogress() {
        return HTML::callout('<i class="fa fa-meh-o"></i> Sorry!', 'This page is not yet implemented!','info');
    }

    static function tab($title, $id, $tabtitles, $tabcontents, $activetab=0) {
        $html = "<div class='nav-tabs-custom'>";
        $html .= "<ul class='nav nav-tabs pull-right'>";
        foreach ($tabtitles as $index => $tabtitle) {
            $active = $index==$activetab ? ' class="active"' : '';
            $html .= "<li$active><a href='#$id$index' data-toggle='tab'>$tabtitle</a></li>";
        }
        $html .= "<li class='pull-left header'>$title</li>";
        $html .= "</ul>";
        $html .= "<div class='tab-content'>";
        foreach ($tabcontents as $index => $tabcontent) {
            $active = $index==$activetab ? 'fade in active' : 'fade';
            $html .= "<div class='tab-pane $active' id='$id$index'>$tabcontent</div>";
        }
        $html .= "</div></div>";
        return $html;
    }

    // to activate DataTable plugin ( > 10 records only)
    // $('.dataTable').DataTable();
    // Columns and data format
    // $columns[<fieldname>] = <header>
    // $columns[<fieldname>] = array(<header>, <datatype>, <format>)
    // $columns[<fieldname>] = array(<header>, array(<class>,<callback function>), <format>)
    // $data[<recordnumber>] = array(<fieldname> => <value>, ...)
    static function datatable($name, $columns, $data, $class='', $attributes='', $withrownumber=true, $footers=array(), $columns0=array()) {
        if (!is_array($data))
            $data = array();
        if (!is_array($footers))            
            $footers = array();
        if (!is_array($columns0))            
            $columns0 = array();
        $datatableclass = count($data) > 0 ? 'dataTable' : '';
        $html = "<table class='table table-striped table-bordered table-hover $datatableclass $class' id='$name' role='grid' $attributes>";
        $html .= '<thead>';
        if ($columns0) {
            foreach ($columns0 as $label) {
                $columnclass = '';
                $colspan = '';
                if (is_array($label)) {
                    if (@$label['columnclass'])
                        $columnclass = " class='".$label['columnclass']."'";
                    if (@$label['colspan'])
                        $colspan = " colspan='".$label['colspan']."'";
                        
                }
                $label = is_array($label) ?  @$label[0] : $label;
                $html .= "<th$columnclass$colspan>$label</th>";
            }
        }
        $html .= '<tr>';
        if ($withrownumber)
            $html .= "<th>#</th>";
        foreach ($columns as $label) {
            $columnclass = '';
            if (is_array($label)) {
                if (@$label['columnclass'])
                    $columnclass = " class='".$label['columnclass']."'";
            }
            $label = is_array($label) ?  @$label[0] : $label;
            $html .= "<th$columnclass>$label</th>";
        }
        $html .= '</tr>';
        $html .= '</thead><tbody>';
        $i=1;
        foreach ($data as $record) {
            $rowclass = isset($record['rowclass']) ? " class='".$record['rowclass']."'" : '';
            $html .= "<tr$rowclass>";
            if ($withrownumber)
                $html .= "<td>$i.</td>";
            foreach ($columns as $key => $label) {
                $columnclass = '';
                if (isset($record[$key])) {
                    $value = $record[$key];
                    if (is_array($label)) {
                        if (@$label[1]) {
                            if ($label[1] == 'date') {
                                if (strtotime($value) !== false)
                                    $value = Data::formatdate($value, $label[2]);
                            }
                            elseif ($label[1] == 'sprintf')
                                $value = sprintf($label[2], $value);
                            elseif ($label[1] == 'yesno') {
                                if (!is_array($label[2]))
                                    $label[2] = array('Yes','No');                            
                                $value = $value ? $label[2][0] : $label[2][1];
                            }
                            elseif ($label[1] == 'money')
                                $value = number_format($value,2);
                            elseif ($label[1] == 'int')
                                $value = number_format($value,0);
                            elseif ($label[1] == 'string')
                                $value = str_replace('@0', $value, $label[2]);
                            elseif ($label[1] == 'list') 
                                $value = @$label[2][$value];
                            elseif (is_array($label[1]) && is_callable($label[1],true)) {
                                $fx = $label[1];
                                // TODO: Call static method Class::Method
                                //$nv = $fx($value);
                                if (!isset($label[2]))
                                    $label[2] = '';
                                $nv = call_user_func($fx, $value, $record);
                                $value = str_replace('[@0]', $value, $label[2]);
                                $value = str_replace('[@1]', $nv, $value);
                                if ($label[2] == '')
                                    $value = $nv;
                            }
                        }
                        if (@$label['columnclass'])
                            $columnclass = " class='".$label['columnclass']."'";
                    }
                }
                else
                    $value = '&nbsp;';
            
                $value = utf8_encode($value);
                $html .= "<td$columnclass>$value</td>";
            }
            $html .= '</tr>';
            $i++;
        }
        $html .= '</tbody>';
        if ($footers) {
            $html .= '<tfoot><tr>';
            if ($withrownumber)
                $html .= "<th>&nbsp;</th>";
            foreach ($footers as $label) {
                $columnclass = '';
                if (is_array($label)) {
                    if (@$label['columnclass'])
                        $columnclass = " class='".$label['columnclass']."'";
                }
                $label = is_array($label) ?  @$label[0] : $label;
                $html .= "<th$columnclass>$label</th>";
            }
            $html .= '</tr></tfoot>';
            
        }
        $html .= '</table>';
        return $html;
    }

    static function icon($icon, $text='', $collection='fa', $title='') {
        if ($collection == '')
            $collection = 'fa'; // default fontawesome collection
        return "<i class='$collection $collection-$icon' title='$title'></i> $text";
    }
    
    static function label($text, $class='primary', $classtype='label') {
        return "<span class='label $classtype-$class'>$text</span>";
    }

    static function badge($text, $color='bg-red', $title='') {
        return "<span class='badge $color' title='$title'>$text</span>";
    }    
    
    static function timelineopeningtag($class='') {
        return "<ul class='timeline $class'>";
    }
    
    static function timelineclosingtag($icon='fa fa-clock-o') {
        return "<li><i class='$icon'></i></li></ul>";

    }
    
    static function timelinetimelabel($label, $color='bg-red') {
        return "<li class='time-label'><span class='$color'>$label</span></li>";
    }
    
    static function timelineitem($icon, $header, $body='', $headertext='', $time='', $footer='', $timetooltip='') {
        $html = "<li>";
        $html .= "<i class='$icon'></i>";
        $html .= "<div class='timeline-item'>";;
        $html .= "<span class='time' title='$timetooltip'><i class='fa fa-clock-o'></i> $time</span>";
        $html .= "<h3 class='timeline-header'><a href='#'>$header</a> $headertext</h3>";
        if ($body) {
            $html .= "<div class='timeline-body'>$body</div>";
            if ($footer)
                $html .= "<div class='timeline-footer'>$footer</div>";
        }
        $html .= "</div>";
        $html .= "</li>";  
        return $html;
    }
    
    static function windowclose() {
        ob_clean();
        return '<script>window.close();</script>';
    }
    
    static function smallbox($header, $text, $url, $color, $icon, $footer='More info', $class='col-lg-3', $class2='col-xs-6') {
        $url = HTML::link($url,"$footer " . HTML::icon('arrow-circle-right'),'', 'small-box-footer');
        $html  = "<div class='$class $class2'>";
        $html .= "<div class='small-box $color'>";
        $html .= "<div class='inner'><h3>$header</h3><p>$text</p></div>";
        $html .= "<div class='icon'><i class='$icon'></i></div>";
        //$html .= "<a href='$url' class='small-box-footer'>$footer <i class='fa fa-arrow-circle-right'></i></a>";
        $html .= $url;
        $html .= "</div></div>";
        return $html;
    }
    
    static function filedownloaderlink($serverfilename, $actualfilename, $title='File Attachment', $size='3x', $target='_blank') {
        $ext            = pathinfo($actualfilename, PATHINFO_EXTENSION);
        $filetype       = HTML::filetypeicon($ext);

        $cs             = Crypto::DLchecksum($serverfilename, $actualfilename);
        $rnd            = mt_rand();
        $serverfilename = base64_encode($serverfilename);
        $actualfilename = base64_encode($actualfilename);
        $href           = APP_BASE . "downloader/$serverfilename/$actualfilename/$cs/$rnd/plain" ;
        
        return "<a href='$href' title='$title' data-toggle='tooltip' target=$target><i class='$filetype fa-$size'></i></a>&nbsp;&nbsp;";
    }

    static function filedownloaderbutton($serverfilename, $actualfilename, $title='File Attachment', $target='_blank') {
        $ext            = pathinfo($actualfilename, PATHINFO_EXTENSION);
        $filetype       = HTML::filetypeicon($ext);

        $cs             = Crypto::DLchecksum($serverfilename, $actualfilename);
        $rnd            = mt_rand();
        $serverfilename = base64_encode($serverfilename);
        $actualfilename = base64_encode($actualfilename);
        $href           = APP_BASE . "downloader/$serverfilename/$actualfilename/$cs/$rnd/plain" ;
        
        return "<a href='$href' title='$title' data-toggle='tooltip' target=$target class='btn btn-app'><i class='$filetype'></i>$ext</a>";
    }
    
    
    static function filetypeicon($ext) {
        $icons['pdf']   = 'fa fa-file-pdf-o';
        $icons['doc']   = 'fa fa-file-word-o';
        $icons['xls']   = 'fa fa-file-excel-o';
        $icons['ppt']   = 'fa fa-file-powerpoint-o';
        $icons['docx']  = 'fa fa-file-word-o';
        $icons['xlsx']  = 'fa fa-file-excel-o';
        $icons['pptx']  = 'fa fa-file-powerpoint-o';
        $icons['png']   = 'fa fa-file-image-o';
        $icons['jpg']   = 'fa fa-file-image-o';
        $icons['bmp']   = 'fa fa-file-image-o';
        $icons['jpeg']  = 'fa fa-file-image-o';
        $filetype = @$icons[$ext];
        if ($filetype == '')
            $filetype = 'fa fa-file';
        return $filetype;
    }
    
    static function modal($id, $title='title', $body='body', $footer='', $class='info') {
        $html  = "<div class='modal modal-$class fade' id='$id'>";
        $html .= "  <div class='modal-dialog modal-lg'>";
        $html .= "    <div class='modal-content'>";
        $html .= "      <div class='modal-header'>";
        $html .= "        <button type='button' class='close' data-dismiss='modal' aria-label='Close'>";
        $html .= "          <span aria-hidden='true'>&times;</span></button>";
        $html .= "        <h4 class='modal-title'>$title</h4>";
        $html .= "      </div>";
        $html .= "      <div class='modal-body' id='modal-body-$id'>";
        $html .= "        <p>$body</p>";
        $html .= "      </div>";
        if ($footer !== null) {
            $html .= "      <div class='modal-footer'>";
            $html .= "        <button type='button' class='btn btn-outline pull-left' data-dismiss='modal'>Close</button>";
            $html .= "        $footer";
            $html .= "      </div>";
        }
        $html .= "    </div>";
        $html .= "  </div>";
        $html .= "</div>";
        return $html;
    }

    static function optionshtml($values, $selectedvalue='') {
        $html = '';
        foreach ($values as $key => $value) {
            $sel = (($key == $selectedvalue) & ($selectedvalue != 'xxx')) ? ' selected=selected' : '';
            $html .= "<option value=\"$key\"{$sel}>$value</option>";
            if ($sel != '')
                $selectedvalue = 'xxx';
        }
        return $html;
    }

    static function boxtool($id, $label, $tooltip, $class='', $data='') {
        return "<button type='button' class='btn btn-box-tool $class' data-toggle='tooltip' title='$tooltip' id='$id' $data>$label</button>";
    }
    
    static function searchbox($id='searchtext',$value='',$minlength=2,$maxlength=20,$label='<i class="fa fa-search"></i>', $divclass='', $searchtexttitle='', $method='post',$buttontype='submit') {
        if ($divclass == '')
            $divclass = 'col-lg-6';
        if ($minlength == '')
            $minlength = 2;
        if ($maxlength == '')
            $maxlength = 20;
        if ($label == '')
            $label = "<i class='fa fa-search'></i>";
        if ($searchtexttitle)
            $searchtexttitle = "<span class='input-group-addon'>$searchtexttitle</span>";   
        return "<form method='$method'><div class='row'><div class='$divclass'><div class='input-group noprint'>$searchtexttitle<input id='$id' name='$id' value='$value' type='text' minlength='$minlength' maxlength='$maxlength' class='form-control' placeholder='Search text...'>
        <span class='input-group-btn'><button type='$buttontype' class='btn btn-default btn-flat' data-confirmation='none' title='Search'>$label</button></span>
        <span class='input-group-btn'><button type='button' class='btn btn-default btn-flat' data-confirmation='none' title='Clear Search Text' onclick='$(\"#$id\").val(\"\").focus()'><i class='fa fa-remove'></i></button></span>
        </div></div></div></form><br>";
    }

    static function selectbox($id='selectbox',$value='',$arraysource, $multiple=false, $selectclass='form-control', $label='<i class="fa fa-play"></i>', $divclass='', $searchtexttitle='Select', $buttontype='submit') {
        if ($divclass == '')
            $divclass = 'col-lg-6';
        if ($label == '')
            $label = "<i class='fa fa-play'></i>";
        if ($searchtexttitle)
            $searchtexttitle = "<span class='input-group-addon' id='{id}_label'>$searchtexttitle</span>";   
        if ($multiple)
            $selecthtml = HTML::selectmultiple($id, $value, $arraysource,false,false,false,"$selectclass");
        else
            $selecthtml = HTML::select($id, $value, $arraysource,false,false,false,"$selectclass");

        $applybutton = "<span class='input-group-btn'><button type='$buttontype' id='{$id}_apply' class='btn btn-default btn-flat' data-confirmation='none' title='Apply'>$label</button></span>";
        return "<div class='$divclass' id='{$id}_div'><div class='input-group noprint'>$searchtexttitle $applybutton $selecthtml</div></div>";
    }

    static function searchboxselect($id='searchtext',$value='',$arraysource='',$label='<i class="fa fa-search"></i>', $divclass='', $searchtexttitle='', $method='post',$buttontype='submit') {
        if ($divclass == '')
            $divclass = 'col-lg-6';
        if ($label == '')
            $label = "<i class='fa fa-search'></i>";
        if ($searchtexttitle)
            $searchtexttitle = "<span class='input-group-addon'>$searchtexttitle</span>";   
        $selectoptions = HTML::selectoptions($value, $arraysource);            
        return "<form method='$method'><div class='row'><div class='$divclass'><div class='input-group noprint'>$searchtexttitle<select id='$id' name='$id' class='form-control' onchange='this.form.submit()'>$selectoptions</select>
        </div></div></div></form><br>";
    }    

    static function searchboxnoform($id='searchtext',$value='',$minlength=2,$maxlength=20,$label='<i class="fa fa-search"></i>', $divclass='', $searchtexttitle='', $method='post',$buttontype='submit') {
        if ($divclass == '')
            $divclass = 'col-lg-6';
        if ($minlength == '')
            $minlength = 2;
        if ($maxlength == '')
            $maxlength = 20;
        if ($label == '')
            $label = "<i class='fa fa-search'></i>";
        if ($searchtexttitle)
            $searchtexttitle = "<span class='input-group-addon'>$searchtexttitle</span>";   
        return "<div class='$divclass'><div class='input-group noprint'>$searchtexttitle<input id='$id' name='$id' value='$value' type='text' minlength='$minlength' maxlength='$maxlength' class='form-control' placeholder='Search text...'>
        <span class='input-group-btn'><button type='$buttontype' class='btn btn-default btn-flat' data-confirmation='none' title='Search'>$label</button></span>
        <span class='input-group-btn'><button type='button' class='btn btn-default btn-flat' data-confirmation='none' title='Clear Search Text' onclick='$(\"#$id\").val(\"\").focus()'><i class='fa fa-remove'></i></button></span>
        </div></div>";
    }    
	
	static function exportcsvbutton($tableid, $csvtitle='', $text='', $id='', $class='') {
		if ($id == '')
			$id = mt_rand();
		if ($csvtitle == '')
			$csvtitle = 'Exported_Table';
		if ($text == '')
			$text = 'Export as CSV';
		if ($class == '')
			$class = 'primary';
				
		return "<a href='#' class='btn btn-$class exportcsv' id='$id' data-tableid='$tableid' data-title='$csvtitle'>$text</a>";
	}
    
    static function jsbuttonaddon($elementid, $title, $icon, $spanid='', $iconid='') {
        if ($spanid == '')
            $spanid = "addon_$elementid";
        if ($icon == '')
            $icon = "fa fa-ellipsis-h";
        if ($iconid = '')
            $iconid = "icon_$elementid";
        $js = "\$('#$elementid').wrap('<div class=\"input-group wrap_$elementid\"></div>');";
        $js .= "\$('.wrap_$elementid').append('<span class=\"input-group-btn\" id=\"$spanid\" title=\"$title\"><button class=\"btn btn-default\" type=\"button\"><i class=\"$icon\" id=\"$iconid\"></i></button></span>');";
        return $js;
    }
    
    static function accordion($panels, $id='') {
        if ($id == '')
            $id = 'accordion_' . mt_rand();
        $html = "<div class='panel-group' id='$id' role='tablist' aria-multiselectable='true'>";
        $r = 0;
        foreach ($panels as $key => $panel) {
            if (!is_array($panel))
                $panel = array("Panel$key",$panel);
                
            $html .= "<div class='panel panel-default'>
                      <div class='panel-heading' role='tab' id='{$id}_heading_$key'>
                      <h4 class='panel-title'>
                      <a role='button' data-toggle='collapse' data-parent='#$id' href='#{$id}_collapse_$key' aria-expanded='true' aria-controls='{$id}_collapse_$id'>
                      {$panel[0]}</a></h4></div>";
            $in = $r ? '' : 'in';
            $html .= "<div id='{$id}_collapse_$key' class='panel-collapse collapse $in' role='tabpanel' aria-labelledby='{$id}_heading_$key' data-row='$r'>
                      <div class='panel-body' data-row='$r'>{$panel[1]}</div></div></div>";
            $r++;
        }
        $html .= '</div>';
        return $html;
    }

    static function applicationbox($title='Title', $content='Content', $footer='',  $boxclass='info', $collapsable=true, $closable=false,$bodyclass='',$icon='th') {
        $html = "<div class='box box-$boxclass'>";
        if ($title != null)  {
            $html .= "<div class='box-header with-border'><i class='fa fa-$icon'></i><h3 class='box-title'>$title</h3><div class='box-tools pull-right'>";
            if ($collapsable)
                $html .= "<button type='button' class='btn btn-box-tool' data-widget='collapse' data-toggle='tooltip' title='Collapse'><i class='fa fa-minus'></i></button>";
                if ($closable)
                $html .= "<button type='button' class='btn btn-box-tool' data-widget='remove' data-toggle='tooltip' title='Remove'><i class='fa fa-times'></i></button>";
                $html .= "</div></div>";
        }
        $html .= "<div class='box-body $bodyclass'>";
        $html .= "<div id='appicons'><ul class='bs-appicons'>$content</ul></div>";
        $html .= "</div>";
        if ($footer)
            $html .= "<div class='box-footer'>$footer</div>";
        $html .= "</div>";
        return $html;
    }      

    static function appImagelink($title, $text, $url, $icon) {        
        $html = "<li>";
        $html .= "<a href='$url'>";
        $html .= "<div class='appimage'><img src='ap_img/".$icon."' class='img-responsive' alt='Responsive image'  /></div>";
        $html .= "<span class='appicon-class'><h4>$title</h4>$text</span>";
        $html .= "</a></li>";
        return $html;    
    }    
	
    
}

?>
