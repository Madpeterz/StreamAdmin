<?php

namespace App\Endpoint\View\Template;

use App\Models\Set\TemplateSet;

class DefaultView extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("page_title", " Templates");
        $table_head = ["id","name"];
        $table_body = [];
        $template_set = new TemplateSet();
        $template_set->loadAll();

        foreach ($template_set as $tempalte) {
            $entry = [];
            $entry[] = $tempalte->getId();
            $entry[] = '<a href="[[SITE_URL]]template/manage/' . $tempalte->getId() . '">'
            . $tempalte->getName() . '</a>';
            $table_body[] = $entry;
        }
        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body));
    }
}
