<?php

class CRUDGeneric extends CSNCRUD {
    private $columns = array(); //'[CampusCode;[Campus Code]][Branch]';
        
    function initialize() {
        global $APP_SESSION;
        $this->columns['CampusCode'] = array('Campus Code',array($this,'callback_fetchcampus'));
        //$this->columns['Branch'] = 'Campus';
        $this->semester = $APP_SESSION->getpagesemester();
        $this->searchtextdivsize = 0;
        $this->searchtextrequired = true;
        $this->showtopbuttons = false;
        $this->addtopbutton(1, 'changecampus', 'Change Campus;Change Campus','exchange' ,'warning', $this->opener);
        $this->addtopbuttoncommand('changecampus' , "Usp_AP_GetAllowedCampus '$this->currentuserid'", $this->columns,'Campus','','R',"Usp_AP_GetCampusCode '[@primarykeyvalue]'");        
        $this->recordselector = false;
        $this->primarykeypaddinglength = 0;    
        $this->showactionbuttons = false;
    }
    function callback_beforeshowform(&$html) {
        global $APP_SESSION;
        $APP_SESSION->setCampusCode($this->primarykeyvalue);
        if (isset($_GET['_p7']))
            Tools::redirect($_GET['_p7']);
        else
            Tools::redirect('dashboard');
    }
    
    function callback_fetchcampus($value, $record) {
        return str_replace("</a>",' - ' . $record['Branch']." CAMPUS</a>",$value);
    }

    function callback_fetchurlextra($record, &$urlextra) {
        if (trim($this->opener2))
            $urlextra = $this->opener2;
    }
}

$system = new CRUDGeneric('Campus','CampusCode','changecampus', $APP_SESSION->getuserid(), $APP_CURRENTPAGE);
$system->run();



?>