<?php

namespace App\Endpoint\View\Template;

use App\Template\Form;

class Create extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", " ~ Create");
        $this->output->addSwapTagString("page_title", " Create new");
        $this->setSwapTag("page_actions", "");
        $form = new Form();
        $form->target("template/create");
        $form->required(true);
        $form->col(3);
        $form->textInput("name", "Name", 30, "", "Name");
        $form->split();
        $form->col(6);
        $form->textarea(
            "detail",
            "Template [Object+Bot IM]",
            800,
            "",
            "Use swap tags as the placeholders! max length 800"
        );
        $form->col(6);
        $form->textarea("notecardDetail", "Notecard template", 2000, "", "Use swap tags as the placeholder");
        $this->setSwapTag("page_content", $form->render("Create", "primary"));
        include "../App/View/Shared/swaps_table.php";
    }
}
