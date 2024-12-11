<?php
$EmployeeCode   = $APP_SESSION->getEmployeeCode();
$filenameserver = base64_decode($_GET['_p1']);
$filenameactual = base64_decode($_GET['_p2']);
$cs             = $_GET['_p3'];

$mimetype = Tools::getMimeType($filenameactual, true);

if (!Crypto::DLValidateChecksum($filenameserver, $filenameactual, $cs)) {
    echo HTML::windowclose();
    return;
}
$filenameserver = APP_UPLOADDIR . $filenameserver;
if (!file_exists($filenameserver)) {
    echo HTML::windowclose();
    return;
}
ob_clean();
//$mimetype = @mime_content_type($filenameserver);
header("Content-Type: $mimetype");
header("Content-Disposition: inline; filename='$filenameactual'");
header("Cache-Control: no-cache, must-revalidate");
echo @file_get_contents($filenameserver);

?>
