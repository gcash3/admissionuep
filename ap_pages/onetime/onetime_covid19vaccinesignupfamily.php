<?php

$emaillink = "https://mail.google.com/a/ue.edu.ph/mail/?extsrc=mailto&url=mailto:". urlencode("?to=dhrdmonitoring@ue.edu.ph&subject=INQUIRY: LT Group of Companies (LTGC) Vaccination Program&body=");
$link = '<a href="https://reserveuefight.ltg.com.ph" target=_blank><b style="font-size:130%"><i class="fa fa-hand-o-right"></i> https://reserveuefight.ltg.com.ph</b></a>';
$body = "
<p>
Dear Respondent:
</p>
<p>In its attempt to restore safety in the workplace and the home amidst the Covid-19 pandemic, <b>LT Group, Inc. (LTG)</b> is voluntarily taking the initiative to consolidate reservation orders for its employees as well as those of its subsidiaries and affiliates who wish to avail of Covid-19 vaccines for their family and household members who are at least eighteen (18) years old. This service to make the vaccines available at cost is in line with the National Government’s effort to end the pandemic. Kindly accomplish the Sign-Up form through the link below.</p>
<p>$link</p>
<p>The said form will be accessible from March 27 to March 30, 2021.</p>
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

