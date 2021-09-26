<?php

namespace App\Endpoint\View\Textureconfig;

use App\Template\Form;

class Create extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", " ~ Create");
        $this->output->addSwapTagString("page_title", " Create new texture config pack");
        $this->setSwapTag("page_actions", "");
        $form = new Form();
        $form->target("textureconfig/create");
        $form->required(true);
        $form->col(6);
            $form->textInput("name", "Name", 30, "", "Name");
            $form->textInput("gettingDetails", "Fetching details", 36, "", "UUID of texture");
            $form->textInput("requestDetails", "Request details", 36, "", "UUID of texture");
        $form->split();
        $form->col(6);
            $form->textInput("offline", "Offline", 36, "", "UUID of texture");
            $form->textInput("waitOwner", "Waiting for owner", 36, "", "UUID of texture");
            $form->textInput("inUse", "InUse", 36, "", "UUID of texture");
        $form->col(6);
            $form->textInput("makePayment", "Request payment", 36, "", "UUID of texture");
            $form->textInput("stockLevels", "Stock levels", 36, "", "UUID of texture");
            $form->textInput("renewHere", "Renew here", 36, "", "UUID of texture");
            $form->textInput("proxyRenew", "Proxy Renew", 36, "", "UUID of texture");
        $this->setSwapTag("page_content", $form->render("Create", "primary"));
    }
}
