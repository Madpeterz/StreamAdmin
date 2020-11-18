<?php

namespace App\View\Avatar;

use App\Template\Form as Form;

class Create extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", "~ Create");
        $this->output->setSwapTagString("page_title", "Create new avatar");
        $this->output->setSwapTagString("page_actions", "");
        $form = new Form();
        $form->target("avatar/create");
        $form->required(true);
        $form->col(6);
            $form->textInput("avatarname", "Name", 125, null, "Madpeter Zond [You can leave out Resident]");
            $form->textInput("avataruuid", "SL UUID", 3, null, "SecondLife UUID [found on their SL profile]");
        $this->output->setSwapTagString("page_content", $form->render("Create", "primary"));
    }
}
