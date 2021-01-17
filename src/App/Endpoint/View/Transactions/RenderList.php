<?php

namespace App\Endpoints\View\Transactions;

use App\Models\AvatarSet;
use App\Models\PackageSet;
use App\Models\RegionSet;
use App\Models\TransactionsSet;

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
            if ($transaction->getPackagelink() != null) {
                $package = $this->package_set->getObjectByID($transaction->getPackagelink());
                $packagename = $package->getName();
            }
            $regionname = "";
            if ($transaction->getRegionlink() != null) {
                $region = $this->region_set->getObjectByID($transaction->getRegionlink());
                $regionname = $region->getName();
            }
            $avatar = $this->avatar_set->getObjectByID($transaction->getAvatarlink());
            $entry = [];
            $entry[] = $transaction->getId();
            $entry[] = $transaction->getTransaction_uid();
            $entry[] = $avatar->getAvatarname();
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
                $entry[] = "<a href=\"[[url_base]]transactions/remove/" . $transaction->getTransaction_uid() . "\">"
                . "<button type=\"button\" class=\"btn btn-danger btn-sm\"><i class=\"fas fa-minus-circle\"></i>"
                . "</button></a>";
            }
            $table_body[] = $entry;
        }
        $this->setSwapTag("page_content", render_datatable($table_head, $table_body));
    }
}
