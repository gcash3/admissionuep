<html>
<head>
<title>PHPMailer - SMTP basic test with no authentication</title>
</head>
<body>

<?php
require_once('class.phpmailer.php');

$mail             = new PHPMailer();

$mail->SMTPAuth   = true;                  
$mail->SMTPSecure = "tls";
$mail->Username   = "adminportal1@ue.edu.ph";  
$mail->Password   = "adminportal12345@";

$mail->IsSMTP();                          
$mail->Port       = 587;   
$mail->Host       = "smtp.gmail.com";
$mail->SetFrom('noreply@ue.edu.ph', 'UE WebGS');
$mail->AddReplyTo("noreply@ue.edu.ph","UE WebGS");

$mail->Subject    = "UE WebGS Notification";
$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; 
$body             = "TESTING phpmailer";

$mail->MsgHTML($body);
$mail->AddAddress("cnuarin@gmail.com", "Chito Nuarin");

if(!$mail->Send()) {
  echo "Mailer Error: " . $mail->ErrorInfo;
} 
else {
  echo "Message sent!";
}

?>

</body>
</html>
