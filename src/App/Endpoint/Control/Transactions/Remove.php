<?php

namespace App\Endpoint\Control\Transactions;

use App\R7\Model\Transactions;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Remove extends ViewAjax
{
    public function process(): void
    {
        if ($this->session->getOwnerLevel() != 1) {
            $this->failed("Do not dont have permission todo this");
            return;
        }
        $input = new InputFilter();
        $accept = $input->postString("accept");
        $this->setSwapTag("redirect", "transactions");
        if ($accept != "Accept") {
            $this->failed("Did not Accept");
            return;
        }
        $transaction = new Transactions();
        if ($transaction->loadByTransactionUid($this->page) == false) {
            $this->failed("Unable to find transaction");
            return;
        }
        $remove_status = $transaction->removeEntry();
        if ($remove_status["status"] == false) {
            $this->failed(
                sprintf("Unable to remove transaction: %1\$s", $remove_status["message"])
            );
            return;
        }
        $this->ok("Transaction removed");
    }
}
