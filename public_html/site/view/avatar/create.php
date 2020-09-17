<?php
$template_parts["html_title"] .= " ~ Create";
$template_parts["page_title"] .= "Create new avatar";
$template_parts["page_actions"] = "";
$form = new form();
$form->target("avatar/create");
$form->required(true);
$form->col(6);
    $form->text_input("avatarname","Name",125,null,"Madpeter Zond [You can leave out Resident]");
    $form->text_input("avataruuid","SL UUID",3,null,"SecondLife UUID [found on their SL profile]");
echo $form->render("Create","primary");
?>
