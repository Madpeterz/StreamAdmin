<?php

$this->output->addSwapTagString("html_title", " ~ Manage");
$this->output->addSwapTagString("page_title", " Editing texture pack");
$this->output->setSwapTagString("page_actions", "<a href='[[url_base]]textureconfig/remove/" . $this->page . "'><button type='button' class='btn btn-danger'>Remove</button></a>");
$textureconfig = new textureconfig();
if ($textureconfig->load($this->page) == true) {
    $this->output->addSwapTagString("page_title", ":" . $textureconfig->getName());
    $form = new form();
    $form->target("textureconfig/update/" . $this->page . "");
    $form->required(true);
    $form->col(6);
        $form->textInput("name", "Name", 30, $textureconfig->getName(), "Name");
        $form->textureInput("getting_details", "Fetching details", 36, $textureconfig->get_getting_details(), "UUID of texture");
        $form->textureInput("request_details", "Request details", 36, $textureconfig->get_request_details(), "UUID of texture");
    $form->split();
    $form->col(6);
        $form->textureInput("offline", "Offline", 36, $textureconfig->get_offline(), "UUID of texture");
        $form->textureInput("wait_owner", "Waiting for owner", 36, $textureconfig->get_wait_owner(), "UUID of texture");
        $form->textureInput("inuse", "Inuse", 36, $textureconfig->get_inuse(), "UUID of texture");
        $form->textureInput("treevend_waiting", "Tree vend [Wait]", 36, $textureconfig->get_treevend_waiting(), "UUID of texture");
    $form->col(6);
        $form->textureInput("make_payment", "Request payment", 36, $textureconfig->get_make_payment(), "UUID of texture");
        $form->textureInput("stock_levels", "Stock levels", 36, $textureconfig->get_stock_levels(), "UUID of texture");
        $form->textureInput("renew_here", "Renew here", 36, $textureconfig->get_renew_here(), "UUID of texture");
        $form->textureInput("proxyrenew", "Proxy Renew", 36, $textureconfig->get_proxyrenew(), "UUID of texture");
    $this->output->setSwapTagString("page_content", $form->render("Update", "primary"));
} else {
    $this->output->redirect("package?bubblemessage=unable to find package&bubbletype=warning");
}
