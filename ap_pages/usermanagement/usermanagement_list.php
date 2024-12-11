<?php
$users = $APP_DBCONNECTION->execute("Usp_AP_GetUsers '', '" . APP_MODULENAME . "'");
if (Tools::emptydataset($users))
    $users = array();

for ($i=0; $i<count($users); $i++) {
    $employeecode = $users[$i]['EmployeeCode'];
    $cs = Crypto::ECchecksum($employeecode) . '/' . mt_rand();
    $users[$i]['EmployeeCodeLink'] = HTML::link("$APP_CURRENTPAGE/view/$employeecode/$cs",$employeecode,'View Access');        
}    

$columns['EmployeeCodeLink'] = 'Employee Code';
$columns['Department']       = 'Department';    
$columns['Name']             = 'Name';
$columns['Campus']           = 'Campus';
$columns['DateCreated']      = array('Date Created', 'date', 'm/d/Y h:i');
$columns['LastUpdate']       = array('Last Update', 'date', 'm/d/Y h:i');

$userstable = HTML::datatable('users',$columns,$users);
echo HTML::box(HTML::icon('table', 'List of Users'), $userstable,'','primary', true, false, 'table-responsive');

$searchtext = Data::sanitize(@$_POST['st']);

$form = HTML::hforminputtext('st','Search', $searchtext, 'Search by name or code',true,false,false,1,4);
$footer = HTML::submitbutton('Search',HTML::icon('search', 'Search'), 'success');

echo '<form class="form-horizontal" method="post">';
echo HTML::box(HTML::icon('search','Search Employee/Faculty'),$form,$footer,'warning',false,false,'');
echo '</form>'; 

if (isset($_POST['st'])) {
    $sql = "Usp_AP_FindEmployees '$EmployeeCode', '$searchtext'";
    $searchresults = $APP_DBCONNECTION->execute($sql);
        
    $search_columns['EmployeeCodeLink'] = 'Code';
    $search_columns['Name']             = 'Name';    
    $search_columns['Department']       = 'Department/College';
    $search_columns['Campus']           = 'Campus';
    $search_columns['Actions']          = 'Actions';  

    if (count($searchresults)==0)
        $searchgrid = '<br><b>No matching record found!</b>';
    else {
        for ($i=0; $i<count($searchresults); $i++) {
            $employeecode = $searchresults[$i]['EmployeeCode'];
            $cs = Crypto::ECchecksum($employeecode) . '/' . mt_rand();
            $searchresults[$i]['EmployeeCodeLink'] = HTML::link("$APP_CURRENTPAGE/view/$employeecode/$cs",$employeecode,'View Access');        
        }
        $searchgrid = HTML::datatable('searchtable', $search_columns, $searchresults);
    }

    echo HTML::box(HTML::icon('table','Search Results'), $searchgrid,'','success');
}



?>
