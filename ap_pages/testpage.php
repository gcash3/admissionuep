<?php

$arraysource = Data::getoptionsarray("Usp_AP_VL_GetTypes", "LeaveType", "Description");

$leavetype = 'VL';

echo HTML::hformselect('LeaveType',"Leave Type", $leavetype, $arraysource);

echo '<div id="list">List DIV</div>';
return;


if (isset($_POST['Save'])) {
    echo '<pre>' . print_r($_POST,true) . '</pre>';
    
    $computercode = @$_POST['ComputerCode'] + 0;
    $cs = @$_POST['CS'];
    echo "<h1>$cs</h1>";
    echo "<h1>$computercode</h1>";
    
    if (Crypto::validateimagechecksum($computercode, $cs)) {
        $sql = "Usp_SaveGrade $computercode" ;
        foreach ($_POST as $key => $value) {
            if (substr($key,0,3) == 'SN_') {
                $sn = substr($key,3);
                $posted = $_POST['Posted_' .$sn];
                $sql .= ", '$sn', '$value', $posted";
            }
        }

        echo "<h1>$sql</h1>";
    }
    else {
        echo 'Invalid Parameter!';
    }
}


$results[] = array('SN'=>'20220100027', 'Grade' => '1.0', 'Posted' => 0);
$results[] = array('SN'=>'20220100028', 'Grade' => '1.25', 'Posted' => 1);
$results[] = array('SN'=>'20220100023', 'Grade' => '1.50', 'Posted' => 0);

$i = 0;
foreach ($results as $record) {
    $sn = $record['SN'];
    $posted = $record['Posted'] == true ? 1 : 0;
    $name = $posted ? mt_rand() : "SN_$sn";

    $grade = @$_POST[$name] ? $_POST[$name] : $record['Grade'];

    //$record['Grade'] = HTML::text($name, '', $grade,'','', $posted, $posted);
    $readonly = $posted ? 'readonly' : '';
    $disabled = $posted ? 'disabled' : '';
    $record['Grade'] = "<input type=number name='$name' min='60' max='100' id='$name' $readonly $disabled class='form-control' value='$grade' maxlength=3>";
    $record['Posted'] = ($posted ? 'Yes' : 'No')  . "<input type=hidden name='Posted_$sn' value='$posted'>";
    
    $results[$i] = $record;
    $i++;
}

$columns['SN'] = 'SN';
$columns['Grade'] = 'Final Grade';
$columns['Posted'] = 'Posted';

$computercode = '123456';
$cs = Crypto::GenericChecksum($computercode);

$html = HTML::hidden('ComputerCode', $computercode);
$html .= HTML::hidden('CS', $cs);
$html .= HTML::datatable('grades',$columns, $results,'',"data-title='Sample'");
$html .= HTML::submitbutton('Save','Save');

echo '<form method=post>';
echo $html;
echo '</form>';
?>

<?php
return;

 include_once('snippetscreator.php');


// $results = $APP_DBCONNECTION->execute($sql);

?>



