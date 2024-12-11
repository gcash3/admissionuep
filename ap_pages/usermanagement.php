<?php
//---------------------------------------------------------------
// File        : usermanagement.php
// Description : User Management Controller
// Author      : CSN
// Date        : 11/03/2017
// --------------------------------------------------------------
$EmployeeCode = $APP_SESSION->getEmployeeCode();
$currentemployeecode = @$_GET['_p2'];
$cs = @$_GET['_p3'];
$command = @$_POST['command'];

if (($currentemployeecode != '') && !Crypto::ECValidateChecksum($currentemployeecode, $cs)) {
    Tools::showdebuginfo('Checksum error: '. $cs);
    $APP_COMMAND = '';
}


if (($command == 'saveaccess') || ($command == 'deleteaccess')) {
    $currentaccess = array();
    if ($command == 'saveaccess') {
        foreach ($_POST as $name => $value) {
            $i = stripos($name, '__');
            if ($i !== false) {
                $pagename = substr($name, 0, $i);
                $code = substr($name, $i+2);
                @$currentaccess[$pagename] .= $code;
            }
        }
    }
    $currentaccessstring = '';
    foreach ($currentaccess as $key => $value) {
        $currentaccessstring .= ($currentaccessstring ? ';' : '') . "$key:$value";
    }
    $sql = "Usp_AP_SaveAccess '$EmployeeCode', '$currentemployeecode', '$currentaccessstring', '" . APP_MODULENAME . "'";
    $results = $APP_DBCONNECTION->execute($sql);
    if (!Tools::emptydataset($results)) {
        $APP_SESSION->setApplicationMessage("Access rights updated.");  
        Tools::redirect($APP_CURRENTPAGE);
        return;
    }
    else {
        echo HTML::alert('Attention:', 'Unable to save access rights.', 'danger');     
    }
}

//---------------------------------------------------------------
// Call User Management Views
// --------------------------------------------------------------
if ($APP_COMMAND == '')
    include_once("$APP_CURRENTPAGE/{$APP_CURRENTPAGE}_list.php");    
elseif ($APP_COMMAND == 'view')
    include_once("$APP_CURRENTPAGE/{$APP_CURRENTPAGE}_form.php");    
   

?>