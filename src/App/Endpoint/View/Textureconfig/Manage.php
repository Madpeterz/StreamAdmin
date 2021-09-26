<?php

namespace App\Endpoint\View\Textureconfig;

use App\Template\Form;
use App\R7\Model\Textureconfig;

class Manage extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", " ~ Manage");
        $this->output->addSwapTagString("page_title", " Editing texture pack");
        $this->setSwapTag(
            "page_actions",
            "<a href='[[url_base]]textureconfig/remove/" . $this->page
            . "'><button type='button' class='btn btn-danger'>Remove</button></a>"
        );
        $textureconfig = new Textureconfig();
        if ($textureconfig->loadID($this->page) == false) {
            $this->output->redirect("package?bubblemessage=unable to find package&bubbletype=warning");
            return;
        }
        $this->output->addSwapTagString("page_title", ":" . $textureconfig->getName());
        $form = new Form();
        $form->target("textureconfig/update/" . $this->page . "");
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
