<?php

namespace App\View\Transactions;

class DefaultView extends RangeForm
{
    public function process(): void
    {
        $this->transaction_set->loadNewest(30);
        $this->package_set->loadIds($this->transaction_set->getAllByField("packagelink"));
        $this->region_set->loadIds($this->transaction_set->getAllByField("regionlink"));
        $this->avatar_set->loadIds($this->transaction_set->getAllByField("avatarlink"));
        $this->output->addSwapTagString("page_title", " Newest 30");
        parent::process();
    }
}
