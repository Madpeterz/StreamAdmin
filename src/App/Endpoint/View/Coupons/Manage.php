<?php

namespace App\Endpoint\View\Coupons;

use App\Models\Marketplacecoupons;
use App\Models\Set\MarketplacecouponsSet;
use YAPF\Bootstrap\Template\Form;

class Manage extends View
{
    public function process(): void
    {
        if ($this->siteConfig->getSession()->getOwnerLevel() == false) {
            $this->output->redirect("Coupons?bubblemessage=incorrect access level&bubbletype=warning");
            return;
        }
        $this->output->addSwapTagString("html_title", "~ Manage");

        $this->setSwapTag("page_actions", ""
            . "<button type='button' 
        data-actiontitle='Remove coupon " . $this->siteConfig->getPage() . "' 
        data-actiontext='Remove coupon' 
        data-actionmessage='are you sure' 
        data-targetendpoint='[[SITE_URL]]Coupons/Remove/" . $this->siteConfig->getPage() . "' 
        class='btn btn-danger confirmDialog'>Remove</button></a>");

        $coupon = new Marketplacecoupons();
        $coupon->loadId($this->siteConfig->getPage());

        if ($coupon->isLoaded() == false) {
            $this->output->redirect("Coupons?bubblemessage=unable to find coupon&bubbletype=warning");
            return;
        }

        $form = new Form();
        $form->target("Coupons/Update/" . $this->siteConfig->getPage());
        $form->required(true);
        $form->numberInput("cost", "Cost L$", $coupon->getCost(), 6, "Cost on marketplace");
        $form->numberInput("listingid", "Listing id", $coupon->getListingid(), 11, "Marketplace listing id");
        $form->numberInput("credit", "Credit [To be added]", $coupon->getCredit(), 6, "How much credit to give");
        $this->setSwapTag("page_content", $form->render("Update", "primary"));
    }
}
