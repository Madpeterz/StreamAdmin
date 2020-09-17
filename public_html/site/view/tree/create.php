<?php
$template_parts["html_title"] .= " ~ Create";
$template_parts["page_title"] .= " : New";
$template_parts["page_actions"] = "";
$form = new form();
$form->target("tree/create");
$form->required(true);
$form->col(6);
    $form->text_input("name","Name",30,"","Name");
echo $form->render("Create","primary");
?>
