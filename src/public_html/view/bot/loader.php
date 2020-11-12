<?php

if ($session->get_ownerlevel() == true) {
    $botconfig = new botconfig();
    $botconfig->load(1);
    $avatar = new avatar();
    $avatar->load($botconfig->get_avatarlink());
    $view_reply->set_swap_tag_string("html_title", "Bot setup");
    $view_reply->set_swap_tag_string("page_title", "Editing bot " . $avatar->get_avatarname());
    $view_reply->set_swap_tag_string("page_actions", "");

    $form = new form();
    $form->target("bot/update");
    $form->required(true);
    $form->col(6);
    $form->group("Basic");
    $form->text_input("avataruid", "Avatar UID <a data-toggle=\"modal\" data-target=\"#AvatarPicker\" href=\"#\" target=\"_blank\">Find</a>", 30, $avatar->get_avatar_uid(), "Avatar uid [Not the same as a SL UUID!]");
    $form->text_input("secret", "Secret SL->Bot", 36, $botconfig->get_secret(), "Bot secret [Found in ***.json or env value]");
    $form->col(6);
    $form->group("Actions");
    $form->select("notecards", "Create notecards", $botconfig->get_notecards(), array(false => "No",true => "Yes"));
    $form->select("ims", "Send ims", $botconfig->get_ims(), array(false => "No",true => "Yes"));
    $view_reply->set_swap_tag_string("page_content", $form->render("Update", "primary"));
} else {
    $view_reply->redirect("config?bubblemessage=Owner level access needed&bubbletype=warning");
}
