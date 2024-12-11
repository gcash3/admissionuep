
<?php 
 if(!$_POST["Random"]){
    return;
 }

 
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
$_SESSION["Class"]
$_SESSION["Name"]
*/

// check session variables before saving to prevent error
// 05/24/2024-CSN
foreach (explode(',','WebReferenceNumber,SN,CampusCode,College,Course,YearLevel,Class,PaymentMode,BlockCode') as $sessionname) {
    if (@$_SESSION[$sessionname] == '') {
        echo "<script>alert('Unable to save student registration. Please reload page.')</script>";
        return;
    }
}


$IPAddress = DATA::get_actualip();

$sql = APP_DB_DATABASE.".dbo.usp_SaveOnlineBasicEd '".$_COOKIE['PageSemester']."','".$_SESSION["WebReferenceNumber"]."','".trim($_SESSION["SN"])."','".$_SESSION["CampusCode"]."','".$_SESSION["College"]."','".$_SESSION["Course"]."','".$_SESSION["YearLevel"]."','".$_SESSION["Class"]."','".$_SESSION["PaymentMode"]."','O','',".$_SESSION["BlockCode"].",'".$IPAddress."'";
//$sql = APP_DB_DATABASE.".dbo.usp_SaveOnlineBasicEd '".$_COOKIE['PageSemester']."','".$_SESSION["WebReferenceNumber"]."','".$_SESSION["SN"]."','".$_SESSION["CampusCode"]."','".$_SESSION["College"]."','".$_SESSION["Course"]."','".$_SESSION["YearLevel"]."','".$_SESSION["Class"]."','".$_SESSION["PaymentMode"]."','O','',".$_SESSION["BlockCode"].",'".$IPAddress."'";

$APP_DBCONNECTION->begintransaction();
$results = $APP_DBCONNECTION->execute($sql);
$random = $_POST["Random"];
$saved = !Tools::emptydataset($results);

// echo '<pre>',print_r($sql), '</pre>';
//  ? > <script> console.log ("<?php echo $sql ? >"); </script> <?php

if (count($results)>0) {
    $APP_DBCONNECTION->commit();
    if (@$results[0]['Message'] <> '') {
        echo '<script type="text/javascript">';
        echo ' alert("'.@$results[0]['Message'].'")';  //not showing an alert box.
        echo '</script>';
        }
    else {

        ?><script> 
            alert("Saved..");
            var vSN = document.querySelector("#StudNo")
            vSN.textContent = "<?php echo $_SESSION["SN"]; ?>";
        </script> <?php

    $sql = APP_DB_DATABASEADMISSION.".dbo.usp_getWebApplications '".$_SESSION["WebReferenceNumber"]."'";
    $results = $APP_DBCONNECTION->execute($sql);
    $_SESSION["SN"] =  $results[0]['SN'];

    }
}
else {
    $APP_DBCONNECTION->rollback();
    //echo HTML::alert('Error','Unable to save application status!','danger');    
    ?><script> 
    alert("Error");
    sql = "<?php echo $sql;?>";
    console.log(sql);
    </script><?php
    $this->showdebugerror($results,$sql); 
 
}


?>

<!-- <script>
x = "< ?php echo $ipaddress; ?>";
console.log(x);
random = "< ?php echo $random; ?>";
console.log(random);
sql = "< ?php echo $sql. "   IDFee:" . $_SESSION["IDFee"]."     GradFee:".$_SESSION["GradFee"]."<br>".$_SESSION["ApplicationNumber"]; ?>";
console.log(sql);
</script> -->

