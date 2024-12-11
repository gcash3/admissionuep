<?php

include("mailer/class.phpmailer.php");
include("mailer/class.smtp.php"); // note, this is optional - gets called from main class if not already loaded

$mail             = new PHPMailer();

$mail->IsSMTP();
$mail->SMTPAuth   = true;                  // enable SMTP authentication
$mail->Port       = 587;                   // set the SMTP port

$mail->Username   = "adminportal1@ue.edu.ph";  // GMAIL username
$mail->Password   = "adminportal12345@";       // GMAIL password

$mail->SMTPDebug = 2;
$mail->Subject    = "UE AdminPortal Notification";
$mail->SetFrom('noreply@ue.edu.ph', 'UE AdminPortal');
$mail->AddReplyTo("noreply@ue.edu.ph","UE AdminPortal");
$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; 
$mail->AddAddress("cnuarin@gmail.com","chito nuarin");

$mail->MsgHTML('test');
$mail->IsHTML(true); // send as HTML

$mail->SMTPSecure = "tls";                 // sets the prefix to the servier
$mail->Host       = "smtp.gmail.com";      // sets GMAIL as the SMTP server

if(!$mail->Send()) {
    echo "Mailer Error: " . $mail->ErrorInfo;
} 
else {
    echo "Message has been sent";
}

?>
