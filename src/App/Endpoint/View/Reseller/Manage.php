<?php

namespace App\Endpoint\View\Reseller;

use App\Models\Avatar;
use App\Models\Reseller;
use YAPF\Bootstrap\Template\Form;

class Manage extends View
{
    public function process(): void
    {
        $this->output->addSwapTagString("html_title", " ~ Manage");
        $this->output->addSwapTagString("page_title", " Editing reseller");

        $this->setSwapTag("page_actions", "<a href='[[SITE_URL]]reseller/remove/"
        . $this->siteConfig->getPage() . "'><button type='button' class='btn btn-danger'>Remove</button></a>");

        $avatar = new Avatar();
        $reseller = new Reseller();
        if ($reseller->loadID($this->siteConfig->getPage()) == false) {
            $this->output->redirect("reseller?bubblemessage=unable to find reseller&bubbletype=warning");
        }
        $avatar->loadID($reseller->getAvatarLink());
        $this->output->addSwapTagString("page_title", ":" . $avatar->getAvatarName());
        if ($this->siteConfig->getSlConfig()->getOwnerAvatarLink() == $avatar->getId()) {
            $this->setSwapTag("page_actions", "");
        }
        $form = new Form();
        $form->target("reseller/update/" . $this->siteConfig->getPage() . "");
        $form->required(true);
        $form->col(6);
            $form->select("allowed", "Allow", $reseller->getAllowed(), [false => "No",true => "Yes"]);
            $form->numberInput("rate", "Rate (as %)", $reseller->getRate(), 3, "Max 100");
        $this->setSwapTag("page_content", $form->render("Update", "primary"));
    }
}
