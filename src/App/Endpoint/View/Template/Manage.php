<?php

namespace App\Endpoint\View\Template;

use App\Template\Form;
use App\R7\Model\Template;
use App\Template\PagedInfo;

class Manage extends View
{
    public function process(): void
    {
        global $pages;
        $this->output->addSwapTagString("html_title", " ~ Manage");
        $this->output->addSwapTagString("page_title", " Manage");

        $this->setSwapTag("page_actions", ""
        . "<button type='button' 
        data-actiontitle='Remove template " . $this->page . "' 
        data-actiontext='Remove template' 
        data-actionmessage='This will fail is the template is being used by a package or API event' 
        data-targetendpoint='[[url_base]]Template/Remove/" . $this->page . "' 
        class='btn btn-danger confirmDialog'>Remove</button></a>");

        $template = new Template();
        if ($template->loadID($this->page) == false) {
            $this->output->redirect("template?bubblemessage=unable to find template&bubbletype=warning");
            return;
        }
        $this->output->addSwapTagString("page_title", ":" . $template->getName());
        $form = new Form();
        $form->target("template/update/" . $this->page . "");
        $form->required(true);
        $form->col(3);
            $form->textInput("name", "Name", 30, $template->getName(), "Name");
        $form->split();
        $form->col(6);
            $form->textarea(
                "detail",
                "Template [Object+Bot IM]",
                800,
                $template->getDetail(),
                "Use swap tags as the placeholders! max length 800",
                17
            );
        $form->col(6);
            $form->textarea(
                "notecardDetail",
                "Notecard template",
                5000,
                $template->getNotecardDetail(),
                "Use swap tags as the placeholder",
                17
            );
        $pages = [];
        $pages["Manage"] = $form->render("Update", "primary");
        include ROOTFOLDER . "/App/Flags/swaps_table_paged.php";
        include ROOTFOLDER . "/App/Endpoint/View/Shared/swaps_table.php";
        $paged = new PagedInfo();
        $this->setSwapTag("page_content", $paged->render($pages));
    }
}
