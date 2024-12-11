
<?php 

// http://localhost:8080/fpdf182/tutorial/tuto5.php
/*
$_SESSION["SN"]
$_COOKIE['PageSemester']
$_SESSION["CampusCode"]
$_SESSION["IDFee"]
$_SESSION["GradFee"]
$_SESSION["College"]
$_SESSION["YearLevel"]
$_SESSION["Course"]
$_SESSION["PaymentMode"]
$_SESSION["BlockCode"]
$_SESSION[selectedCourseYear]
$_SESSION["isOldStudent"]
*/



$sql = APP_DB_DATABASE ."..Usp_WRS_GetRegistration '".$_SESSION["Semester"]."','".$_SESSION["SN"]."',0,0,0";
$results = $APP_DBCONNECTION->execute($sql);
$numrec = count($results);
if ($numrec>0) {
    $_SESSION["PaymentMode"] =$results[0]["PaymentMode"];}
else {$_SESSION["PaymentMode"] ="";}

// // $sql = "[ue database]..Usp_WRS_GetRegistration '".$_SESSION["Semester"]."','".$_SESSION["SN"]."',0";
// $sql = APP_DB_DATABASE.".dbo.usp_GetBreakdownOfFees '".$_COOKIE['PageSemester']."','".$_SESSION["SN"] ."','".$_SESSION["CampusCode"]."','".$_SESSION["College"]."','".$_SESSION["Course"]."','".$_SESSION["YearLevel"]."','O','".$_SESSION["PaymentMode"]."','O','',".$_SESSION["BlockCode"];
// $results1 = $APP_DBCONNECTION->execute($sql);
// $numrec1 = count($results1);

if (trim($_SESSION["Class"]) == "O") {
    $classdescr = "Old Student";
}

if (trim($_SESSION["Class"]) == "F") {
$classdescr = "New Student"; 
}

if (trim($_SESSION["Class"]) == "T") {
    $classdescr = "Transferee";
} 

// Added by Jun - 05/17/2023
$ForeignFee = $results[0]["ForeignFee"] ;   

if ($numrec==0) {
    $h =    '<div class="alert alert-danger" role="alert" style="padding: 15px;height:15px;border-width:0;color:Black;background-color:#ffe6e6;" align="center">
                <span id="">No record/s was found!</span>
             </div>';
    echo $h;

    ?>
    <script>
        setTimeout(fClose,3000);
        function fClose(){
            window.close()
        }
    </script>
    <?php
    return;
}
//require('plugins/fpdf182/rounded_rect.php');
require('plugins/fpdf182/fpdf.php');

class PDF extends FPDF
{
// Page header
function Header()
{
    // Logo
    // $this->Image('logo.png',10,6,30);
    // Arial bold 15
    // $this->SetFont('Arial','B',15);
    // Move to the right
    // $this->Cell(80);
    // Title
    // $this->Cell(30,10,'Title',1,0,'C');
    // Line break
    // $this->Ln(20);
}

// Page footer
 function Footer()
 {
//     // Position at 1.5 cm from bottom
     $this->SetY(-15);
     $this->SetTextColor(0,0,0);
    //  $this->SetDrawColor(0,0,0);
    //  $this->SetLineWidth(.5);
    //  $this->Line(8,262,80,262);
    //  $this->Line(130,262,200,262);
if ($this->PageNo()>1) {
    $this->Cell(21,7,'STUDENT NO.:',0,0,'R');
    $this->Cell(25,7,$_SESSION["SN"].'',0,0,'LR');
    $this->Cell(20,7,'NAME : ',0,0,'R');
    $this->Cell(52,7,$_SESSION["Name"],0,0,'LR');
    $this->Cell(20,7,'COURSE : ',0,0,'R');
    $this->Cell(20,7,$_SESSION["Course"],0,0,'LR');
    $this->Cell(20,7,'YEAR LEVEL : ',0,0,'R');
    $this->Cell(10,7,$_SESSION["YearLevel"],0,1,'LR');
    
    

}     
     $this->Cell(170,8,'   ----------------------------------------                              ----------------------------------------',0,1);
     $this->Cell(170,0,"          Enrollment Faculty Adviser                                                 Student Signature       ",0,1);
     
//     // Arial italic 8
     $this->SetFont('Arial','I',8);
    // Page number
// $this->Cell(0,10,'Page '.$this->PageNo().'/2',0,0,'R');
// Instanciation of inherited class

}

function RoundedRect($x, $y, $w, $h, $r, $style = '')
{

    $k = $this->k;
    $hp = $this->h;
    if($style=='F')
        $op='f';
    elseif($style=='FD' || $style=='DF')
        $op='B';
    else
        $op='S';
    $MyArc = 4/3 * (sqrt(2) - 1);
    $this->_out(sprintf('%.2F %.2F m',($x+$r)*$k,($hp-$y)*$k ));
    $xc = $x+$w-$r ;
    $yc = $y+$r;
    $this->_out(sprintf('%.2F %.2F l', $xc*$k,($hp-$y)*$k ));

    $this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);
    $xc = $x+$w-$r ;
    $yc = $y+$h-$r;
    $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-$yc)*$k));
    $this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);
    $xc = $x+$r ;
    $yc = $y+$h-$r;
    $this->_out(sprintf('%.2F %.2F l',$xc*$k,($hp-($y+$h))*$k));
    $this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);
    $xc = $x+$r ;
    $yc = $y+$r;
    $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$yc)*$k ));
    $this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
    $this->_out($op);
}

function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
{
    $h = $this->h;
    $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $x1*$this->k, ($h-$y1)*$this->k,
        $x2*$this->k, ($h-$y2)*$this->k, $x3*$this->k, ($h-$y3)*$this->k));
}
}

// $pdf = new PDF_AutoPrint();
$pdf = new PDF('p','mm','Legal');    

$pdf->SetDrawColor(220,53,69);
$pdf->AliasNbPages('{pages}');
$pdf->AddPage();
$pdf->SetAutoPageBreak(true);
$pdf->SetMargins(5,5,5,20);
$pdf->SetTitle("Pre-Enrolment Form");
// $pdf->Ln(5);

// $pdf->SetY(-15);
$pdf->SetFont('Arial','',8);
// $pdf->Cell(0,10,'Page '.$pdf->PageNo().". / {pages}",0,0,'C');

$pdf->SetFont('Arial','B',12);
$pdf->Cell(80);
$pdf->SetTextColor(220,53,69);
$pdf->Cell(33,3,'UNIVERSITY OF THE EAST',0,1,'C');
$pdf->SetFont('Arial','',10);
// $pdf->Cell(80);
$pdf->Cell(200,5,$results[1]['CollegeDesc'],0,1,'C');
if (trim($results[1]['CampusAccount']) == "MLA") {
    $pdf->Cell(200,5,'MANILA CAMPUS',0,1,'C');                  
} else {
    $pdf->Cell(198,5,'CALOOCAN CAMPUS',0,1,'C');                  
}
$pdf->SetFont('Arial','B',10);
$pdf->Cell(200,10,'P R E - E N R O L L M E N T   F O R M   ( P E F )',0,1,'C');
$pdf->SetFont('Arial','',10);
// $pdf->Cell(200,0,$results[1]['SemDescr'],0,1,'C');
// $pdf->SetTextColor(0,0,0);
$pdf->Cell(200,0,getyrsemdescription($_SESSION["Semester"]),0,1,'C');
$pdf->Ln(6);
$pdf->SetFont('Courier','B',8);
$pdf->SetTextColor(220,53,69);
$pdf->Cell(21,7,'STUDENT NO.',0,0,'R');
$pdf->SetFont('Courier','',8);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(52,7,':  '.$_SESSION["SN"].'',0,0,'LR');
$pdf->SetFont('Courier','B',8);
$pdf->SetTextColor(220,53,69);
$pdf->Cell(42,7,'COURSE/MAJOR',0,0,'R');
$pdf->SetFont('Courier','',8);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(32,7,': '.$_SESSION["Course"].'',0,0,'LR');
$pdf->SetFont('Courier','B',8);
$pdf->SetTextColor(220,53,69);
$pdf->Cell(37,7,' CURRICULUM : ',0,0,'R');
$pdf->SetFont('Courier','',8);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(32,7,$results[0]["Curriculum"].'',0,0,'LR');
$pdf->Cell(32,7,'',0,1,'LR');

$pdf->SetFont('Courier','B',8);
$pdf->SetTextColor(220,53,69);
$pdf->Cell(184,2,'LRN : ',0,0,'R');
$pdf->SetFont('Courier','',8);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(32,2,trim($_SESSION["LRN"]),0,0,'LR');
$pdf->Cell(32,2,'',0,1,'LR');


$pdf->SetFont('Courier','B',8);
$pdf->SetTextColor(220,53,69);
$pdf->Cell(26,7,'NAME       : ',0,0,'R');
$pdf->SetFont('Courier','',8);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(52,7,$_SESSION["Name"],0,0,'LR');
$pdf->SetFont('Courier','B',8);
$pdf->SetTextColor(220,53,69);
$pdf->Cell(41,7,'YEAR :',0,0,'R');
$pdf->SetFont('Courier','',8);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(28,7,trim($_SESSION["YearLevel"]),0,0,'L');
$pdf->SetFont('Courier','B',8);
$pdf->SetTextColor(220,53,69);
$pdf->Cell(37,7,'CLASS : ',0,0,'R');
$pdf->SetFont('Courier','',8);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(32,7,$classdescr,0,1,'LR');
$pdf->SetFont('Courier','B',8);
$pdf->SetTextColor(220,53,69);
$pdf->Cell(26,7,'ADDRESS    : ',0,0,'R');
$pdf->SetFont('Courier','',8);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(92,7,substr($results[0]["Address"],0,77),0,0,'LR');
$pdf->SetFont('Courier','B',8);
$pdf->SetTextColor(220,53,69);
$pdf->Cell(66,7,' BIRTHDATE : ',0,0,'R');
$pdf->SetFont('Courier','',8);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(32,7,date("m/d/Y",strtotime($_SESSION["Birthdate"])),0,1,'LR');
$pdf->SetLeftMargin(5);

$pdf->Ln(2);
$pdf->SetFont('Courier','B',8);
$pdf->SetTextColor(220,53,69);
$pdf->Cell(100,6,'SUBJECT DESCRIPTION',1,0,'C');
$pdf->Cell(20,6,'SUBJ CODE',1,0,'C');
$pdf->Cell(26,6,'SECTION',1,0,'C');
// $pdf->Cell(10,6,'UNITS',1,0,'C');
$pdf->Cell(20,6,'DAYS',1,0,'C');
$pdf->Cell(20,6,'TIME',1,0,'C');
$pdf->Cell(20,6,'ROOM',1,1,'C');
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Courier','',8);
$i=0;
$Discount=0;
$Total=0;
$Downpayment=0;
$num=$numrec;
$isCash=0;
for ($i=0;$i<$num;$i++) { 

    $pdf->Cell(100,5,substr($results[$i]['Description'],0,58),'R',0,'L');
    $pdf->Cell(20,5,$results[$i]['SubjectCode'],'LR',0,'L');
    if ( trim($results[$i]['Section']) != "Total") {
        $pdf->Cell(26,5,$results[$i]['Section'],'LR',0,'C');
    } else {
        $pdf->Cell(26,5,"",'LR',0,'L');
    }
    if ( trim($results[$i]['Account']) == 'LESS: CASH DISCOUNT') {
        $Discount = $results[$i]['Amount'];
    }

    if ( trim($results[$i]['Account']) == 'UPON REGISTRATION') {
        $Downpayment = $results[$i]['Amount'];
    }

    // $pdf->Cell(10,5,$results[$i]['Units'],'LR',0,'L');
    $pdf->Cell(20,5,$results[$i]['Days'],'LR',0,'C');
    $pdf->Cell(20,5,$results[$i]['Time'],'LR',0,'C');
    $pdf->Cell(20,5,$results[$i]['Room'],'L',1,'L');

    // if ($num<>$numrec) {
    //     $num=$i;
    //     $i=$numrec;
    // }

    if (substr_count($results[$i]['Description'],'basiced') > 0){
        $num=$i+1;
    }   
    
}
// $numrec=$num;

// $pdf->Cell(90,5,'','',0,'C');
// $pdf->Cell(20,5,'','LR',0,'C');
// $pdf->Cell(26,5,'','LR',0,'C');
// $pdf->Cell(10,5,'','LR',0,'C');
// $pdf->Cell(20,5,'','LR',0,'C');
// $pdf->Cell(20,5,'','LR',0,'C');
// $pdf->Cell(20,5,'','T',1,'C');
$n = $num * 5 + 2;
$pdf->RoundedRect(5, 38, 206, 30 + $n, 3.50, '');
$pdf->SetDrawColor(255,255,255);
$pdf->SetFont('Arial','B',8);
$pdf->Ln(10);
$pdf->Cell(100,4,'SCHOOL FEES');
$pdf->Cell(100,4,'AMOUNT',0,1,'R');
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Courier','',8);
$pdf->Ln(2);
$i=0;
$Total=0;
$ctrLast=0;
for ($i=0;$i<$numrec;$i++) { 
    if ($results[$i]['Account'] == 'TOTAL') {
        $pdf->SetFont('Courier','B',8);
        $pdf->Cell(100,5,$results[$i]['Account'],'L',0,'L');
        $pdf->Cell(100,5,number_format($results[$i]['Amount'],2),0,1,'R');
        $Total=$results[$i]['Amount'];
        $pdf->SetFont('Courier','',8);
    }
    else {
        $pdf->Cell(100,5,$results[$i]['Account'],'L',0,'L');
        if (trim($results[$i]['Account'])=='') {
            $pdf->Cell(100,5,'',0,1,'R');}
        else {
            if ($results[$i]['Amount']==0){
                $pdf->Cell(100,5,'',0,1,'R');
            }
            else {
            $pdf->Cell(100,5,number_format($results[$i]['Amount'],2),0,1,'R');}}
    }
    if ((substr($_SESSION["PaymentMode"],0,1) == 'C' && substr_count($results[$i]['Account'],'AMOUNT TO PAY') > 0) || $ctrLast == 2 ){
    break;
    } 

    if ($results[$i]['Amount']==0){
        $ctrLast = $ctrLast + 1;
    }

}

// $pdf->SetFont('Courier','B',8);
// $pdf->Cell(100,5,'TOTAL AMOUNT:   ','L',0,'L');
// $pdf->SetFont('Courier','',8);
// $pdf->Cell(100,5,number_format($Total,2),0,1,'R');
// $nCount = substr($_SESSION["PaymentMode"],1,1);
// if (substr($_SESSION["PaymentMode"],0,1)=="I") {
//     $pdf->Ln(5);
    
//     $pdf->Cell(100,4,'DOWN PAYMENT',0,0,'L');
//     $pdf->Cell(100,5,number_format($results1[1]['DownPayment'],2),0,1,'R');

//     $i=0;
//     $Total=0;
//     for ($i=1;$i<=$nCount;$i++) {
//         $pdf->Cell(100,5,'Installment '.$i,'L',0,'L');
//         $pdf->Cell(100,5,number_format($results1[1]['Prelim'],2),0,1,'R');
//         $Total=$Total +$results1[1]['Prelim'];
//     }   
// }

// $pdf->Ln();
$pdf->Cell(100,4,'To complete the enrollment process, please do the following steps:',0,1);
$pdf->Ln();
$pdf->Cell(20,4,'1. Indicate the mode of payment:',0,1);
$pdf->Cell(65,4,'   [ ] FULL PAYMENT (CASH/CHECK) ',0,0,'');
$pdf->Cell(20,4,'Amount P:    ',0,0,'L');
if ($Discount > 0) { 
    $pdf->Cell(15,4,number_format($Total,2),0,0,'R');
    $pdf->Cell(20,4,'  Discounted P: ',0,0,'L');
    $pdf->Cell(25,4,number_format($Total - $Discount,2),0,1,'R');
}
else {$pdf->Cell(15,4,number_format($Total,2),0,1,'R');}

$pdf->Cell(65,4,'   [ ] Down Payment (INSTALLMENT)','',0,'L');
$pdf->Cell(15,4,'Amount P:     ',0,0,'L');
if (substr($_SESSION["PaymentMode"],0,1) == 'I') {
    $pdf->Cell(20,4,number_format($Downpayment,2),0,1,'R');
}
    else {
        if (substr($_SESSION["PaymentMode"],0,1) == 'C') {
            $pdf->Cell(15,4,'             '.$results[0]['DiscountedRemarks'],0,1,'L');
        }
    else {       
    $pdf->Cell(15,4,'',0,1,'L');    }
    }

// $pdf->Cell(20,4,number_format($results[0]['UponRegistration'],2),0,1,'R');
$pdf->Cell(65,4,'   [ ] OTHERS*',0,0);
$pdf->Cell(15,4,'Amount P:    ',0,1,'L');
// NEW FOREIGN FEE
if ($ForeignFee  > 0) {
    $pdf->Cell(15,4,'Amount P:    ',0,1,'L');
}
$pdf->Ln();                                                               
$pdf->Cell(85,4,'   This Pre-Enrollment Form (PEF) is valid until.  ',0,0);
$pdf->SetFont('Courier','B',8);
$pdf->Cell(14,4,date("m/d/Y",strtotime($results[0]['Valid_Until'])),0,0);
$pdf->SetFont('Courier','',8);
$pdf->Cell(14,4,'   Failure to pay on the date stated herein will',0,1);
$pdf->Cell(100 ,4,'   invalidate the encoded subjects.',0,1);
$pdf->Ln();
$pdf->Cell(100 ,4,'   *Others: Recipients of Scholarships, Grants-in-Aid and School Benefits',0,1);
$pdf->Ln();
$pdf->Cell(100 ,4,'2. Proceed to the UNIVERSITY CASHIER/TELLER or any PHILIPPINE NATIONAL BANK (PNB) branch or log-in at ',0,1);
$pdf->Cell(100 ,4,'   www.bancnetonline.com for payment.              ',0,1);
$pdf->Ln();
$pdf->Cell(100 ,4,'   (For payment thru bank or bancnetonline, get the official receipt at the University Cashier/Teller on the next',0,1);
if (trim($results[1]['CampusAccount']) == "MLA") {
    $pdf->Cell(100 ,4,'    working day. For recipients of scholarships or grants-in-aid, proceed to Window "24" or "26" of the Comptroller`s',0,1);
} else {
    $pdf->Cell(100 ,4,'    working day. For recipients of scholarships or grants-in-aid, proceed to window of the Comptroller`s ',0,1);
}

$pdf->Cell(100 ,4,'    Office. For recipients of school benefits, proceed to the Department of Human Resources and Development (DHRD).',0,1);
$pdf->Ln();
$pdf->Cell(100,4,'3. Present the official receipt at the I.T. Department for the printing of the Official Registration Form.',0,1);                    
// d$pdf->Cell(180,4,'   of the Official Registration Form.',0,1);
// $pdf->Ln(10);

// $pdf->AutoPrint(false);
// $pdf->Ln(20);
// $pdf->Cell(170,8,'   ----------------------------------------                              ----------------------------------------',0,1);
// $pdf->Cell(170,0,"          Enrollment Faculty Adviser                                                 Student Signature       ",0,1);

$pdf->Output();



function getyrsemdescription($yrsem)
{
    $yrsem = trim($yrsem);
    $sem = substr($yrsem,-1,1);
    $sem2 = substr($yrsem,0,4) + 1;
    if ($sem == "0"){
        $result = "Summer " . substr($yrsem,0,4);
    }
    else {
        $result = "School Year " . substr($yrsem,0,4) . "-" . $sem2;
    }
    return $result;
}

?>

