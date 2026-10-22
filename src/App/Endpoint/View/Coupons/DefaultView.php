<?php

namespace App\Endpoint\View\Coupons;

use App\Models\Set\MarketplacecouponsSet;

class DefaultView extends View
{
    public function process(): void
    {
        $tableHead = ["id", "Listing id", "Cost", "Credit", "Claims", "Last claimed"];
        $tableBody = [];
        $coupons = new MarketplacecouponsSet();
        $coupons->loadAll();
        foreach ($coupons as $entry) {
            $dat = [];
            $dat[] = $entry->_Id;
            if ($this->siteConfig->getSession()->getOwnerLevel() == true) {
                $dat[] = '<a href="[[SITE_URL]]Coupons/Manage/' . $entry->_Id . '">' .
                    $entry->_Listingid . '</a>';
            } else {
                $dat[] = $entry->_Listingid;
            }
            $dat[] = $entry->_Cost;
            $dat[] = $entry->_Credit;
            $dat[] = $entry->_Claims;
            $dat[] = date('d/m/Y @ G:i:s', $entry->_LastClaim);
            $tableBody[] = $dat;
        }
        $this->setSwapTag("page_content", $this->renderDatatable($tableHead, $tableBody));
    }
}
