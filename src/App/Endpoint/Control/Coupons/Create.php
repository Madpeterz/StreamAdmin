<?php

namespace App\Endpoint\Control\Coupons;

use App\Models\Marketplacecoupons;
use App\Models\Sets\MarketplacecouponsSet;
use App\Template\ControlAjax;

class Create extends ControlAjax
{
    public function process(): void
    {
        $this->setSwapTag("redirect", null);
        if ($this->siteConfig->getSession()->getOwnerLevel() == false) {
            $this->failed("Sorry only owners can create Coupons");
            return;
        }

        $cost = $this->input->post("cost")->checkInRange(1, 99999)->asInt();
        if ($cost === null) {
            $this->failed("cost not accepted:" . $this->input->getWhyFailed());
            return;
        }
        $listingid = $this->input->post("listingid")->checkInRange(1, 9999999999)->asInt();
        if ($listingid === null) {
            $this->failed("listingid not accepted:" . $this->input->getWhyFailed());
            return;
        }
        $credit = $this->input->post("credit")->checkInRange(1, 99999)->asInt();
        if ($credit === null) {
            $this->failed("credit not accepted:" . $this->input->getWhyFailed());
            return;
        }
        $whereConfig = [
            "fields" => ["listingid"],
            "values" => [$listingid],
            "types" => ["i"],
            "matches" => ["="],
        ];
        $MarketplacecouponsSet = new MarketplacecouponsSet();
        $count_check = $MarketplacecouponsSet->countInDB($whereConfig);
        if ($count_check->status == false) {
            $this->failed("Unable to check if listing id in use");
            return;
        }
        if ($count_check->items != 0) {
            $this->failed("Selected listing id is already in use");
            return;
        }
        $coupon = new Marketplacecoupons();
        $coupon->setListingid($listingid);
        $coupon->setCost($cost);
        $coupon->setCredit($credit);
        $createStatus = $coupon->createEntry();
        if ($createStatus->status == false) {
            $this->failed(sprintf("Unable to create coupon: %1\$s", $createStatus->message));
            return;
        }
        $this->setSwapTag("redirect", "Coupons");
        $this->redirectWithMessage("Coupon created");
    }
}
