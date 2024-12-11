<?php
if ($APP_SESSION->getPageSemester() == '') {
    echo  HTML::alert('','Please select semester first!','danger',false);
    return;
}
require_once("appsummarycommon.php");
$system = new CRUDApplicants('Applicants','type1','type1', $APP_SESSION->getuserid(), $APP_CURRENTPAGE, 'S');
$system->run();

?>