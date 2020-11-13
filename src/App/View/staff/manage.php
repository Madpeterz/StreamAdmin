<?php

if ($session->get_ownerlevel() == true) {
    $this->output->addSwapTagString("html_title", " ~ Manage");
    $this->output->addSwapTagString("page_title", ": Editing staff member");
    $this->output->setSwapTagString("page_actions", "<a href='[[url_base]]staff/remove/" . $page . "'><button type='button' class='btn btn-danger'>Remove</button></a>");

    $staff = new staff();
    if ($staff->load_by_field("id", $page) == true) {
        $form = new form();
        $form->target("staff/update/" . $page . "");
        $form->required(true);
        $form->col(6);
            $form->textInput("username", "Username", 40, $staff->get_username(), "Used to login [does not have to be the same as their SL name]");
            $form->textInput("email", "Email", 200, $staff->get_email(), "Used to change their password via email");
        $this->output->setSwapTagString("page_content", $form->render("Update", "primary"));
    } else {
        $this->output->redirect("staff?bubblemessage=unable to find staff member&bubbletype=warning");
    }
} else {
    $this->output->redirect("staff?bubblemessage=Owner level access needed&bubbletype=warning");
}
