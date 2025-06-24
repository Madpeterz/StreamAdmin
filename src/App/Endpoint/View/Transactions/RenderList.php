<?php

namespace App\Endpoint\View\Transactions;

use App\Models\Avatar;
use App\Models\Set\AvatarSet;
use App\Models\Set\PackageSet;
use App\Models\Set\RegionSet;
use App\Models\Set\TransactionsSet;
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

    protected array $tableHead = [];
    protected array $tableBody = [];
    protected function createNormalTableHead(): void
    {
        $this->tableHead = [
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
            $this->tableHead[] = "Remove";
        }
    }
    protected function getTranactionPackageName(?int $packageLink = null): string
    {
        $packagename = "?";
        if ($packageLink != null) {
            $package = $this->package_set->getObjectByID($packageLink);
            if ($package != null) {
                $packagename = $package->getName();
            }
        }
        return $packagename;
    }
    protected function getTranactionRegionName(?int $regionLink = null): string
    {
        $regionname = "?";
        if ($regionLink != null) {
            $region = $this->region_set->getObjectByID($regionLink);
            if ($region != null) {
                $regionname = $region->getName();
            }
        }
        return $regionname;
    }
    protected array $creditTransactions = [];
    protected function createNormalTableBody(): void
    {
        $this->tableBody = [];
        foreach ($this->transaction_set as $transaction) {
            if ($transaction->getViaMarketplace() == true) {
                $this->creditTransactions[] = $transaction->getId();
                continue;
            }
            $packagename = $this->getTranactionPackageName($transaction->getPackageLink());
            $regionname = $this->getTranactionRegionName($transaction->getRegionLink());
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
            $tooltip = "<span>";
            if ($transaction->getNotes() !== null) {
                $tooltip = '<span data-toggle="tooltip" data-placement="bottom" title="
                ' . $transaction->getNotes() . '">';
            } elseif ($transaction->getSLtransactionUUID() !== null) {
                $tooltip = '<span data-toggle="tooltip" data-placement="bottom" title="
                ' . $transaction->getSLtransactionUUID() . '">';
            }
            $type = "<i class=\"fas fa-user-plus\"></i> New";
            if ($transaction->getRenew() == 1) {
                $type = "<i class=\"fas fa-redo-alt\"></i> Renew";
                if ($proxy == true) {
                    $type = "<i class=\"fas fa-redo-alt\"></i> Proxy";
                }
            } elseif ($transaction->getViaHud() == true) {
                $type = '<i class="fab fa - quinscape"></i> Hud';
            }
            $entry[] = $tooltip . $type . "</span>";
            if ($this->siteConfig->getSession()->getOwnerLevel() == 1) {
                $entry[] = "<button type='button' 
                data-actiontitle='Remove transaction " . $transaction->getTransactionUid() . "' 
                data-actiontext='Remove transaction' 
                data-actionmessage='This has no change on the rental time!' 
                data-targetendpoint='[[SITE_URL]]Transactions/Remove/" . $transaction->getTransactionUid() . "' 
                class='btn btn-danger confirmDialog'>Remove</button></a>";
            }
            $this->tableBody[] = $entry;
        }
    }

    protected Grid $normalTransactionsGrid;
    protected function createNormalTransactions(): void
    {
        $this->normalTransactionsGrid = new Grid();
        $this->createNormalTableHead();
        $this->createNormalTableBody();
        $this->normalTransactionsGrid->addContent("<h4>SL transactions</h4><br/>", 12);
        $this->normalTransactionsGrid->addContent($this->renderDatatable($this->tableHead, $this->tableBody), 12);
    }
    protected function createMarketplaceTableHead(): void
    {
        $this->tableHead = [
            "id",
            "Transaction UID",
            "Payer",
            "Receiver",
            "Notes",
            "Datetime",
        ];
        if ($this->siteConfig->getSession()->getOwnerLevel() == true) {
            $this->tableHead[] = "Remove";
        }
    }
    protected function createMarketplaceTableBody(): void
    {
        $this->tableBody = [];
        foreach ($this->creditTransactions as $transactionid) {
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
                $notes = "L$ " . $transaction->getAmount();
            }
            $entry[] = $notes;
            $entry[] = date('d/m/Y @ G:i:s', $transaction->getUnixtime());
            if ($this->siteConfig->getSession()->getOwnerLevel() == 1) {
                $entry[] = "<button type='button' 
                data-actiontitle='Remove transaction " . $transaction->getTransactionUid() . "' 
                data-actiontext='Remove transaction' 
                data-actionmessage='This has no change on the rental time!' 
                data-targetendpoint='[[SITE_URL]]Transactions/Remove/" . $transaction->getTransactionUid() . "' 
                class='btn btn-danger confirmDialog'>Remove</button></a>";
            }
            $this->tableBody[] = $entry;
        }
    }
    protected Grid $marketplaceTransactionsGrid;
    protected function createMarketplaceTransactions(): void
    {
        $this->marketplaceTransactionsGrid = new Grid();
        $this->createMarketplaceTableHead();
        $this->createMarketplaceTableBody();
        $this->marketplaceTransactionsGrid->addContent("<h4>Marketplace transactions</h4><br/>", 12);
        $this->marketplaceTransactionsGrid->addContent($this->renderDatatable($this->tableHead, $this->tableBody), 12);
    }

    public function renderTransactionTable(): string
    {
        $this->loadRequired();
        $this->createNormalTransactions();
        $this->createMarketplaceTransactions();
        $grid = new Grid();
        $grid->addContent($this->normalTransactionsGrid->getOutput(), 12);
        $grid->addContent($this->marketplaceTransactionsGrid->getOutput(), 12);
        return $grid->getOutput();
    }

    public function process(): void
    {
        $this->setSwapTag("page_content", $this->renderTransactionTable());
    }
}
