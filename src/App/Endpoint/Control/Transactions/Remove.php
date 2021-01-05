<?php

namespace App\Endpoints\Control\Transactions;

use App\Models\Transactions;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Remove extends ViewAjax
{
    public function process(): void
    {
        if ($this->session->getOwnerLevel() != 1) {
            $this->output->setSwapTagString("message", "Do not dont have permission todo this");
            return;
        }
        $input = new InputFilter();
        $accept = $input->postFilter("accept");
        $this->output->setSwapTagString("redirect", "transactions");
        if ($accept != "Accept") {
            $this->output->setSwapTagString("message", "Did not Accept");
            return;
        }
        $transaction = new Transactions();
        if ($transaction->loadByField("transaction_uid", $this->page) == false) {
            $this->output->setSwapTagString("message", "Unable to find transaction");
            return;
        }
        $remove_status = $transaction->removeEntry();
        if ($remove_status["status"] == false) {
            $this->output->setSwapTagString(
                "message",
                sprintf("Unable to remove transaction: %1\$s", $remove_status["message"])
            );
            return;
        }
        $this->output->setSwapTagString("status", "true");
        $this->output->setSwapTagString("message", "Transaction removed");
    }
}
