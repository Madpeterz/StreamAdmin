<?php
$view_reply->add_swap_tag_string("html_title"," ~ Create");
$view_reply->add_swap_tag_string("page_title"," : New");
$view_reply->set_swap_tag_string("page_actions","");
$form = new form();
$form->target("tree/create");
$form->required(true);
$form->col(6);
    $form->text_input("name","Name",30,"","Name");
$view_reply->set_swap_tag_string("page_content",$form->render("Create","primary"));
?>
