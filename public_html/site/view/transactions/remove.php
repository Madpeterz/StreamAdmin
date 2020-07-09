<?php
$template_parts["html_title"] .= " ~ Remove";
$template_parts["page_title"] .= "Remove transaction:";
$template_parts["page_title"] .= $page;
$template_parts["page_actions"] = "";

$form = new form();
$form->target("transactions/remove/".$page."");
$form->required(true);
$form->col(6);
$form->group("Warning</h4><p>Please note: this will have any effect on the rental linked to the transaction</p><h4>");
$form->text_input("accept","Type \"Accept\"",30,"","This will delete the transaction");
echo $form->render("Remove","danger");
echo "";
?>
