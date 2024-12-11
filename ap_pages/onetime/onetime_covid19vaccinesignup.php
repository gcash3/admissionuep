<?php

$emaillink = "https://mail.google.com/a/ue.edu.ph/mail/?extsrc=mailto&url=mailto:". urlencode("?to=dhrdmonitoring@ue.edu.ph&subject=INQUIRY: LT Group of Companies (LTGC) Vaccination Program&body=");
$link = '<a href="https://reserveuefight.ltg.com.ph" target=_blank><b style="font-size:130%"><i class="fa fa-hand-o-right"></i> https://uefight.ltg.com.ph</b></a>';
$body = "
<p>
Dear Respondent:
</p>
<p>In line with the <b>LT Group of Companies (LTGC) Vaccination Program</b> to all employees of its companies and subsidiaries, kindly accomplish the Sign-Up form through the link below.</p>
<p>$link</p>
<p>The said form will be accessible from March 08 to March 15, 2021.</p>
<p>In filling out the form, please observe the following:</p>
<p>
<ol>
<li>Fill out all required information and do not leave any field blank.</li>
<li>Avoid sharing of the link to anyone even to your co-employee to centralize the access of the Sign-Up form at the UE Portals. Kindly encourage them to respond to the Sign-Up form through the UE Portals instead. </li>
<li>For questions or clarifications, kindly coordinate with the Department of Human Resources and Development (DHRD) at <a href='$emaillink' target=_blank>dhrdmonitoring@ue.edu.ph</a></li>
</ol>
</p>
<p>Thank You.</p>";

echo HTML::box(HTML::icon('flash','Important Reminder!'),$body,HTML::icon('lightbulb-o','<em>Stay healthy and keep safe.</em>'),'danger',true,true);
?>

