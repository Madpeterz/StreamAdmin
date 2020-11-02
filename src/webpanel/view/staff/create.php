<?php

if ($session->get_ownerlevel() == true) {
    $view_reply->add_swap_tag_string("html_title", " ~ Create");
    $view_reply->add_swap_tag_string("page_title", " Create new staff account");
    $view_reply->set_swap_tag_string("page_actions", "");
    $form = new form();
    $form->target("staff/create");
    $form->required(true);
    $form->col(6);
        $form->text_input("avataruid", "Avatar UID <a data-toggle=\"modal\" data-target=\"#AvatarPicker\" href=\"#\" target=\"_blank\">Find</a>", 8, "", "Avatar uid");
        $form->text_input("username", "Username", 40, null, "Used to login [does not have to be the same as their SL name]");
        $form->text_input("email", "Email", 200, "", "Used to change their password via email");
    $view_reply->set_swap_tag_string("page_content", $form->render("Create", "primary"));
    $view_reply->add_swap_tag_string("page_content", "<br/><p>Once created they can use the Reset password system to gain access</p>");
} else {
    $view_reply->redirect("staff?bubblemessage=Owner level access needed&bubbletype=warning");
}
