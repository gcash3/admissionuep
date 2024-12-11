<?php
class Email {
    private $mail;
    private $Username = 'AdminPortal1@ue.edu.ph';
    private $Password = APP_DB_PASSWORD;
    private $SenderID = null;
    private $UserEmployeeCode = null;
    public  $DemoAddress = APP_EMAIL_TESTACCOUNT;
    
    function __construct() {
        global $APP_SESSION;
        $this->mail = new PHPMailer();
        $this->mail->IsSMTP();
        $this->mail->SMTPAuth   = true;                  
        $this->mail->Port       = 587;                   
        $this->UserEmployeeCode = $APP_SESSION->getEmployeeCode();
    }
    
    function send($send, $subject, $body, $to, $bcc='', $senderemail='') {
        $this->mail->SetFrom('noreply@ue.edu.ph', 'UE AdminPortal');
        $this->mail->AddReplyTo("noreply@ue.edu.ph","UE AdminPortal");
        $this->mail->Subject    = $subject;
        $this->mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; 
        if (!is_array($to))
            $recipients[] = $to;
        else
            $recipients = $to;
        if (!is_array($bcc))
            $recipientsbcc[] = $bcc;
        else
            $recipientsbcc = $bcc;

        foreach ($recipients as $to) {
            if ($to != '') {
                if ($this->DemoAddress)
                    $to = str_replace('@', '.test@', $to);
                $this->mail->AddAddress($to, $to);
            }
        }
        foreach ($recipientsbcc as $bcc) {
            if ($bcc != '') {
                if ($this->DemoAddress)
                    $bcc = str_replace('@', '.test@', $bcc);
                $this->mail->AddBCC($bcc, $bcc);
            }
        }
        
        $this->mail->IsHTML(true); 
        $this->mail->MsgHTML($body);
        $this->mail->SMTPSecure = "tls";                 
        $this->mail->Host       = "smtp.gmail.com";      
        $this->getNextEmailPassword($senderemail);
        $this->mail->Username   = $this->Username;
        $this->mail->Password   = $this->Password;
        if ($senderemail == '')
            $this->updateEmailCounter();
        if ($send)
            return @$this->mail->Send();
        else
            return true;
    }
    
    private function getNextEmailPassword($senderemail = '') {
        global $APP_DBCONNECTION;
        if ($senderemail == '')
            $senderemail = APP_MODULENAME;
        $sql = "Usp_AP_GetEmailSender '$senderemail'";
        $results = $APP_DBCONNECTION->execute($sql);
        if (!Tools::emptydataset($results)) {
            $record = $results[0];
            $this->Username = $record['EmailAddress'];
            $this->Password = $record['Password'];
            $this->SenderID = $record['SenderID'];
            if (@$record['Host']) {
                $senderemailname = 'UE Admin Portal';
                if (trim(@$record['SenderEmailName']))
                    $senderemail = $record['SenderEmailName'];
                $this->mail->SetFrom(@$record['SenderEmail'], $senderemailname);
                $this->mail->Host       = @$record['Host'];   
                $this->mail->SMTPAuth   = @$record['SMTPAuth'] == true;                  
                $this->mail->Port       = @$record['Port']; 
                $this->mail->SMTPSecure = @$record['SMTPSecure'];  
            }
        }
    }
    
    private function updateEmailCounter() {
        global $APP_DBCONNECTION;
        $sql = "Usp_AP_SaveEmailCounter " . $this->SenderID;
        $results = $APP_DBCONNECTION->execute($sql);
    }
    
    public function sendTemplate($templateid, $values, $to, $bcc='', $SendEmail=true, $preview=false) {
        global $APP_DBCONNECTION;
        global $APP_SESSION;
        if ($templateid > 0) {
            $sql = "Usp_AP_GetEmailTemplate $templateid, '{$this->UserEmployeeCode}'";
            $results = $APP_DBCONNECTION->execute($sql);  
        }
        else {
            $results[0] = $values;
        }
        if (!Tools::emptydataset($results)) {
            $record = $results[0];
            $messagebody = $record['MessageBody'];
            $subject = $record['Subject'];
            foreach ($values as $key => $value) {
                if (!is_array($value)) {
                    $messagebody = str_ireplace("[@$key]", $value, $messagebody);
                    $subject = str_ireplace("[@$key]", $value, $subject);
                }
            }
            if (($to == '') && is_array($bcc) && (count($bcc)==1)) {
                $to = $bcc;
                $bcc = '';
            }
            if ($preview) {
                $tos = is_array($to) ? implode(', ',$to) : $to;
                $html = '';
                if ($tos)
                    $html  = "To: <b>" . $tos . '</b><br>';
                if ($bcc) {
                    $bccs = is_array($bcc) ? implode(', ',$bcc) : array($bcc);
                    $html .= "Bcc: <b>" . $bccs . '</b><br>';
                }
                $html .= "Subject: <b>$subject</b><br>";
                $html .= "<hr>$messagebody";
            }
            $rv = false;
            $rv = $this->send($SendEmail, $subject, $messagebody, $to, $bcc);
            if ($preview) {
                $html = "From: <b>" . $this->Username . '</b><br>' . $html;
                $APP_SESSION->setEmailPreview($html, true);
            }
            return $rv;
        }
        else {
            return false;
        }
    }
}  
?>
