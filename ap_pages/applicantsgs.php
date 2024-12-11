<?php
require_once("applicantscommon.php");
$system = new CRUDcredentials('Applicant','WebReferenceNumber','t1', $APP_SESSION->getuserid(), $APP_CURRENTPAGE, 'G');
$system->run();

?>
