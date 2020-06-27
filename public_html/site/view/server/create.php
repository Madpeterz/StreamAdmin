<?php
$template_parts["html_title"] .= " ~ Create";
$template_parts["page_title"] .= " : New";
$template_parts["page_actions"] = "";
$form = new form();
$form->target("server/create");
$form->required(true);
$form->col(6);
    $form->text_input("domain","Domain",30,"","ip or uncloudflared proxyed domain/subdomain");
    $form->text_input("controlpanel_url","Control panel",200,"","URL to the control panel");
echo $form->render("Create","primary");
?>
