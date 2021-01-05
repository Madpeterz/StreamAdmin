<?php

namespace App\Endpoints\View\Textureconfig;

use App\Template\Form;

class Create extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", " ~ Create");
        $this->output->addSwapTagString("page_title", " Create new texture config pack");
        $this->output->setSwapTagString("page_actions", "");
        $form = new Form();
        $form->target("textureconfig/create");
        $form->required(true);
        $form->col(6);
            $form->textInput("name", "Name", 30, "", "Name");
            $form->textInput("getting_details", "Fetching details", 36, "", "UUID of texture");
            $form->textInput("request_details", "Request details", 36, "", "UUID of texture");
        $form->split();
        $form->col(6);
            $form->textInput("offline", "Offline", 36, "", "UUID of texture");
            $form->textInput("wait_owner", "Waiting for owner", 36, "", "UUID of texture");
            $form->textInput("inuse", "Inuse", 36, "", "UUID of texture");
            $form->textInput("treevend_waiting", "Tree vend [Wait]", 36, "", "UUID of texture");
        $form->col(6);
            $form->textInput("make_payment", "Request payment", 36, "", "UUID of texture");
            $form->textInput("stock_levels", "Stock levels", 36, "", "UUID of texture");
            $form->textInput("renew_here", "Renew here", 36, "", "UUID of texture");
            $form->textInput("proxyrenew", "Proxy Renew", 36, "", "UUID of texture");
        $this->output->setSwapTagString("page_content", $form->render("Create", "primary"));
    }
}
