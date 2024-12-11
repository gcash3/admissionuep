<?php 

    //unset($_SESSION['RecordCount']);
    $recordcount = 0;
    
    // if (!Crypto::ValidateGenericChecksum($_POST['BlockCode'], $_POST['cs'])) {

    //     // echo "<tr><td colspan=8 > ",$_POST['BlockCode'], ' ' , $_POST['cs'], ' ',  Crypto::GenericChecksum($_POST['BlockCode'])," </td></tr>";
    //     // return;
    //     $_SESSION["RecordCount"]=0;
    //     echo "<tr><td colspan=8 > Invalid Parameters! </td></tr>";
    //     return;
    // }

    $BlockCode =  $_POST['BlockCode'];
    $_SESSION["BlockCode"] = $BlockCode;
    $sql = APP_DB_DATABASE.".dbo.Usp_SelectFreshBlockSubjects '".$_COOKIE['PageSemester']."','$BlockCode','','0','',1";
    //$sql = "select * from ".APP_DB_DATABASE.".dbo.[subject offered] where blocksubjectcode = 256800";    
    
//echo "<pre> ";
//echo $sql."<br>";   

// print_r($APP_SESSION)."<br/>";       
// echo phpinfo(INFO_VARIABLES)."<br/>";
// echo '<pre>';
// echo APP_BASE."<br/>";
//echo '<pre>'; 
//$_SESSION["Mymodule"]="Applicant's K-G10"."<br/>";
//print_r($_SESSION)."<br/>";
//echo '<pre>';
//ECHO $_COOKIE['PageSemester']."<br/>";
//print_r($APP_SESSION)."<br/>";
    

//    '20191','256800','M','0','',1
    
    $results = $APP_DBCONNECTION->execute($sql);
//    print_r($results);

    if (Tools::emptydataset($results) ) { 
        $html="";
        $msg="";
        $_SESSION["RecordCount"] = 0;
        for ($i=0;$i<6;$i++) {
        $msg = ($i==1) ? "Unable to load subjects!" : ""; 
        $msg2 = ($i==2) ? "Section is closed." : ""; 
        $html .=   "<tr><td style='text-align: center'>".$msg.$msg2."</td>".
                        "<td><br></td>".
                        "<td> </td>".
                        "<td> </td>".
                        "<td> </td>".
                        "<td> </td>".
                        "<td> </td>".
                        "<td> </td>";
        }
        echo $html;
        echo "<hr>";
        //return;    
    }
else {

    $numrec = count($results);
    $html="";
    for ($i=0;$i<$numrec;$i++) { 
        $html .=   "<tr><td table-bordered>".$results[$i]['Description']."</td>
                        <td table-bordered>".$results[$i]['section']."</td>
                        <td table-bordered>".$results[$i]['days']."</td>
                        <td table-bordered>".$results[$i]['time']."</td>
                        <td table-bordered>".$results[$i]['room']."</td>
                        <td table-bordered>".$results[$i]['limit']."</td>
                        <td table-bordered>".$results[$i]['size']."</td></tr>";
                        }

        $html .= "<tr class='bg-blue'><td id='SubjectCount' >".$numrec." Subject(s)</td>".
                    "<td></td>".
                    "<td></td>".
                    "<td></td>".
                    "<td></td>".
                    "<td></td>".
                    "<td></td></tr>";
$_SESSION["RecordCount"] = $numrec;                    
//$html .= "</tbody></table>";
echo $html; 
                    }
                    
// ---------------------------------------------------------------------------------------------------


?>
 
<script>
   
   $('#Fees').load(
           "<?php echo APP_BASE ?>fBreakdownofFees/plain"
         )   
// CONTINUE HERE

</script>         