<?php

namespace App\Endpoint\View\Transactions;

use App\R7\Model\Avatar;
use App\R7\Set\AvatarSet;
use App\R7\Set\PackageSet;
use App\R7\Set\RegionSet;
use App\R7\Set\TransactionsSet;

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
        $this->avatar_set = new AvatarSet();
        $this->avatar_set->loadIds($this->transaction_set->getAllByField("avatarLink"));
        $this->package_set->loadIds($this->transaction_set->getAllByField("packageLink"));
        $this->region_set->loadIds($this->transaction_set->getAllByField("regionLink"));
    }

    public function loadTransactionsFromAvatar(Avatar $avatar): void
    {
        $this->transaction_set->loadByField("avatarLink", $avatar->getId());
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
        if ($this->session->getOwnerLevel() == true) {
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
            $entry[] = '<a href="[[url_base]]search?search=' . $avatar->getAvatarName() . '">'
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
            if ($this->session->getOwnerLevel() == 1) {
                $entry[] = "<a href=\"[[url_base]]transactions/remove/" . $transaction->getTransactionUid() . "\">"
                . "<button type=\"button\" class=\"btn btn-danger btn-sm\"><i class=\"fas fa-minus-circle\"></i>"
                . "</button></a>";
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
