<?php

namespace App\Endpoints\View\Avatar;

use App\Models\Avatar;
use App\Template\Form as Form;

class Manage extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", "~ Manage");
        $this->output->setSwapTagString("page_title", "Editing avatar");
        $target = "<a href='[[url_base]]avatar/remove/" . $this->page . "'>"
        . "<button type='button' class='btn btn-danger'>Remove</button></a>";
        $this->output->setSwapTagString(
            "page_actions",
            $target
        );
        $avatar = new Avatar();
        if ($avatar->loadByField("avatar_uid", $this->page) == false) {
            $this->output->redirect("avatar?bubblemessage=unable to find avatar&bubbletype=warning");
            return;
        }
        $form = new form();
        $form->target("avatar/update/" . $this->page . "");
        $form->required(true);
        $form->col(6);
        $form->textInput(
            "avatarname",
            "Name",
            125,
            $avatar->getAvatarname(),
            "Madpeter Zond [You can leave out Resident]"
        );
        $form->textInput(
            "avataruuid",
            "SL UUID",
            3,
            $avatar->getAvataruuid(),
            "SecondLife UUID [found on their SL profile]"
        );
        $this->output->setSwapTagString("page_content", $form->render("Update", "primary"));
    }
}
