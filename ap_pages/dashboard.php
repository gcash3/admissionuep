<?php
$dashboard = $APP_SESSION->getDashboard();
if ($dashboard != 'dashboard') {
    Tools::redirect($dashboard);
}
@include_once( $_SERVER['DOCUMENT_ROOT'] . '/portals/common/php/dashboardbanner.php');
echo '<div class="row">';
echo '</div>';
if ($APP_SESSION->getDemo()) {
    $s = HTML::alert('Attention','<b>Admission Portal</b> Test Installation! This installation is only for testing and data will reset without notice.', 'danger',false);
    echo $s;
}
else {
}

?>

