<?php

namespace App\Endpoint\View\Transactions;

class DefaultView extends RangeForm
{
    public function process(): void
    {
        $this->transaction_set->loadNewest(30);
        $this->package_set->loadIds($this->transaction_set->getAllByField("packageLink"));
        $this->region_set->loadIds($this->transaction_set->getAllByField("regionLink"));
        $this->avatar_set->loadIds($this->transaction_set->getAllByField("avatarLink"));
        $this->output->addSwapTagString("page_title", " Newest 30");
        parent::process();
    }
}
