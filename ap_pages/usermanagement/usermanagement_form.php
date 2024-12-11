<?php
$activerecord = array();
if (count($_POST))
    $activerecord = $_POST;
else {
    $currentaccess = $APP_DBCONNECTION->execute("Usp_AP_GetAccessList '$currentemployeecode', 0, '" . APP_MODULENAME . "'");
    if (Tools::emptydataset($currentaccess))
        $currentaccess = array();
    foreach ($currentaccess as $rights) {
        for ($i=0; $i<strlen($rights['Rights']); $i++) {
            $rightscode = substr($rights['Rights'], $i, 1);
            if ($rightscode == 'A')
                $rightscode = 'C';
            elseif ($rightscode == 'E')
                $rightscode = 'U';
            $activerecord[$rights['SubModule'] . '__' . $rightscode] = 1;
        }
    }
}
    
$results = array();
$disabled = ($APP_SESSION->getCanUpdate() || $APP_SESSION->getCanCreate() || $APP_SESSION->getCanDelete()) ? false : true;
buildaccesstable($activerecord, $APP_MODULES, $results, 0, $disabled);

$columns['PageCode']       = 'Page Code';
$columns['Description']    = 'Description';
$columns['CurrentRights']  = 'Current Rights';
$columns['ExistingRights'] = array('with Existing','yesno', array('Yes','-'));
$columns['Actions']        = 'Options';

$body  = HTML::datatable('accesstable', $columns, $results,'','data-pagelength=100');
if ($APP_SESSION->getCanCreate() || $APP_SESSION->getCanUpdate()) {
    $body .= HTML::resetbutton('reset','Reset', 'default btn-xs');
    $body .= HTML::button('grantall','Grant All', 'default btn-xs');
    $body .= HTML::button('grantallreadonly','Grant All Read Only', 'default btn-xs');
}

if ($APP_SESSION->getCanDelete())
    $body .= HTML::button('denyall','Deny All', 'default btn-xs');

$footer  = HTML::linkbutton($APP_CURRENTPAGE,HTML::icon('arrow-left','Go Back'));
if ($APP_SESSION->getCanUpdate()) 
    $footer .= HTML::button('saveaccess','Save Access','success');
if ($APP_SESSION->getCanDelete()) 
    $footer .= HTML::button('deleteaccess','Delete Access','danger');
echo '<form method=post id="accessrights">';
echo '<input type="hidden" value="" name="command" id="command">';
echo HTML::box(Tools::picturelink($currentemployeecode,'img-circle img-xs') . " Access Rights for <b>$currentemployeecode</b>", $body,$footer,'primary');
echo '</form>';

Tools::showdebuginfo(print_r($_GET, true));
Tools::showdebuginfo(print_r($activerecord, true));

function buildaccesstable($activerecord, $modules, &$results, $level=0, $readonly=false) {
    $disabled = $readonly ? ' disabled' : '';
    foreach ($modules as $pagename => $module) {
        if ($module[0] || is_array($module[4])) {
            $spacing = $level ? "&nbsp;&nbsp;&nbsp;&nbsp;" : '';

            $cols['PageCode']       = $pagename;
            $cols['Description']    = "$spacing$spacing<i class='$module[3]'></i> $module[2]";
            $cols['CurrentRights']  = '';
            $cols['ExistingRights'] = 0;
            
            if ($module[1]) {
                $accesslist = Tools::explodepagerights(@$module[6] . '');
                $actions = '';
                $currentrights = '';
                foreach ($accesslist as $rightscode => $rightsdescription) {
                    $checked = @$activerecord["{$pagename}__$rightscode"] ? 'checked' : '';
                    $actions .= "<label class='checkbox-inline' $disabled><input type='checkbox' id='{$pagename}__$rightscode' name='{$pagename}__$rightscode' value='$rightscode' $checked class='accessitem__$rightscode'$disabled> $rightsdescription</label> ";
                    if ($checked) {
                        $currentrights .= $rightscode;
                        $cols['ExistingRights'] = 1;
                    }
                }
                $cols['CurrentRights']  = $currentrights;
                $cols['Actions'] = $actions;
            }
            else {
                $cols['Actions']        = '<em class="text-gray">Access rights not required</em>';
                $cols['CurrentRights']  = '<em class="text-gray">n/a</em>';
                $cols['ExistingRights'] = 1;
            }
            $results[] = $cols;
        }
        if (is_array($module[4])) 
            buildaccesstable($activerecord, $module[4], $results, $level+1, $readonly);
    }
}
?>
