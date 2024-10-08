<?php

namespace App\Endpoint\View\Template;

use YAPF\Bootstrap\Template\Form;
use YAPF\Bootstrap\Template\PagedInfo;

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
        $form->textarea("detail", "Template [Object+Bot IM]", 800, "", "Use swap tags as the placeholders!", 17);
        $form->col(6);
        $form->textarea("notecardDetail", "Notecard template", 5000, "", "Use swap tags as the placeholder", 17);
        $this->pages["Create"] = $form->render("Create", "primary");
        $this->use_paged_swaps = true;
        $this->getSwaps();
        $paged = new PagedInfo();
        $this->setSwapTag("page_content", $paged->render($this->pages));
    }
}
