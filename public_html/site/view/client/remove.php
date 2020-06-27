<?php
$template_parts["html_title"] .= " ~ Remove";
$template_parts["page_title"] .= "Remove client:";
$template_parts["page_title"] .= $page;
$template_parts["page_actions"] = "";

$form = new form();
$form->target("client/remove/".$page."");
$form->required(true);
$form->col(6);
$form->group("Warning</h4><p>This will end the rental without any refund!</p><h4>");
$form->text_input("accept","Type \"Accept\"",30,"","I understand and accept the outcome of my actions");
echo $form->render("Remove","danger");
echo "";
?>
