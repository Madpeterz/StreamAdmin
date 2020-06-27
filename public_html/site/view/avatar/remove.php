<?php
$template_parts["html_title"] .= " ~ Remove";
$template_parts["page_title"] .= "Remove avatar:";
$template_parts["page_title"] .= $page;
$template_parts["page_actions"] = "";

$form = new form();
$form->target("avatar/remove/".$page."");
$form->required(true);
$form->col(6);
$form->group("Warning</h4><p>If the avatar is currenly in use this will fail</p><h4>");
$form->text_input("accept","Type \"Accept\"",30,"","This will delete the avatar");
echo $form->render("Remove","danger");
echo "";
?>
