<?php
$UserEmployeeCode = $APP_SESSION->getEmployeeCode(); 
$searchtext = trim(@$_POST['SearchText']);


$body = '<br>';
$sql = "Usp_AP_FindEmployees '$UserEmployeeCode', '$searchtext', 0";
$searchresults = $APP_DBCONNECTION->execute($sql);
    
$search_columns['EmployeeCodeLink'] = 'Code';
$search_columns['Name']             = 'Name';    
$search_columns['Department']       = 'Department/College';
$search_columns['Campus']           = 'Campus';
$search_columns['Position']         = 'Position';        

if (count($searchresults)==0)
    $searchgrid = '<b>No matching record found!</b>';
else {
    if (count($searchresults) < 51) {
        for ($i=0; $i<count($searchresults); $i++) {
            $employeecode = $searchresults[$i]['EmployeeCode'];
            $searchresults[$i]['EmployeeCodeLink'] = "<a href='javascript:le(\"$employeecode\")'>$employeecode</a>";
        }
        $searchgrid = HTML::datatable('searchtable', $search_columns, $searchresults,'table-sm');
        $body .= $searchgrid;
    }
    else
        $body .= HTML::alert(null,'<b>Too many records found. Please narrow down your search.</b>','info');
}
echo $body;
echo '<script>';
echo "\$('#searchtable').DataTable({responsive:true});";  
echo '</script>';
    
?>