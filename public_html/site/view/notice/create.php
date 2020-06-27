<?php
$template_parts["html_title"] .= " ~ Create";
$template_parts["page_title"] .= " : New";
$template_parts["page_actions"] = "";
$form = new form();
$form->target("notice/create");
$form->required(true);
$form->col(6);
    $form->group("Basic");
    $form->text_input("name","Name",30,"","Name");
    $form->textarea("immessage","Message",800,"","use the swaps as placeholders");
$form->col(6);
    $form->group("Config");
    $form->select("usebot","Use bot to send IM",false,array(false=>"No",true=>"Yes"));
    $form->number_input("hoursremaining","Hours remain [Trigger at]",24,3,"Max value 999");
echo $form->render("Create","primary");
include("site/view/shared/swaps_table.php");
?>
