<?php

namespace App\View\Staff;

use App\Staff;
use App\Template\Form;

class Manage extends View
{
    public function process(): void
    {
        if ($this->session->getOwnerLevel() == false) {
            $this->output->redirect("staff?bubblemessage=Owner level access needed&bubbletype=warning");
            return;
        }
        $this->output->addSwapTagString("html_title", " ~ Manage");
        $this->output->addSwapTagString("page_title", ": Editing staff member");
        $this->output->setSwapTagString(
            "page_actions",
            "<a href='[[url_base]]staff/remove/" . $this->page . "'><button type='button' "
            . "class='btn btn-danger'>Remove</button></a>"
        );

        $staff = new Staff();
        if ($staff->loadByField("id", $this->page) == false) {
            $this->output->redirect("staff?bubblemessage=unable to find staff member&bubbletype=warning");
            return;
        }
        $form = new Form();
        $form->target("staff/update/" . $this->page . "");
        $form->required(true);
        $form->col(6);
        $form->textInput(
            "username",
            "Username",
            40,
            $staff->getUsername(),
            "Used to login [does not have to be the same as their SL name]"
        );
        $form->textInput("email", "Email", 200, $staff->getEmail(), "Used to change their password via email");
        $this->output->setSwapTagString("page_content", $form->render("Update", "primary"));
    }
}
