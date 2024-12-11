<?php 
    include_once("class.php");
    
    $selectedCourseYear =  $_POST['selectedCourseYear'];
    $CourseYear = new JunClass('','',$selectedCourseYear);
    $_SESSION["College"] = $CourseYear->getCollege();
    $_SESSION["YearLevel"] = $CourseYear->getYear();
    $_SESSION["Course"] =  $CourseYear->getCourse();
    $_SESSION["selectedCourseYear"] = $selectedCourseYear;

    $sql = APP_DB_DATABASE ."..Usp_SelectFreshBlockSubjectCode '".$_COOKIE['PageSemester']."','','".$_SESSION["College"]."','".$_SESSION["YearLevel"]."','".$_SESSION["CampusCode"]."',1";
    $results = $APP_DBCONNECTION->execute($sql);
    $numrec = count($results);
    $html = "";
echo $sql;

    for ($i=0;$i<$numrec;$i++) { 
        $bsc=$results[$i]['BlockSubjectCode'];
    $html .= '<tr class="selector" id='.$bsc.'><td><a href="#" id = "BlockCode"  onclick="fBlockSchedule(\''.$bsc."','".Crypto::GenericChecksum($bsc).'\')">'.$bsc."</td><td>".
     '<a href="#" id = "BlockCode"  onclick="fBlockSchedule(\''.$bsc."','".Crypto::GenericChecksum($bsc).'\')">'.$results[$i]['section']."</td><td>".
                 $results[$i]['curriculum']."</td><td>".
                 $results[$i]['description']."</td></tr>";
    }

    echo $html;
?>

    <script> 
    //alert("123");
    
    $('#tbodySchedule').html(
        "<?php 
            $x = new JunClass('','','');
            $html = $x->ClearSchedules();
            echo $html;
        ?>"
    )

    $('#BreakDownOfFees').html(
        
        "<?php 
            $x = new JunClass('','','');
            $html = $x->ClearTableOfFees();
            echo $html;
        ?>"
    )

    $('#Distribution').html(
        "<?php 
            $x = new JunClass('','','');
            $html = $x->ClearBreakdownOfFees();
            echo $html;
        ?>"
    )

    $('#Paymentmode').hide();
    $('#FeesTable_info').hide();

</script>

        
