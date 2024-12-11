<?php
if ($APP_SESSION->getMustChangePassword() && (count($_POST)==0)) {
    echo HTML::alert('Alert!','You must change your password to proceed.','warning');
}
class CRUDUsers extends CSNCRUD {
    private $assignnew = false;
    private $mustchangepassword = false;
    function initialize() {
        global $APP_SESSION;
        $this->setpageaccess(true,true,true);
        $this->assignnew = $APP_SESSION->validatePassword('');
        $this->mustchangepassword = $APP_SESSION->getMustChangePassword();
        $this->command = $this->updatecommand;
        $this->showtopbuttons = false;
        $this->showback = false;
        $this->addtopbuttoncommand($this->defaultcommand, "", "",'','',array($this->updatecommand),'',"Usp_AP_SaveLoginAlias '$this->currentuserid'",'');        

        $this->addformfield('', 0, 'userid', $this->currentuserid ,'hidden');
        if (!$this->assignnew)
            $this->addformfield('', 0, 'cup' ,'' ,'password','Current Password');
        $this->addformfield('', 0, 'np'      ,'' ,'password','New Password','','',$this->mustchangepassword);
        $this->addformfield('', 0, 'cp'  ,'' ,'password','Confirm Password','','',$this->mustchangepassword);
        $this->addformfield('',0,'remarks',"Password must be at least 6 characters and must contain at least 1 number and 1 upper case letter.",'html',' ');
        $this->addformfield('', 1, 'un'  ,'' ,'text','Login Alias','Login alias','',false);  
    }

    function callback_fetchformtitle(&$title, &$icon) {
        $title = $this->assignnew ? 'New' : 'Update' ;
        $icon = 'key';
    }

    function callback_validateform($commandtype, &$alertmessage) {
        global $APP_SESSION;
        if ($commandtype = $this->postsavecommand) {
            $alertmessage = Data::validatenewpassword($APP_SESSION->getUsername(),'','', $this->activerecord['np'], $this->activerecord['cp'], $this->command==$this->createcommand);
            if (!$this->assignnew && ($alertmessage == '') && !$APP_SESSION->validatePassword($this->activerecord['cup'])) {
                $alertmessage = 'Invalid current password!';
            }
            if ($alertmessage == '') {
                $password = Crypto::PasswordChecksum($this->activerecord['np']);
                $this->setfieldsproperties('','newpassword','valuequoted', "'$password'");
            }
        }
    }

    function callback_fetchformfooterbuttons(&$footer) {
        $footer .= HTML::submitbutton('Logout','Log Out','default') . '<kbd class="pull-right" id="capslock" style="display:none">CAPSLOCK</kbd>';
    }

    function callback_post(&$cancel) {
        if (isset($_POST['Logout'])) {
            Tools::redirect('signout');
            $cancel = true;
        }      
    }
    
    function callback_beforesave(&$cancel) {
        global $APP_SESSION;
        $CurrentPassword = @$this->activerecord['cup'];
        $NewPassword = $this->activerecord['np'];
        $ec = $APP_SESSION->getEmployeeCode();
        $username = $this->activerecord['un'];
        $alertmessage = ''; 
        if (($NewPassword == '') && ($username == '')) {
            $alertmessage = 'Please input new password and/or login alias!';
        }
        elseif (($CurrentPassword != $NewPassword) && $NewPassword) {
            $db = new CSNmssql($ec, $CurrentPassword, 'master', APP_DB_SERVERNAME, true);
            if ($db->connected) {
                $sql = "ALTER LOGIN [$ec] WITH PASSWORD = '$NewPassword' OLD_PASSWORD='$CurrentPassword'";
                $results = $db->execute($sql);
                if (($db->errormessage == "ERROR: Changed database context to 'master'.") || ($db->errormessage == '')) {
                    DATA::savelog($ec,'CP');
                }
                else {
                    $alertmessage = 'ERROR: Unable to change password!';
                }
            }
            else {
                $alertmessage = 'Unable to connect to server!' . "[$ec] [$CurrentPassword]" . APP_DB_SERVERNAME;
            }      
        }
        if ($alertmessage)
            echo HTML::alert('Error',$alertmessage);
        $cancel = $alertmessage != '';   
    }   

    function callback_aftersave($commandtype, &$redirect) {
        global $APP_SESSION;
        if ($this->activerecord['np']) {
            $APP_SESSION->setPassword($this->activerecord['np']);
            $APP_SESSION->setMustChangePassword(false);
            $APP_SESSION->setApplicationMessage('Password changed!');
        }
        else  {
            $APP_SESSION->setApplicationMessage('Login alias changed!');  
        }
        
        $redirect = $this->mustchangepassword ? 'dashboard' : 'profile';        
    }
 
}

$users = new CRUDUsers('User','userid','form', $APP_SESSION->getuserid(), $APP_CURRENTPAGE);
$users->run();
?>


