<?php
$template_parts["html_title"] .= " ~ Create";
$template_parts["page_title"] .= "Create new client";
$template_parts["page_actions"] = "";

$package_set = new package_set();
$package_set->loadAll();
$server_set = new server_set();
$server_set->loadAll();

$form = new form();
$form->target("client/create");
$form->required(true);
$form->col(6);
    $form->group("Basics");
    $form->text_input("avataruid","Avatar UID <a href=\"[[url_base]]avatar\" target=\"_blank\">Find</a>",30,"","Avatar uid");
    $form->number_input("daysremaining","Days remaining",0,3,"Max 999");
    $form->text_input("streamuid","Stream UID <a href=\"[[url_base]]stream\" target=\"_blank\">Find</a>",30,"","Stream uid [Found in streams]");
echo $form->render("Create","primary");
?>
