<?php

namespace App\Endpoint\View\Datatables;

use App\Models\Datatable;
use YAPF\Bootstrap\Template\Form as Form;

class Manage extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", "~ Manage");
        $this->setSwapTag("page_title", "Editing datatable");
        $this->setSwapTag("page_actions", "");
        $datatable = new Datatable();
        if ($datatable->loadid($this->siteConfig->getPage()) == false) {
            $this->output->redirect("datatables?bubblemessage=Unable to find datatable&bubbletype=warning");
            return;
        }
        $this->output->addSwapTagString("page_title", " : " . $datatable->getName() . " 
        [" . $datatable->getId() . "]");
        $form = new form();
        $form->target("datatables/update/" . $this->siteConfig->getPage() . "");
        $form->required(true);
        $elements = explode(",", $datatable->getCols());
        $bits = [];
        foreach ($elements as $enl) {
            $sub = explode("=", $enl);
            $bits[$sub[0]] = $sub[1];
        }
        $form->select("col", "Sort by col", $datatable->getCol(), $bits);
        $form->select("dir", "Direction", $datatable->getDir(), ["desc" => "Descending","asc" => "Ascending"]);
        $this->setSwapTag("page_content", $form->render("Update", "success"));
    }
}
