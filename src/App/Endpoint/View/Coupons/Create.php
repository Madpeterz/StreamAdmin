<?php

namespace App\Endpoint\View\Coupons;

use App\Models\Marketplacecoupons;
use App\Models\Set\MarketplacecouponsSet;
use YAPF\Bootstrap\Template\Form;

class Create extends View
{
    public function process(): void
    {
        if ($this->siteConfig->getSession()->getOwnerLevel() == false) {
            $this->output->redirect("Coupons?bubblemessage=incorrect access level&bubbletype=warning");
            return;
        }
        $this->output->addSwapTagString("html_title", "~ Create");

        $this->setSwapTag("page_actions", " ");

        $form = new Form();
        $form->target("Coupons/Create");
        $form->required(true);
        $form->numberInput("cost", "Cost L$", null, 6, "Cost on marketplace");
        $form->numberInput("listingid", "Listing id", null, 11, "Marketplace listing id");
        $form->numberInput("credit", "Credit [To be added]", null, 6, "How much credit to give");
        $this->setSwapTag("page_content", $form->render("Create", "primary"));
    }
}
