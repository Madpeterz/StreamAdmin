<?php
$template_parts["html_title"] .= " ~ Remove";
$template_parts["page_title"] .= "Remove package:";
$template_parts["page_title"] .= $page;
$template_parts["page_actions"] = "";

$form = new form();
$form->target("package/remove/".$page."");
$form->required(true);
$form->col(6);
$form->group("Warning</h4><p>If the package is currenly in use this will fail</p><h4>");
$form->text_input("accept","Type \"Accept\"",30,"","This will delete the package");
echo $form->render("Remove","danger");
echo "";
?>
