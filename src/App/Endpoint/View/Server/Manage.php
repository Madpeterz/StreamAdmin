<?php

namespace App\Endpoint\View\Server;

use App\Models\Server;
use YAPF\Bootstrap\Template\Form;

class Manage extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", " ~ Manage");
        $this->output->addSwapTagString("page_title", " Editing");
        $this->setSwapTag("page_actions", ""
        . "<button type='button' 
        data-actiontitle='Remove server " . $this->siteConfig->getPage() . "' 
        data-actiontext='Remove server' 
        data-actionmessage='This will fail if there is anything using this server!' 
        data-targetendpoint='[[SITE_URL]]Server/Remove/" . $this->siteConfig->getPage() . "' 
        class='btn btn-danger confirmDialog'>Remove</button></a>");

        $server = new Server();
        $this->output->addSwapTagString("page_title", " :" . $server->getDomain());
        $form = new Form();
        $form->target("server/update/" . $this->siteConfig->getPage() . "");
        $form->required(true);
        $form->group("Basic config");
        $form->col(6);
        $form->textInput("domain", "Domain", 30, $server->getDomain(), "ip or uncloudflared proxyed domain/subdomain");
        $form->textInput(
            "controlPanelURL",
            "Control panel",
            200,
            $server->getControlPanelURL(),
            "URL to the control panel"
        );
        $this->setSwapTag("page_content", $form->render("Update", "primary"));
    }
}
