<?php

$this->output->addSwapTagString("html_title", " ~ Manage");
$this->output->addSwapTagString("page_title", " Editing texture pack");
$this->output->setSwapTagString("page_actions", "<a href='[[url_base]]textureconfig/remove/" . $page . "'><button type='button' class='btn btn-danger'>Remove</button></a>");
$textureconfig = new textureconfig();
if ($textureconfig->load($page) == true) {
    $this->output->addSwapTagString("page_title", ":" . $textureconfig->get_name());
    $form = new form();
    $form->target("textureconfig/update/" . $page . "");
    $form->required(true);
    $form->col(6);
        $form->textInput("name", "Name", 30, $textureconfig->get_name(), "Name");
        $form->texture_input("getting_details", "Fetching details", 36, $textureconfig->get_getting_details(), "UUID of texture");
        $form->texture_input("request_details", "Request details", 36, $textureconfig->get_request_details(), "UUID of texture");
    $form->split();
    $form->col(6);
        $form->texture_input("offline", "Offline", 36, $textureconfig->get_offline(), "UUID of texture");
        $form->texture_input("wait_owner", "Waiting for owner", 36, $textureconfig->get_wait_owner(), "UUID of texture");
        $form->texture_input("inuse", "Inuse", 36, $textureconfig->get_inuse(), "UUID of texture");
        $form->texture_input("treevend_waiting", "Tree vend [Wait]", 36, $textureconfig->get_treevend_waiting(), "UUID of texture");
    $form->col(6);
        $form->texture_input("make_payment", "Request payment", 36, $textureconfig->get_make_payment(), "UUID of texture");
        $form->texture_input("stock_levels", "Stock levels", 36, $textureconfig->get_stock_levels(), "UUID of texture");
        $form->texture_input("renew_here", "Renew here", 36, $textureconfig->get_renew_here(), "UUID of texture");
        $form->texture_input("proxyrenew", "Proxy Renew", 36, $textureconfig->get_proxyrenew(), "UUID of texture");
    $this->output->setSwapTagString("page_content", $form->render("Update", "primary"));
} else {
    $this->output->redirect("package?bubblemessage=unable to find package&bubbletype=warning");
}
