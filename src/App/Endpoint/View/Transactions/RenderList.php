<?php

namespace App\Endpoint\View\Transactions;

use App\Models\Avatar;
use App\Models\Sets\AvatarSet;
use App\Models\Sets\PackageSet;
use App\Models\Sets\RegionSet;
use App\Models\Sets\TransactionsSet;

abstract class RenderList extends View
{
    protected TransactionsSet $transaction_set;
    protected PackageSet $package_set;
    protected RegionSet $region_set;
    protected AvatarSet $avatar_set;

    public function __construct()
    {
        parent::__construct();
        $this->transaction_set = new TransactionsSet();
        $this->package_set = new PackageSet();
        $this->region_set = new RegionSet();
        $this->avatar_set = new AvatarSet();
    }

    public function loadRequired(): void
    {
        $this->avatar_set = $this->transaction_set->relatedAvatar();
        $this->package_set = $this->transaction_set->relatedPackage();
        $this->region_set = $this->transaction_set->relatedRegion();
    }

    public function loadTransactionsFromAvatar(Avatar $avatar): void
    {
        $this->transaction_set->loadByAvatarLink($avatar->getId(), 150);
    }

    public function renderTransactionTable(): string
    {
        $table_head = ["id","Transaction UID","Client","Package","Region","Amount","Datetime","Mode"];
        $this->loadRequired();
        $table_head = [
            "id",
            "Transaction UID",
            "Client",
            "Package",
            "Region",
            "Amount",
            "Datetime",
            "Type",
        ];
        if ($this->siteConfig->getSession()->getOwnerLevel() == true) {
            $table_head[] = "Remove";
        }
        $table_body = [];
        foreach ($this->transaction_set as $transaction) {
            $packagename = "?";
            if ($transaction->getPackageLink() != null) {
                $package = $this->package_set->getObjectByID($transaction->getPackageLink());
                if ($package != null) {
                    $packagename = $package->getName();
                }
            }
            $regionname = "?";
            if ($transaction->getRegionLink() != null) {
                $region = $this->region_set->getObjectByID($transaction->getRegionLink());
                if ($region != null) {
                    $regionname = $region->getName();
                }
            }
            $avatar = $this->avatar_set->getObjectByID($transaction->getAvatarLink());
            $entry = [];
            $entry[] = $transaction->getId();
            $entry[] = $transaction->getTransactionUid();
            $entry[] = '<a href="[[SITE_URL]]search?search=' . $avatar->getAvatarName() . '">'
            . $avatar->getAvatarName() . '</a>';
            $entry[] = $packagename;
            $entry[] = $regionname;
            $entry[] = $transaction->getAmount();
            $entry[] = date('d/m/Y @ G:i:s', $transaction->getUnixtime());
            $type = "<i class=\"fas fa-user-plus\"></i> New";
            if ($transaction->getRenew() == 1) {
                $type = "<i class=\"fas fa-redo-alt\"></i> Renew";
            }
            if ($transaction->getViaHud() == true) {
                $type = '<span data-toggle="tooltip" data-placement="bottom" title="
                ' . $transaction->getSLtransactionUUID() . '"><i class="fab fa-quinscape"></i> Hud</span>';
            }
            $entry[] = $type;
            if ($this->siteConfig->getSession()->getOwnerLevel() == 1) {
                $entry[] = "<button type='button' 
                data-actiontitle='Remove transaction " . $transaction->getTransactionUid() . "' 
                data-actiontext='Remove transaction' 
                data-actionmessage='This has no change on the rental time!' 
                data-targetendpoint='[[SITE_URL]]Transactions/Remove/" . $transaction->getTransactionUid() . "' 
                class='btn btn-danger confirmDialog'>Remove</button></a>";
            }
            $table_body[] = $entry;
        }
        return $this->renderDatatable($table_head, $table_body);
    }

    public function process(): void
    {
        $this->setSwapTag("page_content", $this->renderTransactionTable());
    }
}
