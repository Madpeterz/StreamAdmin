<?php

namespace App\Endpoint\View\Transactions;

use App\Models\Avatar;
use App\Models\Sets\AvatarSet;
use App\Models\Sets\PackageSet;
use App\Models\Sets\RegionSet;
use App\Models\Sets\TransactionsSet;
use YAPF\Bootstrap\Template\Grid;

abstract class RenderList extends View
{
    protected TransactionsSet $transaction_set;
    protected PackageSet $package_set;
    protected RegionSet $region_set;
    protected AvatarSet $avatar_set;
    protected AvatarSet $transactionFor;

    public function __construct()
    {
        parent::__construct();
        $this->transaction_set = new TransactionsSet();
        $this->package_set = new PackageSet();
        $this->region_set = new RegionSet();
        $this->avatar_set = new AvatarSet();
        $this->transactionFor = new AvatarSet();
    }

    public function loadRequired(): void
    {
        $this->avatar_set = $this->transaction_set->relatedAvatar();
        $this->package_set = $this->transaction_set->relatedPackage();
        $this->region_set = $this->transaction_set->relatedRegion();
        $this->transactionFor = new AvatarSet();
        $this->transactionFor->loadFromIds($this->transaction_set->uniqueTargetAvatars());
        $avatarids = $this->avatar_set->uniqueIds();
        foreach ($this->transactionFor as $avatar) {
            if (in_array($avatar->getId(), $avatarids) == true) {
                continue;
            }
            $this->avatar_set->addToCollected($avatar);
        }
    }

    public function loadTransactionsFromAvatar(Avatar $avatar): void
    {
        $this->transaction_set->loadByAvatarLink($avatar->getId(), 150);
    }

    public function renderTransactionTable(): string
    {
        $table_head = ["id", "Transaction UID", "Client", "Package", "Region", "Amount", "Datetime", "Mode"];
        $this->loadRequired();
        $table_head = [
            "id",
            "Transaction UID",
            "Payer",
            "Receiver",
            "Package",
            "Region",
            "Amount",
            "Datetime",
            "Type",
        ];
        if ($this->siteConfig->getSession()->getOwnerLevel() == true) {
            $table_head[] = "Remove";
        }

        $creditTransactions = [];
        $tableBodyMarketplace = [];
        foreach ($this->transaction_set as $transaction) {
            if ($transaction->getViaMarketplace() == true) {
                $creditTransactions[] = $transaction->getId();
                continue;
            }
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
            $entry = [];
            $entry[] = $transaction->getId();
            $entry[] = $transaction->getTransactionUid();
            $payer = $this->avatar_set->getObjectByID($transaction->getAvatarLink());
            $entry[] = '<a href="[[SITE_URL]]search?search=' . $payer->getAvatarName() . '">'
                . $payer->getAvatarName() . '</a>';
            $proxy = false;
            if ($transaction->getTargetAvatar() != null) {
                $target = $this->avatar_set->getObjectByID($transaction->getTargetAvatar());
                if ($target->getId() != $payer->getId()) {
                    $proxy = true;
                }
                $entry[] = '<a href="[[SITE_URL]]search?search=' . $target->getAvatarName() . '">'
                    . $target->getAvatarName() . '</a>';
            } else {
                $entry[] = " - ";
            }
            $entry[] = $packagename;
            $entry[] = $regionname;
            $entry[] = $transaction->getAmount();
            $entry[] = date('d/m/Y @ G:i:s', $transaction->getUnixtime());
            $type = "<i class=\"fas fa-user-plus\"></i> New";
            if ($transaction->getRenew() == 1) {
                $type = "<i class=\"fas fa-redo-alt\"></i> Renew";
                if ($proxy == true) {
                    $type = "<i class=\"fas fa-redo-alt\"></i> Proxy";
                }
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

        $tableBodyMarketplace = [];
        $tableHeadMarketplace = [
            "id",
            "Transaction UID",
            "Payer",
            "Receiver",
            "Notes",
            "Amount",
        ];
        if ($this->siteConfig->getSession()->getOwnerLevel() == true) {
            $tableHeadMarketplace[] = "Remove";
        }
        foreach ($creditTransactions as $transactionid) {
            $transaction = $this->transaction_set->getObjectByID($transactionid);
            if ($transaction->getViaMarketplace() == false) {
                continue;
            }
            $entry = [];
            $entry[] = $transactionid;
            $entry[] = $transaction->getTransactionUid();
            $payer = $this->avatar_set->getObjectByID($transaction->getAvatarLink());
            $entry[] = '<a href="[[SITE_URL]]search?search=' . $payer->getAvatarName() . '">'
                . $payer->getAvatarName() . '</a>';
            if ($transaction->getTargetAvatar() != null) {
                $target = $this->avatar_set->getObjectByID($transaction->getTargetAvatar());
                $entry[] = '<a href="[[SITE_URL]]search?search=' . $target->getAvatarName() . '">'
                    . $target->getAvatarName() . '</a>';
            } else {
                $entry[] = " - ";
            }
            $notes = $transaction->getNotes();
            if ($notes === null) {
                $notes = "-";
            }
            $entry[] = $notes;
            $entry[] = $transaction->getAmount();
            if ($this->siteConfig->getSession()->getOwnerLevel() == 1) {
                $entry[] = "<button type='button' 
                data-actiontitle='Remove transaction " . $transaction->getTransactionUid() . "' 
                data-actiontext='Remove transaction' 
                data-actionmessage='This has no change on the rental time!' 
                data-targetendpoint='[[SITE_URL]]Transactions/Remove/" . $transaction->getTransactionUid() . "' 
                class='btn btn-danger confirmDialog'>Remove</button></a>";
            }
            $tableBodyMarketplace[] = $entry;
        }

        $grid = new Grid();
        $subgrid = new Grid();
        $subgrid->addContent("<h4>Marketplace transactions</h4>", 12);
        $subgrid->addContent($this->renderDatatable($tableHeadMarketplace, $tableBodyMarketplace), 12);

        $subgrid2 = new Grid();
        $subgrid2->addContent("<h4>SL transactions</h4>", 12);
        $subgrid2->addContent($this->renderDatatable($table_head, $table_body), 12);
        $grid->addContent($subgrid2->getOutput(), 6);
        $grid->addContent($subgrid->getOutput(), 6);
        return $grid->getOutput();
    }

    public function process(): void
    {
        $this->setSwapTag("page_content", $this->renderTransactionTable());
    }
}
