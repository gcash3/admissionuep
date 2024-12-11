<?php
$UserEmployeeCode = $APP_SESSION->getEmployeeCode();
$sql = "Usp_AP_GetEmployeeInfo '$UserEmployeeCode'";
$results = $APP_DBCONNECTION->execute($sql);
if (!Tools::emptydataset($results)) {
    $activerecord = $results[0];
}
else {
    echo HTML::alert('Error', 'Unable to retrieve user info!');
    return;
}

$body = '';
$title = '';
$footer = '';

$name         = ucwords(strtolower(Data::sanitize($activerecord['Name'])));
$position     = ucwords(strtolower(Data::sanitize($activerecord['Position'])));
$email        = Data::sanitize(@$activerecord['Email']);
$department   = Data::sanitize(@$activerecord['Department']);
$sss          = Data::sanitize(@$activerecord['SSS_No']);
$tin          = Data::sanitize(@$activerecord['TIN']);
$phic         = Data::sanitize(@$activerecord['PhilHealth']);
$pagibig      = Data::sanitize(@$activerecord['PagIBIG_No']);

$contactperson  = Data::sanitize(@$activerecord['ContactPerson']);
$contactaddress = Data::sanitize(@$activerecord['ContactAddress']);
$contactnumber  = Data::sanitize(@$activerecord['ContactNumber']);
$contactmobile  = Data::sanitize(@$activerecord['ContactMobileNumber']);


$department = Data::sanitize($activerecord['Department']);

$body = '';
$body .= "<img src='" . Tools::pictureurl($UserEmployeeCode) . "' class='profile-user-img img-responsive img-circle' alt='User Image'>";
$body .= "<h3 class='profile-username text-center'>$name</h3>"; 
$body .= "<p class='text-muted text-center'>$position</p>";
$body .= '<ul class="list-group list-group-unbordered">';
$body .= '<li class="list-group-item"><b>Employee Code</b> <a class="pull-right">' . $UserEmployeeCode . '</a></li>';
$body .= '<li class="list-group-item"><b>UE Email</b> <a class="pull-right" href="https://mail.google.com/mail/u/?authuser='.$email.'" target=gmail>'.$email.'</a></li>';
$body .= '<li class="list-group-item"><b>Department</b> <a class="pull-right">'.$department.'</a></li>';
$body .= '</ul>';
$body .= 'Initial password for UE Email:<br>birthdate + last ' . (strlen($UserEmployeeCode) >= 4 ? '4' : '3') . ' digits of employee code<br>format: <b><span class="label label-danger">yyyy</span><span class="label label-info">mm</span><span class="label label-warning">dd</span><span class="label label-success">9999</span></b>';

$title = $name;
$footer .= HTML::linkbutton('changepassword','Change UE Portal Password','success');

echo '<div class="row">';
echo '<div class="col-lg-4">';
echo HTML::box(null,$body, $footer,'primary',false,false,'box-profile');
echo '</div>';


$title = HTML::icon('envelope','Emergency Contact Details');
$body = '';
$footer = '';
$body .= '<ul class="list-group list-group-unbordered">';
$body .= '<li class="list-group-item"><b>Contact Person</b> <a class="pull-right">' . ucwords(strtolower($contactperson)). '</a></li>';
$body .= '<li class="list-group-item"><b>Address</b> <a class="pull-right">'.ucwords(strtolower($contactaddress)).'</a></li>';
$body .= '<li class="list-group-item"><b>Telephone No.</b> <a class="pull-right">'.$contactnumber.'</a></li>';
$body .= '<li class="list-group-item"><b>Mobile No.</b> <a class="pull-right">'.$contactmobile.'</a></li>';
$body .= '<li class="list-group-item">&nbsp;<a class="pull-right">'.'&nbsp;'.'</a></li>';
$body .= '<li class="list-group-item">&nbsp;<a class="pull-right">'.'&nbsp;'.'</a></li>';
$body .= '</ul>';

echo '<div class="col-lg-5">';
echo HTML::box($title,$body, $footer,'danger box-profile');
echo '</div>';

$title = HTML::icon('bank','Government ID');
$body = '';
$footer = '';
$body .= '<ul class="list-group list-group-unbordered">';
$body .= '<li class="list-group-item"><b>SSS No.</b> <a class="pull-right">' . $sss. '</a></li>';
$body .= '<li class="list-group-item"><b>TIN</b> <a class="pull-right">'.$tin.'</a></li>';
$body .= '<li class="list-group-item"><b>Philhealth</b> <a class="pull-right">'.$phic.'</a></li>';
$body .= '<li class="list-group-item"><b>Pag-IBIG</b> <a class="pull-right">'.$pagibig.'</a></li>';
$body .= '<li class="list-group-item"><b>PRC No.</b> <a class="pull-right">'.'[none]'.'</a></li>';
$body .= '<li class="list-group-item"><b>National ID</b> <a class="pull-right">'.'[none]'.'</a></li>';
$body .= '</ul>';

echo '<div class="col-lg-3">';
echo HTML::box($title,$body, $footer,'success box-profile');
echo '</div>';



echo '</div>';
?>