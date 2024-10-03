<?php

namespace App\Endpoint\Control\Coupons;

use App\Models\Marketplacecoupons;
use App\Models\Sets\MarketplacecouponsSet;
use App\Template\ControlAjax;

class Update extends ControlAjax
{
    public function process(): void
    {
        $this->setSwapTag("redirect", null);
        if ($this->siteConfig->getSession()->getOwnerLevel() == false) {
            $this->failed("Sorry only owners can make changes to Coupons");
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

        $coupon = new Marketplacecoupons();
        if ($coupon->loadid($this->siteConfig->getPage())->status == false) {
            $this->failed("Unable to find the coupon");
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
        $expected_count = 0;
        if ($coupon->getListingid() == $listingid) {
            $expected_count = 1;
        }
        if ($count_check->status == false) {
            $this->failed("Unable to check if listing id in use");
            return;
        }
        if ($count_check->items != $expected_count) {
            $this->failed("Selected listing id is already in use");
            return;
        }
        $coupon->setListingid($listingid);
        $coupon->setCost($cost);
        $coupon->setCredit($credit);
        $update_status = $coupon->updateEntry();
        if ($update_status->status == false) {
            $this->failed(sprintf("Unable to update coupon: %1\$s", $update_status->message));
            return;
        }
        $this->setSwapTag("redirect", "Coupons");
        $this->redirectWithMessage("Coupon updated");
    }
}
