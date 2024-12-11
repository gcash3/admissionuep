<?php
Class Crypto {
	// Generic password checksum
    static public function PasswordChecksum($id) {
        return sha1("#$%2sdfdsfasfsf34;$id;&*(789sdgdgjjdjd");
    }
   									  
    static public function PasswordValidateChecksum($id, $cs) {
        return Crypto::PasswordChecksum($id) == $cs;
    }

     // validity: 1 year, 1 month, 1 day, 1 hour, 1 minute
    static public function GenericChecksum($id,$validitydateformat='mdY') {
        return substr(sha1("#$%2affasff34;$id;&*(78mvmmu9" . date($validitydateformat)),0,40);
    }
    
    static public function ValidateGenericChecksum($id, $cs) {
        return Crypto::GenericChecksum($id) == $cs;
    } 
	
    // Document Tracker Checksum
    static public function DTchecksum($id) {
        return substr(sha1("csn;$id;malupit;"),0,5) . substr(sha1("bulu;$id;tong;"),-5);
    }
    
    // Document Tracker Checksum validator
    static public function DTValidateChecksum($id, $cs) {
        return Crypto::DTchecksum($id) == $cs;
    }
    
    // Picture Checksum
    static public function imagechecksum($id) {
        return sha1("larawanngmgaempleyadoatprofessor;$id;99999999111111");
    }
    
    // Picture Checksum validator
    static public function validateimagechecksum($id, $cs) {
        return Crypto::imagechecksum($id) == $cs;
    }
    
    static public function ECchecksum($id) {
        return substr(sha1("huawei maganda;$id;j7;"),0,5) . substr(sha1("sheryl;$id;gadget;"),-5);
    }
    
    // Document Tracker Checksum validator
    static public function ECValidateChecksum($id, $cs) {
        return Crypto::ECchecksum($id) == $cs;
    }    
    
    // File downloaer Checksum
    static public function DLchecksum($filenameserver, $filenameactual) {
        return substr(sha1("$filenameserver;chito;$filenameactual;lodi"),0,5) . substr(sha1("$filenameserver;petmalu;$filenameactual;lodi"),-6);
    }
    
    // Downloader Checksum validator
    static public function DLValidateChecksum($filenameserver, $filenameactual, $cs) {
        return Crypto::DLchecksum($filenameserver, $filenameactual) == $cs;
    }
    
    static public function ClearanceChecksum($deptcode, $campuscode, $itemid, $payclass, $employeecode) {
        return sha1("chito;$deptcode;$campuscode;$itemid;$employeecode;the great");
    }
    
    static public function ValidateClearanceChecksum($deptcode, $campuscode, $itemid, $payclass, $employeecode, $cs) {
        return Crypto::ClearanceChecksum($deptcode, $campuscode, $itemid, $payclass, $employeecode) == $cs;
    }
    
    static public function ClearanceViewerChecksum($ApplicationID, $ApplicationKey, $EmployeeCode) {
        return sha1("@ChItO;$ApplicationID;$ApplicationKey;$EmployeeCode;NuArIn@");        
    }
    
    static public function ValidateClearanceViewerChecksum($ApplicationID, $ApplicationKey, $EmployeeCode, $cs) {
        return Crypto::ClearanceViewerChecksum($ApplicationID, $ApplicationKey, $EmployeeCode) == $cs;
    }
    
    static public function DirectPageLinkChecksum($EmployeeCode, $LinkValidity, $PageToOpen) {
        return sha1("big;bang;$EmployeeCode;$LinkValidity;$PageToOpen;theory");        
    }
    
    static public function ValidateDirectPageChecksum($EmployeeCode, $LinkValidity, $PageToOpen, $cs) {
        return Crypto::DirectPageLinkChecksum($EmployeeCode, $LinkValidity, $PageToOpen) == $cs;
    }
    
    static public function GetFileChecksum($ImageTypeID, $WebReferenceNumber, $RecordID, $localfile,$random) {
        return sha1("MECQ;$ImageTypeID;$WebReferenceNumber;$RecordID;$localfile;$random;CSN@ue;05/16/2020");
    }
    
}  
?>
