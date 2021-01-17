<?php

namespace App\Endpoints\View\Staff;

use App\Template\Form;

class Create extends View
{
    public function process(): void
    {
        if ($this->session->getOwnerLevel() == false) {
            $this->output->redirect("staff?bubblemessage=Owner level access needed&bubbletype=warning");
            return;
        }
        $this->output->addSwapTagString("html_title", " ~ Create");
        $this->output->addSwapTagString("page_title", " Create new staff account");
        $this->setSwapTag("page_actions", "");
        $form = new Form();
        $form->target("staff/create");
        $form->required(true);
        $form->col(6);
            $form->textInput(
                "avataruid",
                "Avatar UID <a data-toggle=\"modal\" data-target=\"#AvatarPicker\" "
                . "href=\"#\" target=\"_blank\">Find</a>",
                8,
                "",
                "Avatar uid"
            );
            $form->textInput(
                "username",
                "Username",
                40,
                null,
                "Used to login [does not have to be the same as their SL name]"
            );
            $form->textInput("email", "Email", 200, "", "Used to change their password via email");
        $this->setSwapTag("page_content", $form->render("Create", "primary"));
        $this->output->addSwapTagString(
            "page_content",
            "<br/><p>Once created they can use the Reset password system to gain access</p>"
        );
    }
}
