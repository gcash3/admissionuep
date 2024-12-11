<?php


if ($APP_SESSION->getPageSemester() == '') {
    echo  HTML::alert('','Please select semester first!','danger',true);
    return;
}

// $html = '<table class="table table-condensed payslip">'.
//         '<tbody class="table-bordered">'.
//         '<tr><td class="col-sm-1">Reference No.</td><td class=""><b>2020096680</b></td><td class="col-sm-1">Department</td><td class=""><b>Secondary Laboratory                              </b></td>                          </tr>'.
//         '<tr><td class="col-sm-1">Lastname</td><td class=""><b>PASCUA</b></td> <td class="col-sm-1">Course</td><td class=""><b>HS    - 4</b></td></tr>'.
//         '<tr><td class="col-sm-1">Firstname</td><td class=""><b>JUN</b></td><td class="col-sm-1">Campus</td><td class=""><b>Manila Campus</b></td>                    </tr>'.
//         '<tr><td class="col-sm-1">Middlename</td><td class=""><b>PESTANO</b></td><td class="col-sm-1">Sex</td><td class=""><b>MALE</b></td>                              </tr>'.
//         '</tbody>'.
//         '</table>';

// echo HTML::box('Applicant Data', $html, 'ApplicantXXX', 'success',true,false,'table-responsive');


//echo $APP_CURRENTPAGE;
//echo "Testing";

require_once("assignschedulecommonktog10.php");
$system = new CRUDcredentials('Applicant Schedule','WebReferenceNumber','t1', $APP_SESSION->getuserid(), $APP_CURRENTPAGE, 'B');
$system->run();

?>

