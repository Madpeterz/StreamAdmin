<?php

namespace App\Endpoint\Control\Package;

use App\R7\Model\Package;
use App\R7\Set\RentalSet;
use App\R7\Set\StreamSet;
use App\R7\Set\TransactionsSet;
use App\R7\Set\TreevenderpackagesSet;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Remove extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $package = new Package();
        $stream_set = new StreamSet();
        $transaction_set = new TransactionsSet();
        $rental_set = new RentalSet();
        $treevender_packages_set = new TreevenderpackagesSet();

        $accept = $input->postFilter("accept");
        $this->setSwapTag("redirect", "package");
        if ($accept != "Accept") {
            $this->setSwapTag("message", "Did not Accept");
            $this->setSwapTag("redirect", "package/manage/" . $this->page . "");
            return;
        }
        if ($package->loadByField("packageUid", $this->page) == false) {
            $this->setSwapTag("message", "Unable to find package");
            return;
        }
        $load_status = $stream_set->loadOnField("packageLink", $package->getId());
        if ($load_status["status"] == false) {
            $this->setSwapTag("message", "Unable to check if package is being used by any streams");
            return;
        }
        if ($stream_set->getCount() != 0) {
            $this->setSwapTag(
                "message",
                sprintf(
                    "Unable to remove package it is currently being used by: %1\$s stream('s)",
                    $stream_set->getCount()
                )
            );
            return;
        }
        $load_status = $transaction_set->loadOnField("packageLink", $package->getId());
        if ($load_status["status"] == false) {
            $this->setSwapTag("message", "Unable to check if package is being used by any transactions");
            return;
        }
        if ($transaction_set->getCount() != 0) {
            $this->setSwapTag(
                "message",
                sprintf(
                    "Unable to remove package it is currently being used by: %1\$s transaction('s)",
                    $transaction_set->getCount()
                )
            );
            return;
        }
        $load_status = $rental_set->loadOnField("packageLink", $package->getId());
        if ($load_status["status"] == false) {
            $this->setSwapTag("message", "Unable to check if package is being used by any clients");
            return;
        }
        if ($rental_set->getCount() != 0) {
            $this->setSwapTag(
                "message",
                sprintf(
                    "Unable to remove package it is currently being used by: %1\$s clients('s)",
                    $rental_set->getCount()
                )
            );
            return;
        }
        $load_status = $treevender_packages_set->loadByField("packageLink", $package->getId());
        if ($load_status["status"] == false) {
            $this->setSwapTag("message", "Unable to check if package is being used by any treevenders");
            return;
        }
        if ($treevender_packages_set->getCount() != 0) {
            $this->setSwapTag(
                "message",
                sprintf(
                    "Unable to remove package it is currently being used by: %1\$s treevender('s)",
                    $treevender_packages_set->getCount()
                )
            );
            return;
        }
        $remove_status = $package->removeEntry();
        if ($remove_status["status"] == false) {
            $this->setSwapTag(
                "message",
                sprintf(
                    "Unable to remove package: %1\$s",
                    $remove_status["message"]
                )
            );
            return;
        }
        $this->setSwapTag("status", true);
        $this->setSwapTag("message", "Package removed");
    }
}
