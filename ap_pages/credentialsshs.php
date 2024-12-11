<?php
if ($APP_SESSION->getPageSemester() == '') {
    echo  HTML::alert('','Please select semester first!','danger',false);
    return;
}
require_once("credentialscommon.php");
$system = new CRUDcredentials('Applicant','ApplicationNumber','t1', $APP_SESSION->getuserid(), $APP_CURRENTPAGE, 'S');
$system->run();

?>
