<?php

$treevender_packages = new treevender_packages();
if ($treevender_packages->loadID($this->page) == true) {
    $treevender = new treevender();
    if ($treevender->loadID($treevender_packages->get_treevenderlink()) == true) {
        $package = new package();
        if ($package->loadID($treevender_packages->getPackagelink()) == true) {
            $this->output->addSwapTagString("html_title", " ~ Remove");
            $this->output->addSwapTagString("page_title", " Remove linked package:" . $package->getName() . " from tree vender:" . $treevender->getName());
            $this->output->setSwapTagString("page_actions", "");

            $form = new form();
            $form->target("tree/removepackage/" . $this->page . "");
            $form->required(true);
            $form->col(6);
            $form->group("Warning");
            $form->textInput("accept", "Type \"Accept\"", 30, "", "This will remove the link to the package");
            $this->output->setSwapTagString("page_content", $form->render("Remove", "danger"));
        } else {
            $this->output->redirect("tree?bubblemessage=Unable to find package&bubbletype=warning");
        }
    } else {
        $this->output->redirect("tree?bubblemessage=Unable to find treevender thats linked to this package link&bubbletype=warning");
    }
} else {
    $this->output->redirect("tree?bubblemessage=Unable to find linked treevender package&bubbletype=warning");
}
