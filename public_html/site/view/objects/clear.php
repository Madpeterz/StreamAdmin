<?php
$template_parts["html_title"] .= " ~ Clear";
$template_parts["page_title"] .= ": Clear";
$template_parts["page_actions"] = "";

$form = new form();
$form->target("objects/clear");
$form->required(true);
$form->col(6);
$form->group("Clear all objects (DB only)");
$form->text_input("accept","Type \"Accept\"",30,"","ok sure");
echo $form->render("Clear","warning");
echo "";
?>
