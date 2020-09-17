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
    $form->text_input("avataruid","Avatar UID (Or UUID/Full name)",30,"","Avatar uid | Madpeter Zond | xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx");
    $form->direct_add("<a data-toggle=\"modal\" data-target=\"#AvatarPicker\" href=\"#\" target=\"_blank\">Find/Add avatar</a><br/>");
    $form->number_input("daysremaining","Days remaining",0,3,"Max 999");
    $form->text_input("streamuid","Stream UID (Or port)",30,"","Stream uid | Port number");
$form->col(6);
$form->col(6);
    $form->direct_add("<br/>If there are multiple streams with the same port number you must use the UID!");
print $form->render("Create","primary");
?>
