<!-- https://sweetalert.js.org/guides/ -->
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<?php
include_once("class.php");

$selectedCourseYear = $_SESSION["selectedCourseYear"];
$CourseYear = new JunClass('','',$selectedCourseYear);
$_SESSION["College"] = $CourseYear->getCollege();
$_SESSION["YearLevel"] = $CourseYear->getYear();
$_SESSION["Course"] =  $CourseYear->getCourse();

$sql = APP_DB_DATABASE.".dbo.usp_GetTuitionMiscFee '".$_COOKIE['PageSemester']."','".$_SESSION["YearLevel"]."','".$_SESSION["College"]."','".$_SESSION["CampusCode"]."'";
//  echo $sql.'<br><br>';
$html=""; 
$html2=""; 
// $html .= "<div class='container col-sm-12' ><div class='row'> <div class='col-sm-6'>";


//--------------------------------------------------
$results = $APP_DBCONNECTION->execute($sql);
//    print_r($results);
$html2 .=   "<table id='FeesTable' class='table table-striped table-hover table-condensed success style=' scrollCollapse: true; position: relative; overflow: auto; width: 100%;'><thead><tr  style='background-color: #dc3545 ; color: white '>".
            "<th class='col-sm-4'>Description</th><th style='text-align: right; padding-right: 40px;'>Amount</th></thead>".
            "<tbody id='BreakDownOfFees'>";
    //$html2 .=   "<tr><td></td><td></td></tr>";
    $total=0;

    if ( $_SESSION["RecordCount"] == 0) { //(Tools::emptydataset($results)) ||
        $msg="";
        
        $html2 .=   "<tr><td>Tuition</td><td style='text-align: right;  padding-top:0; padding-right: 20px;'>".number_format(0,2)."</td></tr>";
        }
    else {
        $numrec = count($results);

        for ($i=0;$i<$numrec;$i++) { 
            $total += $results[$i]['Amount'];
            $html2 .=   "<tr><td>".$results[$i]['Description']."</td><td style='text-align: right;  padding-right: 20px;'>".number_format($results[$i]['Amount'],2)."</td></tr>";
        }
    }

$html2 .= "<tr class='bg-blue'><td>TOTAL</td><td style='text-align: right; padding-right: 20px;'>".number_format($total,2)."</td></tbody></table>";

// select * from [dbo].[Ufx_WRS_GetAssessment]('20191', '', '1', 'Y','ELEM','2', 'O','I', 'O','P',500,0,0,13,2423321,2423331,2423341,2423351,2423361,2423371,2423381,2423391,2423401,2423411,2423421,2423431,2423441,0,0,0,0,0,0,0,'')
// $html .= HTML::box('Breakdown of Fees', $html, '', 'warning',true,false,'table-responsive');
// $html .= "</div><div class='col-sm-6'>";
//--------------------------------------------------

$sql = APP_DB_DATABASE.".dbo.usp_GetBreakdownOfFees '".$_COOKIE['PageSemester']."','".$_SESSION["SN"] ."','".$_SESSION["CampusCode"]."','".$_SESSION["College"]."','".$_SESSION["Course"]."','".$_SESSION["YearLevel"]."','O','".$_SESSION["PaymentMode"] ."','O','',".$_SESSION["BlockCode"];
// echo $sql;
// return;
$results1 = $APP_DBCONNECTION->execute($sql);

$_SESSION["IDFee"] = 0;
$_SESSION["GradFee"] = 0;
//40102080000		Graduation Fee
//40102050000		ID Fee
// margin-top: 7px;
$html3 =    "<table class='table table-striped table-hover table-condensed success class='col-sm-6' style=' scrollCollapse: true; position: relative; overflow: auto; width: 100%;'><thead><tr  style='background-color: #dc3545 ; color: white;'>".
            "<th>Description</th><th style='text-align: right; padding-right: 40px;'>Amount</th></thead>".
            "<tbody id='Distribution'>";
            
if ($total==0) {
    $html3 .=   "<tr><td>Downpayment</td><td style='text-align: right; padding-right: 20px;'>".number_format(0,2)."</td></tr>";

} else {
    $numrec = count($results1);
    $total=0;
    $i=0;
    
    for ($i=0;$i<$numrec;$i++) { 
        if ($results1[$i]['AccountCode'] == "40102050000") {$_SESSION["IDFee"] = $results1[$i]['Amount'];}
        if ($results1[$i]['AccountCode'] == "40102080000") {$_SESSION["GradFee"] = $results1[$i]['Amount'];}
        $total += $results1[$i]['Amount'];
        $html3 .=   "<tr><td>".$results1[$i]['Account']."</td><td style='text-align: right;  padding-right: 20px;'>".number_format($results1[$i]['Amount'],2)."</td></tr>";
    }
    $html3 .= "<tr class='bg-blue'><td>TOTAL FEES</td><td style='text-align: right;  padding-right: 20px;'>".number_format($total,2)."</td>";
    
    $html3 .= "</tbody></table>";
}

$cash = ($_SESSION["PaymentMode"]=="C") ? "selected" : "";
$installment3 = ($_SESSION["PaymentMode"]=="I3") ? "selected" : "";
$installment5 = ($_SESSION["PaymentMode"]=="I5") ? "selected" : "";
$installment7 = ($_SESSION["PaymentMode"]=="I7") ? "selected" : "";
$installment9 = ($_SESSION["PaymentMode"]=="I9") ? "selected" : "";

// $html .= HTML::box('Payment Distribution', $html3, '', 'success',true,false,'table-responsive');
echo '<br>'.$html3;


if ($total>0) {
    $html .= "<div id='divElement' class='container col-sm-12' >
                </div>";
    // $html .= "<div class='container-fluid'>
    //             echo alert('<button type='button' class='close' data-dismiss='alert'>&times;</button><i class='fa'>&#xf071;</i> No record found')
    //            </div> " ;                

    $html .= "<div id='Paymentmode' show>
                <div class='col-sm-12'> 
                <label style='margin-top: 6px;padding-right: 0px; margin-right:0px;' >MODE OF PAYMENT:</label>
                <select class='form-control' id='list' name='list'  onchange=getSelectedValue()".' style="margin-top: 6x; margin-right: 10px; padding-left: 0px;">'."
                        <option value='C'  ". $cash ."  >CASH / FULL PAYMENT</option>
                        <option value='I3' ". $installment3 ." >3 INSTALLMENTS</option>
                        <option value='I5' ". $installment5 ." >5 INSTALLMENTS</option>
                        <option value='I7' ". $installment7 ." >7 INSTALLMENTS</option>
                        <option value='I9' ". $installment9 ." >9 INSTALLMENTS</option>
                </select> 

                <a class='btn btn-primary' title='' " . (($_SESSION["Date_Validated"] <> NULL) ? "'disabled' onclick=SaveEnrolment(1) ": "'enabled' onclick='SaveEnrolment(0)'") . " style='margin-top:10px'> <i title='' class='fa fa-save'></i> Save</a>
                <a class='btn btn-success' title='' onclick='PrintPEF()' style='margin-top:10px'> <i title='' class='fa fa-print'></i> Print</a>
                <label id='Save' hidden></label>
                </div>
                </div>";
}

echo $html;
//echo HTML::button('Save',HTML::icon('fa-save','Save'),'danger','submit');
$h = "<div id='divElement' class='container col-sm-10' style='visibility:visible' >
<div class='alert alert-success alert-dismissible fade show d-print-none' >
<button type='button' class='close d-print-none' data-dismiss='alert'>&times;</button>
<strong>Schedule has been saved !!!</strong>
</div></div>";
?>


<script>
function getSelectedValue()
{
    var getRandom = Math.floor(Math.random() * 1000);
    var selectedValue = document.getElementById("list").value;
    //  alert(selectedValue);

    //$('#Distribution').hide();
     $('#Distribution').load(
         "<?php echo APP_BASE ?>fPaymentDistribution/plain",{PayMode:selectedValue,cs:getRandom}
     );
}

function SaveEnrolment(isValidated)
{
    
    if (isValidated==1) {
        alert("Already validated! Saving cancelled.");
        return;
    }

    var getRandom = Math.floor(Math.random() * 1000);
   
    $('#Save').load(
        
         "<?php echo APP_BASE ?>SaveEnrolment/plain",{Random:getRandom}
     );

    //  swal("Schedule has been saved !!!","","success");
    //  document.getElementById("divElement").innerHTML = "<div class='alert alert-success alert-dismissible show fade d-print-none'><button type='button' class='close d-print-none' data-dismiss='alert'>&times;</button><strong>Schedule has been saved !!!</strong></div>"

}


function showdiv()
    {
        document.getElementById("divElement").style.visibility="visible";
        // setTimeout("showdiv()",5000);
    }
        

function hidediv()
    {

        document.getElementById("divElement").style.visibility="hidden";
        setTimeout("hidediv()",4000);
    }
function PrintPEF()
{
    //alert('Hello');
    
    window.open("<?php echo APP_BASE ?>pefKtoG10/plain");
    
    //myWindow.document.write("<h1>This is 'myWindow'</h1>");
     //$('#Save').load("< ?php echo APP_BASE ?>pefKtoG10/plain");
    
}

        

</script>


<script>
 $(document).ready(function() {
     $('#FeesTable').DataTable( {
         "scrollY":        "200px",
         "scrollCollapse": true,
         "paging":         false,
         "searching": false,
         "ordering": false
         
     } );
 } );

 var getRandom = Math.floor(Math.random() * 1000);
    var selectedValue = "<?php echo $_SESSION["PaymentMode"]; ?>";
    // alert(selectedValue);
    $('#Distribution').load(
        "<?php echo APP_BASE ?>fPaymentDistribution/plain",{PayMode:selectedValue,cs:getRandom}
    );

</script>         