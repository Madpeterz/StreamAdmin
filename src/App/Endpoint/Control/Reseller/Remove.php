<?php

namespace App\Endpoint\Control\Reseller;

use App\Models\Reseller;
use App\Models\Sets\TransactionsSet;
use App\Template\ControlAjax;

class Remove extends ControlAjax
{
    protected ?TransactionsSet $transactionsSet = null;
    public function process(): void
    {

        $reseller = new Reseller();
        $this->transactionsSet = new TransactionsSet();
        $accept = $this->input->post("accept")->asString();
        $this->setSwapTag("redirect", "reseller");
        if ($accept != "Accept") {
            $this->failed("Did not Accept");
            $this->setSwapTag("redirect", "reseller/manage/" . $this->siteConfig->getPage() . "");
            return;
        }
        if ($reseller->loadID($this->siteConfig->getPage())->status == false) {
            $this->failed("Unable to find Reseller");
            return;
        }
        if ($reseller->getId() == 1) {
            $this->failed("Unable to remove the first reseller");
            return;
        }
        $this->transactionsSet = $reseller->relatedTransactions();
        if ($this->transactionsSet->getCount() > 0) {
            if ($this->transferTransactions() == false) {
                $this->failed("Unable to transfer transactions for reseller to system!");
                return;
            }
        }
        $remove_status = $reseller->removeEntry();
        if ($remove_status->status == false) {
            $this->failed(
                sprintf("Unable to remove Reseller: %1\$s", $remove_status->message)
            );
            return;
        }
        $this->redirectWithMessage("Reseller removed");
    }

    protected function transferTransactions(): bool
    {
        $reseller = new Reseller();
        if ($reseller->loadByAvatarLink($this->siteConfig->getSlConfig()->getOwnerAvatarLink()) == false) {
            return false;
        }
        if ($reseller->isLoaded() == false) {
            return false;
        }
        return $this->transactionsSet->updateFieldInCollection("resellerLink", $reseller->getId())->status;
    }
}
