<?php

namespace App\Endpoint\View\Textureconfig;

use YAPF\Bootstrap\Template\Form;
use App\Models\Textureconfig;

class Manage extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", " ~ Manage");
        $this->output->addSwapTagString("page_title", " Editing texture pack");
        $this->setSwapTag("page_actions", ""
        . "<button type='button' 
        data-actiontitle='Remove texture pack " . $this->siteConfig->getPage() . "' 
        data-actiontext='Remove texture pack' 
        data-actionmessage='Are you sure you want to remove this texture pack?' 
        data-targetendpoint='[[SITE_URL]]Textureconfig/Remove/" . $this->siteConfig->getPage() . "' 
        class='btn btn-danger confirmDialog'>Remove</button></a>");

        $textureconfig = new Textureconfig();
        if ($textureconfig->loadID($this->siteConfig->getPage()) == false) {
            $this->output->redirect("package?bubblemessage=unable to find package&bubbletype=warning");
            return;
        }
        $this->output->addSwapTagString("page_title", ":" . $textureconfig->getName());
        $form = new Form();
        $form->target("textureconfig/update/" . $this->siteConfig->getPage() . "");
        $form->required(true);
        $form->col(6);
            $form->textInput("name", "Name", 30, $textureconfig->getName(), "Name");
            $form->textureInput(
                "gettingDetails",
                "Fetching details",
                36,
                $textureconfig->getGettingDetails(),
                "UUID of texture"
            );
            $form->textureInput(
                "requestDetails",
                "Request details",
                36,
                $textureconfig->getRequestDetails(),
                "UUID of texture"
            );
        $form->split();
        $form->col(6);
            $form->textureInput("offline", "Offline", 36, $textureconfig->getOffline(), "UUID of texture");
            $form->textureInput(
                "waitOwner",
                "Waiting for owner",
                36,
                $textureconfig->getWaitOwner(),
                "UUID of texture"
            );
            $form->textureInput("inUse", "InUse", 36, $textureconfig->getInUse(), "UUID of texture");
        $form->col(6);
            $form->textureInput(
                "makePayment",
                "Request payment",
                36,
                $textureconfig->getMakePayment(),
                "UUID of texture"
            );
            $form->textureInput(
                "stockLevels",
                "Stock levels",
                36,
                $textureconfig->getStockLevels(),
                "UUID of texture"
            );
            $form->textureInput("renewHere", "Renew here", 36, $textureconfig->getRenewHere(), "UUID of texture");
            $form->textureInput("proxyRenew", "Proxy Renew", 36, $textureconfig->getProxyRenew(), "UUID of texture");
        $this->setSwapTag("page_content", $form->render("Update", "primary"));
    }
}
