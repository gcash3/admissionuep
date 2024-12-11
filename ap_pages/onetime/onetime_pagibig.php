<?php
$title = "<img src='ap_img/pagibiglogo.png?v64'> Pag-IBIG Fund";
$employeecode = $APP_SESSION->getEmployeeCode();
$employeeinfo = $APP_DBCONNECTION->execute("Usp_AP_GetEmployeeInfo '$employeecode'");

$body = '';
if (is_array($employeeinfo) && count($employeeinfo)) {
    $employeeinfo = $employeeinfo[0];
    $lastname     = utf8_encode(@$employeeinfo['LastName']);
    $firstname    = @$employeeinfo['FirstName'];
    $middlename   = @$employeeinfo['MiddleName'];
    $pagibig      = @$employeeinfo['PagIBIG_No'];
    $mobilenumber = @$employeeinfo['MobileNumber'];
    $emailaddress = @$employeeinfo['EmailAddress'];
    $mobilenumberlastupdate = @$employeeinfo['MobileNumberLastUpdate'];
    $emailaddresslastupdate = @$employeeinfo['EmailAddressLastUpdate'];
    
}
if (isset($_POST['savepagibig'])) {
    $mobilenumber = @$_POST['MobileNumber'];
    $emailaddress = @$_POST['EmailAddress'];
    $sql = "Usp_AP_UpdateContact '$employeecode', '$mobilenumber', '$emailaddress'";
    $results = $APP_DBCONNECTION->execute($sql);
    if (!is_array($results) || (count($results) == 0)) {
        $body .= HTML::alert('Error','Unable to update contact details!');
    } 
    else {
        
        $body .= "<h3>Thank you for updating your record!</h3>";
        if ($APP_CURRENTPAGE != 'profile')
            $body .= "You can also update your contact info in your <a href='" . APP_BASE . "profile'>profile</a> page.<br>";
        $APP_SESSION->setApplicationMessage($body);
        Tools::redirect($APP_CURRENTPAGE);  
        return;
    }
}

$body .= '<h4>All Faculty Members and Employees:</h4>';
$body .= '<p style="font-size:120%"><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;To update your <b>Pag-IBIG</b> fund records, please provide information needed below. The information you will provide will solely be used by the <b>Pag-IBIG Fund</b> for various announcements and updates.<p>';
$body .= '<hr>';
$body .= '<div class="form-group row">';
$body .= '  <label for="ln" class="col-lg-2 col-form-label">Lastname</label>';
$body .= '  <div class="col-lg-3">';
$body .= '    <input type="text" class="form-control" id="ln" name="ln" readonly value="'.@$lastname.'">';
$body .= '  </div>';
$body .= '</div>';

$body .= '<div class="form-group row">';
$body .= '  <label for="fn" class="col-lg-2 col-form-label">Firstname</label>';
$body .= '  <div class="col-lg-3">';
$body .= '    <input type="text" class="form-control" id="fn" name="fn" readonly value="'.@$firstname.'">';
$body .= '  </div>';
$body .= '</div>';

$body .= '<div class="form-group row">';
$body .= '  <label for="mn" class="col-lg-2 col-form-label">Middlename</label>';
$body .= '  <div class="col-lg-3">';
$body .= '    <input type="text" class="form-control" id="fn" name="mn" readonly value="'.@$middlename.'">';
$body .= '  </div>';
$body .= '</div>';

$body .= '<div class="form-group row">';
$body .= '  <label for="id" class="col-lg-2 col-form-label">Pag-IBIG ID</label>';
$body .= '  <div class="col-lg-3">';
$body .= '    <input type="text" class="form-control" id="id" name="id" readonly value="'.@$pagibig.'">';
$body .= '  </div>';
$body .= '</div>';
$body .= '<hr>';
$body .= '<div class="form-group row">';
$body .= '  <label for="MobileNumber" class="col-lg-2 col-form-label">Mobile No. <i class="fa fa-check" title="Required"></i></label>';
$body .= '  <div class="col-lg-3">';
$body .= '    <input onchange="$(\'#savepagibig\').prop(\'disabled\',false)" type="text" class="form-control" id="MobileNumber" name="MobileNumber" required value="'.@$mobilenumber.'" autofocus  pattern="09[0-9][0-9][0-9]{7}">';
$body .= '    <small>'.@$mobilenumberlastupdate.'</small>';
$body .= '  </div>';
$body .= '  <div class="col-lg-3">';  
$body .= '  <small>Ex. 09991234567</small>';
$body .= '  </div>';
$body .= '</div>';

$body .= '<div class="form-group row">';
$body .= '  <label for="EmailAddress" class="col-lg-2 col-form-label">Email Address</label>';
$body .= '  <div class="col-lg-3">';
$body .= '    <input onchange="$(\'#savepagibig\').prop(\'disabled\',false)"  type="email" class="form-control" id="EmailAddress" name="EmailAddress" value="'.@$emailaddress.'">';
$body .= '    <small>'.@$emailaddresslastupdate.'</small>';
$body .= '  </div>';  
$body .= '</div>';
$body .= '<div class="form-group row">';
$body .= '  <div class="col-lg-5">';
$footer = HTML::button('savepagibig','Update Record','success','submit',false,true);
$body .= '  </div>';
$body .= '</div>';

$footer .= '<a href="https://www.ue.edu.ph/manila/main.html?page=privacy&link=main" target=_blank class="pull-right">*UE Data Privacy Statement</a>';


echo '<form method="Post" autocomplete=off>';
echo HTML::box($title,$body,$footer,'primary',true,false,'');
echo '</form>';
?>