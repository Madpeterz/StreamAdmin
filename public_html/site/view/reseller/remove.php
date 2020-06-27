<?php
$template_parts["html_title"] .= " ~ Remove";
$template_parts["page_title"] .= "Remove reseller:";
$template_parts["page_title"] .= $page;
$template_parts["page_actions"] = "";

$form = new form();
$form->target("reseller/remove/".$page."");
$form->required(true);
$form->col(6);
$form->group("Warning</h4><p>If the reseller is currenly in use this will fail<br/>You should just mark them as not allowed!</p><h4>");
$form->text_input("accept","Type \"Accept\"",30,"","This will delete the reseller");
echo $form->render("Remove","danger");
echo "";
?>
