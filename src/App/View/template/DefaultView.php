<?php

namespace App\View\Template;

use App\TemplateSet;

class DefaultView extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("page_title", " Templates");
        $table_head = ["id","name"];
        $table_body = [];
        $template_set = new TemplateSet();
        $template_set->loadAll();

        foreach ($template_set->getAllIds() as $template_id) {
            $tempalte = $template_set->getObjectByID($template_id);
            $entry = [];
            $entry[] = $tempalte->getId();
            $entry[] = '<a href="[[url_base]]template/manage/' . $tempalte->getId() . '">'
            . $tempalte->getName() . '</a>';
            $table_body[] = $entry;
        }
        $this->output->setSwapTagString("page_content", render_datatable($table_head, $table_body));
    }
}
