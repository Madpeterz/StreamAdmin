<?php

namespace App\Endpoint\View\Transactions;

use App\R7\Set\AvatarSet;
use App\R7\Set\PackageSet;
use App\R7\Set\RegionSet;
use App\R7\Set\TransactionsSet;

abstract class RenderList extends View
{
    protected $transaction_set = null;
    protected $package_set = null;
    protected $region_set = null;
    protected $avatar_set = null;

    public function __construct()
    {
        parent::__construct();
        $this->transaction_set = new TransactionsSet();
        $this->package_set = new PackageSet();
        $this->region_set = new RegionSet();
        $this->avatar_set = new AvatarSet();
    }

    public function process(): void
    {
        $table_head = ["id","Transaction UID","Client","Package","Region","Amount","Datetime","Mode"];
        if ($this->session->getOwnerLevel() == 1) {
            $table_head[] = "Remove";
        }
        $table_body = [];
        foreach ($this->transaction_set->getAllIds() as $transaction_id) {
            $transaction = $this->transaction_set->getObjectByID($transaction_id);
            $packagename = "";
            if ($transaction->getPackageLink() != null) {
                $package = $this->package_set->getObjectByID($transaction->getPackageLink());
                $packagename = $package->getName();
            }
            $regionname = "";
            if ($transaction->getRegionLink() != null) {
                $region = $this->region_set->getObjectByID($transaction->getRegionLink());
                $regionname = $region->getName();
            }
            $avatar = $this->avatar_set->getObjectByID($transaction->getAvatarLink());
            $entry = [];
            $entry[] = $transaction->getId();
            $entry[] = $transaction->getTransactionUid();
            $entry[] = $avatar->getAvatarName();
            $entry[] = $packagename;
            $entry[] = $regionname;
            $entry[] = $transaction->getAmount();
            $entry[] = date('l jS \of F Y h:i:s A', $transaction->getUnixtime());
            $type = "New";
            if ($transaction->getRenew() == 1) {
                $type = "Renew";
            }
            $entry[] = $type;
            if ($this->session->getOwnerLevel() == 1) {
                $entry[] = "<a href=\"[[url_base]]transactions/remove/" . $transaction->getTransactionUid() . "\">"
                . "<button type=\"button\" class=\"btn btn-danger btn-sm\"><i class=\"fas fa-minus-circle\"></i>"
                . "</button></a>";
            }
            $table_body[] = $entry;
        }
        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body));
    }
}
