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
            $dat[] = $entry->getId();
            if ($this->siteConfig->getSession()->getOwnerLevel() == true) {
                $dat[] = '<a href="[[SITE_URL]]Coupons/Manage/' . $entry->getId() . '">' .
                    $entry->getListingid() . '</a>';
            } else {
                $dat[] = $entry->getListingid();
            }
            $dat[] = $entry->getCost();
            $dat[] = $entry->getCredit();
            $dat[] = $entry->getClaims();
            $dat[] = date('d/m/Y @ G:i:s', $entry->getLastClaim());
            $tableBody[] = $dat;
        }
        $this->setSwapTag("page_content", $this->renderDatatable($tableHead, $tableBody));
    }
}
