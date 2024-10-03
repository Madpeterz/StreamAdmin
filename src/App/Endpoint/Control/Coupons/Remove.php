<?php

namespace App\Endpoint\Control\Coupons;

use App\Models\Marketplacecoupons;
use App\Template\ControlAjax;

class Remove extends ControlAjax
{
    public function process(): void
    {
        $this->setSwapTag("redirect", null);
        if ($this->siteConfig->getSession()->getOwnerLevel() == false) {
            $this->failed("Sorry only owners can make changes to Coupons");
            return;
        }
        $accept = $this->input->post("accept")->asString();
        if ($accept == null) {
            $this->failed("Accept button not triggered");
            return;
        }
        $coupon = new Marketplacecoupons();
        if ($coupon->loadid($this->siteConfig->getPage())->status == false) {
            $this->failed("Unable to find the coupon");
            return;
        }
        $removeStatus = $coupon->removeEntry();
        if ($removeStatus->status == false) {
            $this->failed(sprintf("Unable to remove coupon: %1\$s", $removeStatus->message));
            return;
        }
        $this->setSwapTag("redirect", "Coupons");
        $this->redirectWithMessage("Coupon removed");
    }
}
