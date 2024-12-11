<form id="form" name="form" class='form-horizontal' autocomplete='off' method=post>
<?php
/*------------------------------------------------------------
 * Filename    : updateaddress.php
 * Description : Page for editting name,address,mobile,email
 * Author      : Chito S. Nuarin
 * Date        : 10/29/2020
 * Note        : Can be included in any page. 
 *               $currentpage = 'updateaddress';
 * 
 *               // To edit other employee record
 *               $TargetEmployeeCode = 'xxx'; 
 *               $nodataprivacycheck = true;  
 *               $redirectaftersave = 'dashboad'   
 *               $opener = '';   
 *               include_once('updateaddress.php');
 * 
 * Files       : ap_pages/updateaddress.php  (this file)
 *               ap_pages/updateaddress/updateaddress_functions.php
 *               ap_pages/updateaddress/updateaddress_current.php
 *               ap_pages/updateaddress/updateaddress_form.php
 *               ap_pages/footer/foooter_updateaddress.php
 *               ap_ajax/updateaddress.php
 ------------------------------------------------------------*/
$currentpage = 'updateaddress';
require_once("$currentpage/{$currentpage}_functions.php"); 
$UserEmployeeCode = $APP_SESSION->getEmployeeCode();
if (@$TargetEmployeeCode == '')
    $TargetEmployeeCode = $UserEmployeeCode;

$command = @$_GET['_p1'];
if (@$opener == '')
    $opener = @$_GET['_p2'];

$CurrentName = '';
$CurrentAddress = '';
$CurrentMobileNumber = '';
$CurrentEmailAddress = '';
$CurrentMobileNumberLastUpdate = '';
$CurrentEmailAddressLastUpdate = '';
$CurrentAddressLastUpdate = '';

$Firstname = Data::sanitize(@$_POST['txtFirstname']);
$Lastname =  Data::sanitize(@$_POST['txtLastname']);
$Middlename =  Data::sanitize(@$_POST['txtMiddlename']);
$Suffix =  Data::sanitize(@$_POST['txtSuffix']);

$MobileNumber =  Data::sanitize(@$_POST['txtMobileNumber']);
$EmailAddress = Data::sanitize(@$_POST['txtEmailAddress']);
$Address = Data::sanitize(@$_POST['txtAddress']);
$ZIPCode = Data::sanitize(@$_POST['txtZIPCode']);

$Region = Data::sanitize(@$_POST['txtRegion']);
$Province = Data::sanitize(@$_POST['txtProvince']);
$City = Data::sanitize(@$_POST['txtCity']);
$SubMun = Data::sanitize(@$_POST['txtSubMun']);
$Barangay = Data::sanitize(@$_POST['txtBarangay']);
$HouseNumber = Data::sanitize(@$_POST['txtHouseNumber']);
$SubAddress = Data::sanitize(@$_POST['txtSubAddress']);

$sql = "Usp_AP_GetEmployeeAddress '$TargetEmployeeCode'";
$results = $APP_DBCONNECTION->execute($sql);
if (!Tools::emptydataset($results)) {
    $record = $results[0];
    $CurrentName = $record['Name'];
    $CurrentAddress = $record['Address'];
    if (count($_POST) == 0) {
        $Firstname = $record['Firstname'];
        $Lastname = $record['Lastname'];
        $Middlename = $record['Middlename'];
        $Suffix = $record['Suffix'];
        $MobileNumber = $record['MobileNumber'];
        $EmailAddress = $record['EmailAddress'];
        $Region = $record['Region'];
        $Province = $record['Province'];
        $City = $record['City_Mun'];
        $SubMun = $record['SubMun'];
        $Barangay = $record['Barangay_PSGC'];
        $HouseNumber = $record['HouseNumber'];
        $SubAddress = $record['SubAddress'];
        $Address = $record['Address'];
        $ZIPCode = trim($record['ZIPCode']);
    }

    $CurrentMobileNumber = @$record['MobileNumber'];
    $CurrentEmailAddress = @$record['EmailAddress'];
    $CurrentMobileNumberLastUpdate = trim(@$record['MobileNumberLastUpdate']);
    $CurrentEmailAddressLastUpdate = trim(@$record['EmailAddressLastUpdate']);
    $CurrentAddressLastUpdate = trim(@$record['AddressLastUpdate']);

    if ($CurrentMobileNumberLastUpdate) {
        $CurrentMobileNumberLastUpdate = ' <sup data-toggle="tooltip" title="Last Update: '. Data::formatdate($CurrentMobileNumberLastUpdate,'m/d/Y h:i:s') . '">' . HTML::icon('info') . '</sup>';
    }

    if ($CurrentEmailAddressLastUpdate) {
        $CurrentEmailAddressLastUpdate = ' <sup data-toggle="tooltip" title="Last Update: '. Data::formatdate($CurrentEmailAddressLastUpdate,'m/d/Y h:i:s') . '">' . HTML::icon('info') . '</sup>';
    }    

    if ($CurrentAddressLastUpdate) {
        $CurrentAddressLastUpdate = ' <sup data-toggle="tooltip" title="Last Update: '. Data::formatdate($CurrentAddressLastUpdate,'m/d/Y h:i:s') . '">' . HTML::icon('info') . '</sup>';
    }    

    if ($ZIPCode)
        $CurrentAddress .= ", $ZIPCode";
}
else {
    echo Tools::devonly('<pre>$results:' . print_r($results,true) . "<br>\$sql: $sql</pre>");
}
    

$SubMunArray = array();
$BarangayArray = array();
$ProvinceArray = array();
$CityArray = array();

loadList('', $RegionArray);
if (trim($Region))
    loadList($Region, $ProvinceArray);

if (trim($Province)) 
    loadList($Province, $CityArray);
if ($Region == '130000000')
    loadList($Region, $CityArray);

if (trim($SubMun)) {
    loadList($City, $SubMunArray);
    loadList($SubMun, $BarangayArray);
}
elseif (trim($City)) {
    loadList($City, $BarangayArray);
}

$SaveDisabled = ($Barangay == '');
if (isset($_POST['Save'])) {
    $sql = "Usp_AP_SaveEmployeeInfo '$TargetEmployeeCode', '$Lastname', '$Firstname', '$Middlename', '$Suffix', '$Barangay', '$Address', '$SubAddress', '$ZIPCode', '$MobileNumber', '$EmailAddress', '$HouseNumber', '$UserEmployeeCode'";
    $results = $APP_DBCONNECTION->execute(iconv("UTF-8",'CP1252',$sql));
    if (TOOLS::emptydataset($results)) {
        echo HTML::alert('Error!', 'Unable to update personal information at this moment!');
        echo Tools::devonly("<pre>DEBUG INFO:<br>SQL: $sql<br>" . print_r($results,true) . "</pre>");
        $SaveDisabled = false;
    }
    else {
        if ($UserEmployeeCode == $TargetEmployeeCode) {
            $APP_SESSION->setapplicationmessage('Thank you for updating your record.', 'success');
            if ($APP_SESSION->getsessionvalue('RunOncePage') == 'updateaddress')
                $APP_SESSION->setsessionvalue('RunOncePage','');
        }
        TOOLS::redirect(@$redirectaftersave ? $redirectaftersave : 'dashboard');
    }

    $SubAddress = utf8_decode($SubAddress);
    $Address = utf8_decode($Address);
    $Lastname = utf8_decode($Lastname);
    $Firstname = utf8_decode($Firstname);
    $Middlename = utf8_decode($Middlename);
}

if (($command == 'edit') || @$nodataprivacycheck)
    require_once("$currentpage/{$currentpage}_form.php"); 
else {
    $statement = '';
    if ($APP_SESSION->getsessionvalue('RunOncePage'))
        $statement = "<p><b>You are requested to encode your specified personal data for the updating of your records in the Department of Human Resources and Development (DHRD)</b></p>";
    $statement .= '<p>Collected information will form part of your employment details as an employee of the University of the East and will be used for legitimate purposes only.  These will be kept in a secure location and will be disposed of five (5) years after your employment in the University. This is without prejudice to providing the collected information to government agencies as well as the judicial and quasi judicial branches of the government should they require to obtain the same. </p>';
    $statement .= '<p>For the full Privacy Statement of the University, please click this link:  <b><a href="https://www.ue.edu.ph/mla/data-privacy-notice/" target=_blank>UE Data Privacy Policy</a></b></p>';
    $statement .= '<p><b>To signify your agreement, please click on the button below.</b></p>';
    $statement .= HTML::linkbutton($APP_CURRENTPAGE . "/edit/$command", 'I AGREE','warning');

    echo HTML::callout('Attention',$statement,'info');
    require_once("$currentpage/{$currentpage}_current.php"); 
   
}
?>
</form>

