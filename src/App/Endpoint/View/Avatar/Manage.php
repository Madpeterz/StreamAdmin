<?php

namespace App\Endpoint\View\Avatar;

use App\R7\Model\Avatar;
use App\Template\Form as Form;

class Manage extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", "~ Manage");
        $this->setSwapTag("page_title", "Editing avatar");
        $target = "<a href='[[url_base]]avatar/remove/" . $this->page . "'>"
        . "<button type='button' class='btn btn-danger'>Remove</button></a>";
        $this->setSwapTag(
            "page_actions",
            $target
        );
        $avatar = new Avatar();
        if ($avatar->loadByField("avatarUid", $this->page) == false) {
            $this->output->redirect("avatar?bubblemessage=unable to find avatar&bubbletype=warning");
            return;
        }
        $this->output->addSwapTagString("page_title", " : " . $avatar->getAvatarName() . " [" . $avatar->getAvatarUid() . "]");
        $form = new form();
        $form->target("avatar/update/" . $this->page . "");
        $form->required(true);
        $form->col(6);
        $form->textInput(
            "avatarName",
            "Name",
            125,
            $avatar->getAvatarName(),
            "Madpeter Zond [You can leave out Resident]"
        );
        $form->textInput(
            "avatarUUID",
            "SL UUID",
            3,
            $avatar->getAvatarUUID(),
            "SecondLife UUID [found on their SL profile]"
        );
        $this->setSwapTag("page_content", $form->render("Update", "primary"));
    }
}
