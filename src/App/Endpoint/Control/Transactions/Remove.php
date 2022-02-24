<?php

namespace App\Endpoint\Control\Transactions;

use App\Models\Transactions;
use App\Framework\ViewAjax;

class Remove extends ViewAjax
{
    public function process(): void
    {
        if ($this->siteConfig->getSession()->getOwnerLevel() != 1) {
            $this->failed("Do not dont have permission todo this");
            return;
        }

        $accept = $this->input->post("accept");
        $this->setSwapTag("redirect", "transactions");
        if ($accept != "Accept") {
            $this->failed("Did not Accept");
            return;
        }
        $transaction = new Transactions();
        if ($transaction->loadByTransactionUid($this->siteConfig->getPage()) == false) {
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
