<?php

$view_reply->add_swap_tag_string("html_title", "~ Create");
$view_reply->set_swap_tag_string("page_title", "Create new avatar");
$view_reply->set_swap_tag_string("page_actions", "");
$form = new form();
$form->target("avatar/create");
$form->required(true);
$form->col(6);
    $form->text_input("avatarname", "Name", 125, null, "Madpeter Zond [You can leave out Resident]");
    $form->text_input("avataruuid", "SL UUID", 3, null, "SecondLife UUID [found on their SL profile]");
$view_reply->set_swap_tag_string("page_content", $form->render("Create", "primary"));
