<?php
if ($APP_SESSION->getPageSemester() == '') {
    echo  HTML::alert('','Please select semester first!','danger',false);
    return;
}

//echo "Testing";

require_once("applicantscommonkg10.php");
$system = new CRUDcredentials('Applicant','WebReferenceNumber','t1', $APP_SESSION->getuserid(), $APP_CURRENTPAGE, 'B');
$system->run();

?>
