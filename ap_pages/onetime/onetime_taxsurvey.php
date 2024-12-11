<?php
$year ='2021';
if (isset($_POST['Substituted'])) {
    $substituted = $_POST['Substituted'];
    $taxableyear = $year-1;
    $employeecode = $APP_SESSION->getEmployeeCode();
    $sql = APP_DB_DATABASEPAYROLL . "..Usp_SaveTaxSurvey '$taxableyear', '$employeecode', $substituted";
    $results = $APP_DBCONNECTION->execute($sql);
    if (Tools::emptydataset($results)) {
        $message = 'Unable to save your response. Please try later.';
        $message .= Tools::devonly("<br><pre>SQL:".$sql . '<br>' . print_r($results,true) . '</pre>');
        $APP_SESSION->setApplicationMessage($message,true,'danger');
    }
    header('Location: '. $_SERVER['REQUEST_URI']);
    return;
}
?>
<div class="well">
<h2>Memorandum to All Faculty Members and Employees</h2>
<p>This survey is relative to the preparation of employer's annual information return of income taxes withheld on compensation and employee's certificate of compensation payment/tax withheld on the compensation such as salaries, wages, and other forms of remuneration by each employer indicating therein the total amount paid and the taxes withheld therefrom during the calendar year <?php echo $year?>.</p>
<p>In relation thereto, the University needs to determine who are qualified for <b>substituted filing</b> of the income tax return based on Section 2.83.4 of Revenue Regulation 02-1998, as amended by Revenue Regulation 03-2002, to wit:</p>
<ul>
<li><b>Qualified</b> for substituted filing are those receiving purely compensation income from the University for the calendar year 
<?php echo $year-1?> and the income tax of which has been withheld by the University (tax due equals tax withheld).</li>
<li><b>Not qualified</b> for substituted filing are those deriving compensation from two or more employers concurrently or 
successively at any time during the taxable year; those deriving other non-business, non-profession-related income in 
addition to compensation income not otherwise subject to a final tax; and those whose spouse is not qualified for the 
substituted filing of income tax returns. Consequently, the filing of the income tax return with the Bureau of Internal 
Revenue (BIR) shall be their responsibility.</li>
</ul>
<p>In line with this, we respectfully request all personnel to declare in the survey form whether or not he/she is qualified for substituted filing of income tax return. The accomplished survey form is necessary for the preparation, certification, and filing of tax certificates. Kindly submit the accomplished form on or before November 16, <?php echo $year?>.</p>
<p>Should there be any update on the information provided after the cut-off date, the Comptroller's Department should be notified no later than December 1, <?php echo $year?>.</p>

Thank you for your cooperation.<br>
<br>
<b>Redentor D. Gironella</b><br>
Comptroller
<br>
<br>
Are you qualified for substituted filing (receiving purely compensation income only from University of the East and no other source/s of income for year <?php echo $year?>)
<form method='post'>
<div class="radio">
  <label><input type="radio" name="Substituted" value='1' required>Yes</label>
</div>
<div class="radio">
  <label><input type="radio" name="Substituted" value='0' required>No</label>
</div>
<input type=submit value="Submit" class='btn btn-danger' data-confirmation='Do you want submit your reply?'>
</form>
<br>

       