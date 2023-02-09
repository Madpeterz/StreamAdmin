<?php

namespace App\Endpoint\View\Server;

use YAPF\Bootstrap\Template\Form;

class Create extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", " ~ Create");
        $this->output->addSwapTagString("page_title", " : New");
        $this->setSwapTag("page_actions", "");

        $form = new form();
        $form->target("server/create");
        $form->required(true);
        $form->group("Basic config");
        $form->col(6);
            $form->textInput("domain", "Domain", 30, "", "ip or uncloudflared proxyed domain/subdomain");
            $form->textInput("controlPanelURL", "Control panel", 200, "", "URL to the control panel");
        $this->setSwapTag("page_content", $form->render("Create", "primary"));
    }
}
