<?php

namespace App\Endpoint\View\Transactions;

class DefaultView extends Forms
{
    public function process(): void
    {
        $this->transaction_set->loadNewest(30);
        $this->output->addSwapTagString("page_title", " Newest 30");
        parent::process();
    }
}
