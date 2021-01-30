<?php

namespace App\Endpoint\Control\Reseller;

use App\R7\Model\Reseller;
use App\R7\Set\TransactionsSet;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Remove extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $reseller = new Reseller();
        $transactionsSet = new TransactionsSet();
        $accept = $input->postFilter("accept");
        $this->setSwapTag("redirect", "reseller");
        if ($accept != "Accept") {
            $this->setSwapTag("message", "Did not Accept");
            $this->setSwapTag("redirect", "reseller/manage/" . $this->page . "");
            return;
        }
        if ($reseller->loadID($this->page) == false) {
            $this->setSwapTag("message", "Unable to find Reseller");
            return;
        }
        if ($transactionsSet->loadByField("resellerLink", $reseller->getId()) == false) {
            $this->setSwapTag("message", "Unable to check if reseller has any transactions to transfer");
            return;
        }
        if ($transactionsSet->getCount() > 0) {
            if ($this->transferTransactions($transactionsSet) == false) {
                $this->setSwapTag("message", "Unable to transfer transactions for reseller to system!");
                return;
            }
        }
        $remove_status = $reseller->removeEntry();
        if ($remove_status["status"] == false) {
            $this->setSwapTag(
                "message",
                sprintf("Unable to remove Reseller: %1\$s", $remove_status["message"])
            );
            return;
        }
        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "Reseller removed");
    }

    protected function transferTransactions(TransactionsSet $transactionsSet): bool
    {
        $reseller = new Reseller();
        if ($reseller->loadByField("avatarLink", $this->slconfig->getOwnerAvatarLink()) == false) {
            return false;
        }
        if ($reseller->getId() <= 0) {
            return false;
        }
        return $transactionsSet->updateFieldInCollection("resellerLink", $reseller->getId())["status"];
    }
}
