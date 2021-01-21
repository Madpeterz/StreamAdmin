<?php

namespace App\Endpoint\Control\Transactions;

use App\Models\Transactions;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Remove extends ViewAjax
{
    public function process(): void
    {
        if ($this->session->getOwnerLevel() != 1) {
            $this->setSwapTag("message", "Do not dont have permission todo this");
            return;
        }
        $input = new InputFilter();
        $accept = $input->postFilter("accept");
        $this->setSwapTag("redirect", "transactions");
        if ($accept != "Accept") {
            $this->setSwapTag("message", "Did not Accept");
            return;
        }
        $transaction = new Transactions();
        if ($transaction->loadByField("transactionUid", $this->page) == false) {
            $this->setSwapTag("message", "Unable to find transaction");
            return;
        }
        $remove_status = $transaction->removeEntry();
        if ($remove_status["status"] == false) {
            $this->setSwapTag(
                "message",
                sprintf("Unable to remove transaction: %1\$s", $remove_status["message"])
            );
            return;
        }
        $this->setSwapTag("status", "true");
        $this->setSwapTag("message", "Transaction removed");
    }
}
