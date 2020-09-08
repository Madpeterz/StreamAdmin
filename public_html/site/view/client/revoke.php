<?php
$template_parts["html_title"] .= " ~ Revoke";
$template_parts["page_title"] .= "revoke client rental:";
$template_parts["page_title"] .= $page;
$template_parts["page_actions"] = "";

$form = new form();
$form->target("client/revoke/".$page."");
$form->required(true);
$form->col(6);
$form->group("Warning</h4><p>This will end the rental without any refund!</p><h4>");
$form->text_input("accept","Type \"Accept\"",30,"","I understand and accept the outcome of my actions");
echo $form->render("Revoke","danger");
echo "";
?>
