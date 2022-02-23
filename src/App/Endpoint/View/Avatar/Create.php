<?php

namespace App\Endpoint\View\Avatar;

use YAPF\Bootstrap\Template\Form as Form;

class Create extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", "~ Create");
        $this->setSwapTag("page_title", "Create new avatar");
        $this->setSwapTag("page_actions", "");
        $form = new Form();
        $form->target("avatar/create");
        $form->required(true);
        $form->col(6);
            $form->textInput("avatarName", "Name", 125, null, "Madpeter Zond [You can leave out Resident]");
            $form->textInput("avatarUUID", "SL UUID", 36, null, "SecondLife UUID [found on their SL profile]");
        $this->setSwapTag("page_content", $form->render("Create", "primary"));
    }
}
