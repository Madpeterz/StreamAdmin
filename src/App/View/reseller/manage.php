<?php

$this->output->addSwapTagString("html_title", " ~ Manage");
$this->output->addSwapTagString("page_title", " Editing reseller");
$this->output->setSwapTagString("page_actions", "<a href='[[url_base]]reseller/remove/" . $page . "'><button type='button' class='btn btn-danger'>Remove</button></a>");

$avatar = new avatar();
$reseller = new reseller();
if ($reseller->load($page) == true) {
    $avatar->load($reseller->get_avatarlink());
    $this->output->addSwapTagString("page_title", ":" . $avatar->get_avatarname());
    $form = new form();
    $form->target("reseller/update/" . $page . "");
    $form->required(true);
    $form->col(6);
        $form->select("allowed", "Allow", $reseller->get_allowed(), array(false => "No",true => "Yes"));
        $form->numberInput("rate", "Rate (as %)", $reseller->get_rate(), 3, "Max 100");
    $this->output->setSwapTagString("page_content", $form->render("Update", "primary"));
} else {
    $this->output->redirect("reseller?bubblemessage=unable to find reseller&bubbletype=warning");
}
