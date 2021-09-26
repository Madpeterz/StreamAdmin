<?php

namespace App\Endpoint\View\Tree;

use App\Template\Form;

class Create extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", " ~ Create");
        $this->output->addSwapTagString("page_title", " : New");
        $this->setSwapTag("page_actions", "");
        $form = new Form();
        $form->target("tree/create");
        $form->required(true);
        $form->col(6);
        $form->textInput("name", "Name", 30, "", "Name");
        $form->textureInput("textureWaiting", "Waiting", 36, null, "UUID when waiting for a user");
        $form->textureInput("textureInuse", "In use", 36, null, "UUID when activly being used");
        $this->setSwapTag("page_content", $form->render("Create", "primary"));
    }
}
