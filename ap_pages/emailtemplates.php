<?php
//---------------------------------------------------------------
// File        : emailtemplates.php
// Description : Email Templates Controller
// Author      : CSN
// Date        : 10/05/2018
// --------------------------------------------------------------
include("ap_fw/mailer/class.phpmailer.php");
include("ap_fw/mailer/class.smtp.php"); 
include("ap_php/UE.class.Email.php");

$command = @$_GET['_p1'];
$opener  = '';
if ($command == '')
    $command = 'current';
$opener = @$_GET['_p5'] ? @$_GET['_p5'] : '';      
    
$UserEmployeeCode = $APP_SESSION->getEmployeeCode();

if ($APP_SESSION->getCanCreate()) 
    $titles['new']     = array(HTML::icon('plus', 'New Template'), 'info', $opener);
$titles['current']     = array(HTML::icon('list', 'Current Templates'), 'success');
$titles['deleted']     = array(HTML::icon('trash', 'Deleted Templates'), 'danger');
$titles['all']         = array(HTML::icon('list', 'All Templates'), 'warning');

$TemplateID     = 0;
$Description    = '';
$Subject        = '';
$MessageBody    = '';
$Revision       = 0;
$LastModified   = '';
$To             = '';
$Bcc            = '';

$activerecord = array();

$showform = in_array($command, array('view','edit','delete','preview'));
$showgrid = in_array($command, array('current','deleted','all'));


if ($showform) {
    $TemplateID = $_GET['_p2'] + 0;
    $cs = @$_GET['_p3'];
    if (!Crypto::DTValidateChecksum($TemplateID, $cs)) {
        echo HTML::alert('Error', 'Invalid parameters!' . "[$cs]");
        $command = $opener;
        $showform = false;
    }
    else {
        $sql = "Usp_AP_GetEmailTemplate $TemplateID, '$UserEmployeeCode'";
        $results = $APP_DBCONNECTION->execute($sql);
        if (!Tools::emptydataset($results)) {
            $activerecord = $results[0];
        }
        else {
            echo HTML::alert('Error', 'Unable to retrieve email template!');
            $command = $opener;
            $showform = false;
            $showgrid = true;
        }
    }
}

// get form data from $_POST (form)
if (count($_POST)) {
    foreach ($_POST as $key => $value) {
        if (substr($key,-2,2) == '[]')
            $key = substr($key, 0, strlen($key)-2);
        $activerecord[$key] = $value;
    }
}

$postback = isset($_POST['EmployeeCode']);

// retrieve and sanitize data from activerecord (from DB or $_POST)
if (count($activerecord)) {
    $Description    = Data::sanitize(@$activerecord['Description']);
    $Subject        = Data::sanitize(@$activerecord['Subject'],'', false);
    $MessageBody    = Data::sanitize(@$activerecord['MessageBody'],'', false);
    $Revision       = Data::sanitize(@$activerecord['Revision']);
    $LastModified   = Data::formatdate(Data::sanitize(@$activerecord['ModifiedDate'])) . ' by ' . Data::sanitize(@$activerecord['ModifiedBy']);
    $To             = Data::sanitize(@$activerecord['To']);
    $Bcc            = Data::sanitize(@$activerecord['Bcc']);
}

// process button event
if (count($_POST) ) {
  $alertmessage  = '';
  $alertclass    = 'danger';
  if (isset($_POST['SaveEmailTemplate']) && ($APP_SESSION->getCanCreate() || $APP_SESSION->getCanUpdate())) {
        if (Tools::emptyvalues($Description, $Subject, $MessageBody))  {
            $alertmessage = 'Form contains invalid data format!';
        }
        else {
            $sql = "Usp_AP_SaveEmailTemplate $TemplateID, '$UserEmployeeCode', '$Description', '$Subject', '$MessageBody', '" . APP_MODULENAME . "'";
            $results = $APP_DBCONNECTION->execute($sql);

            if (!Tools::emptydataset($results)) {
                $ApplicationID = $results[0]['TemplateID'];
                $APP_SESSION->setApplicationMessage($results[0]['Modified']  ? "Email Template recorded." : 'No changes made to the current record.');
                Tools::redirect("$APP_CURRENTPAGE/$opener");                
            }
            else {
                $alertmessage = 'Unable to save email template! ';
            }
        }
    
    }
    elseif (isset($_POST['DeleteEmailTemplate']) && $APP_SESSION->getCanDelete()) {
        $results = $APP_DBCONNECTION->execute("Usp_AP_DeleteEmailTemplate '$UserEmployeeCode', $TemplateID");
        if (!is_array($results) || count($results) == 0) {
            $alertmessage = $APP_DBCONNECTION->errormessage;
        }
        else {
            $APP_SESSION->setApplicationMessage('Email Template Deleted.');
            Tools::redirect("$APP_CURRENTPAGE/deleted");
        }
        
    }
    elseif (isset($_POST['SendTestEmail'])) {
        $mail = new Email();
        if (!$mail->sendTemplate($TemplateID, array(), $To, $Bcc))
            $alertmessage = 'Unable to send test email!';
        else {
            $alertmessage = 'Test mail sent!';
            $alertclass = 'success';
        }
    }
    

    if ($alertmessage != '') {
        echo HTML::alert('Attention:', $alertmessage, $alertclass);
    }    
}


if ($showform || ($command == 'new')) {
    include_once("$APP_CURRENTPAGE/{$APP_CURRENTPAGE}_form.php");       
}
elseif ($showgrid) {
    include_once("$APP_CURRENTPAGE/{$APP_CURRENTPAGE}_list.php");
}
elseif ($command == 'search') {
    //include_once("$APP_CURRENTPAGE/{$APP_CURRENTPAGE}_search.php");
}

?>

