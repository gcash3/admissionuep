<?php
$UserEmployeeCode = $APP_SESSION->getuserid();
$searchtext = trim(@$_POST['st']);
$applicationnumber = @$_POST['an'] + 0;
$webreferencenumber = @$_POST['rn'] + 0;
$cs = @$_POST['cs'];

if ((strlen($searchtext) <= 3) && ($searchtext <> 'N/L')) {
    echo 'Search text too short';
    return;
}

if (Crypto::GenericChecksum("$applicationnumber;uwi;na;$webreferencenumber") != $cs) {
    echo 'Invalid parameters!';
    return;
}


if ($searchtext <> 'N/L') {
    $sql = APP_DB_DATABASEADMISSION . "..Usp_OA_FindSchools '$searchtext', 1";
    $results = $APP_DBCONNECTION->execute($sql);

    echo "Search results for [" , htmlentities($searchtext), ']<br>Showing ', min(100, count($results)), " of ", count($results);
    echo '. Click Application number to update School ID.';   
    echo '<table class="table table-bordered table-hover table-condensed">';
    $i = 0;
    foreach ($results as $record) {
        $schoolid = $record['SchoolID'];
        $schoolname = $record['SchoolName'];
        $cs = Crypto::GenericChecksum('$schoolid;$applicationnumber;sardinas;ulam;ko;$webreferencenumber');
        echo '<tr>';
        echo '<td rowspan=2>', ++$i, '.</td>';
        $link = "setschool($schoolid, $applicationnumber, '$cs', '$schoolname', $webreferencenumber)";
        echo "<td><a class='setschool' onclick=\"$link\">$schoolid</a></td>";
        echo '<td>', $schoolname, '</td>';
        echo '<td>', $record['SchoolType'], '</td>';
        echo '<td>', $record['Region'], '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td colspan=4><small>', $record['SchoolAddress'], '</small></td>';
        echo '</tr>';
        if ($i == 100)
            break;
    }
    echo '</table>';
    return;
}
$schoolid = 0;
$schoolname = 'N/L';
$cs = Crypto::GenericChecksum('$schoolid;$applicationnumber;sardinas;ulam;ko;$webreferencenumber');
$link = "if (\$('#SchoolName').val().trim() == '') \$('#SchoolName').focus(); else setschool($schoolid, $applicationnumber, '$cs', \$('.schooltype:checked').val() + ':' + \$('#SchoolName').val(), $webreferencenumber)";
echo '
<div class="form-group">
<label>School Name</label>
<input type="text" class="form-control no-warning" id="SchoolName" autofocus>
</div>
<div class="radio">
<label><input type="radio" class="radio-inline schooltype" name="SchooType" value="PRIVATE" checked>Private</label>
<label><input type="radio" class="radio-inline schooltype" name="SchooType" value="PUBLIC">Public</label>
</div>
<button type="button" class="btn btn-default" id="SaveSchool" onclick="'. $link . '">Save School</button> 
</div>
<script>$("#SchoolName").focus();</script>
';

?>