<?php
$command = @$_GET['_p1'];
if ($command == '')
    $command = 'search';
$EmployeeCode = $APP_SESSION->getEmployeeCode(); 

	$SN	           = '';
	$Lastname	   = '';
	$Firstname	   = '';
	$Middlename    = '';
	$College	   = '';
	$Course	       = '';
	$YearLevel	   = '';
	$AccessCode	   = '';
	$Birthdate     = '';
	$LastSemester  = '';
	$Source   	   = '';
	$NewAccessCode = '';

$activerecord  = array();
$showform      = in_array($command, array('view','edit'));

// load document detail from stored proc
if ($showform) {
    $SN = $_GET['_p2'] + 0;
    $cs = @$_GET['_p3'];
    $st = @$_GET['_p5'];
    if (!Crypto::DTValidateChecksum($SN, $cs)) {
        echo HTML::alert('Error', 'Invalid parameters!' . "[$cs]");
        $command = '';
        $showform = false;
    }
    else {
        $results = $APP_DBCONNECTION->execute("Usp_AP_GetStudentInfo '$EmployeeCode', '$SN'");
        if (!Tools::emptydataset($results)) {
            $activerecord = $results[0];
        }
        else {
            $APP_SESSION->setApplicationMessage('Unable to retrieve student details!'.$results, false, 'danger');
            echo TOOLS::redirect("$APP_CURRENTPAGE/search/$st");
            return;
        }
    }
}

if ( ($command == 'search') && isset($_GET['_p2']) && (count($_POST) == 0))  {
    $activerecord['st'] = @$_GET['_p2'];
}


	
// get form data from $_POST (form)
if (count($_POST)) {
    foreach ($_POST as $key => $value) {
        if (substr($key,-2,2) == '[]')
            $key = substr($key, 0, strlen($key)-2);
        $activerecord[$key] = $value;
    }

	

}

if (!$showform) {
	$searchtext = Data::sanitize(@$activerecord['st']);

	$form = HTML::hforminputtext('st','S.N. or Name', $searchtext, 'Search documents',true,false,false,2,4);

	//$footer = HTML::linkbutton("$APP_CURRENTPAGE/$opener",HTML::icon('arrow-left', 'Go Back'));
	$footer = HTML::submitbutton('Search',HTML::icon('search', 'Search'), 'success');

	echo HTML::box(HTML::icon('search','Search Student'),$form,$footer,'warning');

}

if (count($activerecord)) {	
	$SN	           = Data::sanitize(@$activerecord['SN']);
	$Lastname	   = Data::sanitize(@$activerecord['Lastname']);
	$Firstname	   = Data::sanitize(@$activerecord['Firstname']);
	$Middlename    = Data::sanitize(@$activerecord['Middlename']);
	$College	   = Data::sanitize(@$activerecord['College']);
	$Course	       = Data::sanitize(@$activerecord['Course']);
	$YearLevel	   = Data::sanitize(@$activerecord['YearLevel']);
	$AccessCode	   = Data::sanitize(@$activerecord['AccessCode']);
	$Birthdate     = Data::formatdate(Data::sanitize(@$activerecord['Birthdate']));
	$LastSemester  = Data::sanitize(@$activerecord['LastSemester']);
	$Source   	   = Data::sanitize(@$activerecord['Source']);	
	$NewAccessCode = Data::sanitize(@$activerecord['NewAccessCode']);
}

if (isset($_POST['Save'])) {
	$errormessage = '';
	if ($NewAccessCode == $SN) {
		$errormessage = 'Student Number is not allowed as access code!';
	}
	if ($errormessage == '') {
		$sql = "Usp_UpdateAccessCode '$SN', '$NewAccessCode', 1";
		$results = $APP_DBCONNECTION->execute($sql);
		$APP_SESSION->setApplicationMessage("Access code changed [$Lastname, $Firstname - $SN]");
		Tools::redirect("$APP_CURRENTPAGE/search/$st"); 
	}
	else {
		echo HTML::alert('Attention', $errormessage);
	}
	
}

if (($command == 'search') && isset($_GET['_p2']) ) {
    $searchresults = $APP_DBCONNECTION->execute("Usp_AP_SearchStudents '$EmployeeCode', '$searchtext'");
 
        
    $search_columns['SN']             = 'SN';
    $search_columns['Name']           = 'Name';
    $search_columns['College']        = 'College';    
    $search_columns['Course']         = 'Course';
    $search_columns['CampusCode']     = array('Campus', 'list', array('Manila','Caloocan','UERM','Makati'));
    $search_columns['Active']         = array('Active','yesno','');

    if (!is_array($searchresults)) {
		echo HTML::alert('Error', $APP_DBCONNECTION->errormessage);
	}
    else {
        for ($i=0; $i<count($searchresults); $i++) {
            $sn = trim($searchresults[$i]['SN']);
            $cs = Crypto::DTchecksum($sn) . '/' . mt_rand();
            $searchresults[$i]['SN'] = HTML::link("$APP_CURRENTPAGE/edit/$sn/$cs/$searchtext",$sn,'View Document');        
        }
        $searchgrid = HTML::datatable('searchtable', $search_columns, $searchresults);
		echo HTML::box(HTML::icon('table','Search Results'), $searchgrid,'','success');
    }
}
elseif (($command == 'edit') && $showform) {
	

	
	$body = '';
	$body = '<table class="table table-bordered">';
	$body .= "<tr><td class='col-lg-2'>Course</td><td>$College - $Course</td></tr>";
	$body .= "<tr><td>Year Level</td><td>$YearLevel</td></tr>";
	$body .= "<tr><td>Birthdate</td><td>$Birthdate</td></tr>";
	$body .= "<tr><td>Last Semester</td><td>$LastSemester</td></tr>";
	$body .= "<tr><td>Current Access code</td><td><b>".formataccesscode($AccessCode) . "</b></td></tr>";
	$body .= "<tr><td>Data Source</td><td>$Source</td></tr>";
	$body .= '</table>';
	$body .= '<hr>';
	$body .= HTML::hforminputtext('NewAccessCode','New Access Code', $NewAccessCode, 'New Access Code', true,false,false,2,3);    

	$title = HTML::icon('user')." $Lastname, $Firstname - $SN";
	$footer = HTML::linkbutton("$APP_CURRENTPAGE/search/$st",HTML::icon('arrow-left','Go Back'));
	if ($APP_SESSION->getCanUpdate()) { 
		$footer .= HTML::submitbutton('Save',HTML::icon('floppy-o','Save'), 'warning');	
	}
	
	echo '<form method=post autocomplete=off>';
	echo HTML::box($title, $body, $footer);
	echo '</form>';

	
}


function formataccesscode($accesscode) {
	$s = '';
	for ($i=0; $i<strlen($accesscode); $i++) {
		$s .= '<span class="label bg-red accesscode text-red">' . substr($accesscode,$i,1) . '</span>&nbsp;';
	}
	$s .= ' <a href="#" id="reveal">Reveal Access Code</a>';
	return $s;
}
?>