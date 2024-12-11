<?php 
include("class.php");
$selectedCourseYear = $_SESSION["selectedCourseYear"];
$CourseYear = new JunClass('','',$selectedCourseYear);
$_SESSION["College"] = $CourseYear->getCollege();
$_SESSION["YearLevel"] = $CourseYear->getYear();
$_SESSION["Course"] =  $CourseYear->getCourse();

//unset($_SESSION['RecordCount']);

$recordcount = 0;
$_SESSION["PaymentMode"] = $_POST["PayMode"];
// echo "<pre>".$_SESSION["PaymentMode"]."</pre>";
$html = "";
// if (!Crypto::ValidateGenericChecksum($_POST['PayMode'], $_POST['cs'])) {
//     $_SESSION["RecordCount"]=0;
//     echo "<tr><td colspan=8 > Invalid Parameters! </td></tr>";
//     return;

// }
    // $html .=   "<tr><td style='text-align: center'>".
    //             "<td> test1</td>".
    //             "<td> test2</td></tr>";

    
$PM = '';
if ($_SESSION["PaymentMode"]=="I") { $PM = "INSTALLMENT"; } ELSE {$PM = "CASH";}

//ECHO $PM;
// $html2=""; 
//  $html .=  '<table id="Distribution" class="table table-striped table-hover table-condensed success" style="margin-top: 7px;max-height: 300px;">
//          <thead><tr style="background-color: #dc3545 ; color: white;">
//             <th>Description</th><th style="text-align: right; padding-right: 40px;">Amount</th></tr></thead>

$sql = APP_DB_DATABASE.".dbo.usp_GetBreakdownOfFees '".$_COOKIE['PageSemester']."','".$_SESSION["SN"] ."','".$_SESSION["CampusCode"]."','".$_SESSION["College"]."','".$_SESSION["Course"]."','".$_SESSION["YearLevel"]."','O','".$_SESSION["PaymentMode"]."','O','',".$_SESSION["BlockCode"];

//  echo "<pre>";
//  echo $sql;
//  echo "</pre>";
// return;
$results1 = $APP_DBCONNECTION->execute($sql);
$numrec = count($results1);
$total=0;
$i=0;
$_SESSION["IDFee"] = 0;
$_SESSION["GradFee"] = 0;
for ($i=0;$i<$numrec;$i++) { 
    if ($results1[$i]['AccountCode'] == "40102050000") {$_SESSION["IDFee"] = $results1[$i]['Amount'];}
    if ($results1[$i]['AccountCode'] == "40102080000") {$_SESSION["GradFee"] = $results1[$i]['Amount'];}
    $total += $results1[$i]['Amount'];
    $html .=   "<tr><td>".$results1[$i]['Account']."</td><td style='text-align: right;  padding-right: 20px;'>".number_format($results1[$i]['Amount'],2)."</td></tr>";
}
$html .= "<tr class='bg-blue'><td>TOTAL FEES</td><td style='text-align: right;  padding-right: 20px;'>".number_format($total,2)."</td>";

if (substr($_SESSION["PaymentMode"],0,1)=="I") {
    $html .= "<tr><td>DOWNPAYMENT</td><td style='text-align: right;  padding-right: 20px;'>".number_format($results1[1]['DownPayment'],2)."</td></tr>";
    
    $nCount = substr($_SESSION["PaymentMode"],1,1);
    $i = 1;
    for ($i=1;$i<=$nCount;$i++) {
        $html .="<tr><td>Installment ".$i."</td><td style='text-align: right;  padding-right: 20px;'>".number_format($results1[1]['Prelim'],2)."</td></tr>"; 
    }
}

$creditbalance = $results1[1]['CreditBalance'];
$totaldiscount  = $results1[1]['TotalDiscount'];
$amounttopay = $results1[1]['DownPayment'];
if ($creditbalance > 0) {
    $amounttopay -=  $creditbalance;
    $html .= "<tr><td>LESS: CREDIT BALANCE</td><td style='text-align: right;  padding-right: 20px;'>".number_format($creditbalance,2)."</td></tr>";
}
if ($totaldiscount) {
    $amounttopay -=  $totaldiscount;
    $html .= "<tr><td>LESS: DISCOUNT <small>(Not applicable to credit card payments)</small></td><td style='text-align: right;  padding-right: 20px;'>".number_format($totaldiscount,2)."</td></tr>";
}
$html .= "<tr class='bg-blue'><td>UPON REGISTRATION:</td><td style='text-align: right;  padding-right: 20px;'>".number_format($amounttopay,2)."</td></tr>";
if ($amounttopay <= 0) {
    $remarks = trim(($totaldiscount ? 'Discount' : '') . ' ' . ($creditbalance ? 'Credit Balance' : ''));
    if ($remarks)
        $remarks .= ' applied.';
    $html .= "<tr class='bg-red'><td colspan=2><b>ATTENTION: $remarks Registration will be automatically validated after save/print. Registration can not be edited once validated.</b></td></tr>";
}
$html .= "</tbody></table>";

echo $html; 

// $html2 .=  "<table class='table table-striped table-hover table-condensed success'><thead><tr  style='background-color: #dc3545 ; color: white '>".
//     "<th>Description</th><th style='text-align: right; padding-right: 40px;'>Amount</th></thead>".
//     "<tbody id='Distribution'>";
//     //$html2 .=   "<tr><td></td><td></td></tr>";
//     $total=0;

       
//         $html2 .=   "<tr><td>Testing</td><td>123 </td></tr>";


// $html2 .= "</tbody></table>";

 //$html .= HTML::box('Payment Distribution', $html, 'Box Footer', 'warning',true,false,'table-responsive');

 
//echo '<pre>'; 
// print_r($APP_SESSION)."<br/>";       
// Testing Data
//phpinfo(INFO_VARIABLES);
//echo APP_BASE;
//echo '<pre>'; 
// echo $_SESSION["Mymodule"]="Applicant's K-G10"."<br/>";

//print_r($_SESSION)."<br/>";
//print_r($APP_SESSION)."<br/>";
//echo '</pre>';
//ECHO $_COOKIE['PageSemester']."<br/>";

?>