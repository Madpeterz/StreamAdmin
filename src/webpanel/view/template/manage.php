<?php

$view_reply->add_swap_tag_string("html_title", " ~ Manage");
$view_reply->add_swap_tag_string("page_title", " Manage");
$view_reply->set_swap_tag_string("page_actions", "<a href='[[url_base]]template/remove/" . $page . "'><button type='button' class='btn btn-danger'>Remove</button></a>");
$template = new template();
if ($template->load($page) == true) {
    $view_reply->add_swap_tag_string("page_title", ":" . $template->get_name());
    $form = new form();
    $form->target("template/update/" . $page . "");
    $form->required(true);
    $form->col(3);
        $form->text_input("name", "Name", 30, $template->get_name(), "Name");
    $form->split();
    $form->col(6);
        $form->textarea("detail", "Template [Object+Bot IM]", 800, $template->get_detail(), "Use swap tags as the placeholders! max length 800");
    $form->col(6);
        $form->textarea("notecarddetail", "Notecard template", 2000, $template->get_notecarddetail(), "Use swap tags as the placeholder");
    $view_reply->set_swap_tag_string("page_content", $form->render("Update", "primary"));
    include "webpanel/view/shared/swaps_table.php";
} else {
    $view_reply->redirect("template?bubblemessage=unable to find template&bubbletype=warning");
}
