<?php
//---------------------------------------------------------------
// File        : emailtemplate_form.php
// Description : Email Template View - Form
// Author      : CSN
// Date        : 10/05/2018
// --------------------------------------------------------------  
$readonly = ($command != 'new') && ($command != 'edit');

$title = 'Email Template ' . ($command != 'new' ? "" : " [New]");
$form = '';
$footer = HTML::linkbutton("$APP_CURRENTPAGE/$opener",HTML::icon('arrow-left','Go Back'));
if (!$readonly)
    $footer .= HTML::submitbutton('SaveEmailTemplate',HTML::icon('floppy-o','Save Email Template'), 'success', false);
if (($command == 'delete') && $APP_SESSION->getCanDelete()) 
    $footer .= HTML::submitbutton('DeleteEmailTemplate','Delete Email Template', 'danger');

$form .= HTML::hforminputtext('TemplateID','Template ID', $TemplateID, 'Template ID', false, true, false,2,1);
$form .= HTML::hforminputtext('Description','Description', $Description, 'Description', true, $readonly, false);
$form .= HTML::hforminputtext('Subject','Subject', $Subject, 'Subject', true, $readonly, false);
if ($readonly)
    $form  .= HTML::hformdiv('Message Preview', "<div class='preview'>$MessageBody</div>Last modified on $LastModified Revision $Revision ",2,5);
else
    $form .= HTML::hformtextarea('MessageBody','Body', $MessageBody, 'Email Body...', true, $readonly,  $readonly, 2,10);
    
if ($readonly) {
    $form .= '<hr>';
    $form .= HTML::hidden('Body', $MessageBody);
    $form .= HTML::hforminputtext('To','Send Test Email To', $To, 'To', true, false, false,2,5,'email');
    $form .= HTML::hforminputtext('Bcc','Send Test Email Bcc', $Bcc, 'Bcc', false, false, false,2,5,'email');
    $footer .= HTML::submitbutton('SendTestEmail',HTML::icon('envelopoe','Send Test Email'), 'success', false);
}    

    
$body = $form;
echo "<form class='form-horizontal' method=post>";
echo HTML::box("<b>$title</b>", $body, $footer, 'primary',true,false,' ');
echo '</form>';


?>
