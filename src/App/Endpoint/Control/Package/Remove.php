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
        $rental_set = new RentalSet();
        $treevender_packages_set = new TreevenderpackagesSet();

        $accept = $input->postString("accept");
        $this->setSwapTag("redirect", "package");
        if ($accept != "Accept") {
            $this->failed("Did not Accept");
            $this->setSwapTag("redirect", "package/manage/" . $this->page . "");
            return;
        }
        if ($package->loadByPackageUid($this->page) == false) {
            $this->failed("Unable to find package");
            return;
        }
        $load_status = $stream_set->loadByPackageLink($package->getId());
        if ($load_status["status"] == false) {
            $this->failed("Unable to check if package is being used by any streams");
            return;
        }
        if ($stream_set->getCount() != 0) {
            $this->failed(
                sprintf(
                    "Unable to remove package it is currently being used by: %1\$s stream('s)",
                    $stream_set->getCount()
                )
            );
            return;
        }

        if ($this->unlinkTransactions($package) == false) {
            return;
        }

        $load_status = $rental_set->loadByPackageLink($package->getId());
        if ($load_status["status"] == false) {
            $this->failed("Unable to check if package is being used by any clients");
            return;
        }
        if ($rental_set->getCount() != 0) {
            $this->failed(
                sprintf(
                    "Unable to remove package it is currently being used by: %1\$s clients('s)",
                    $rental_set->getCount()
                )
            );
            return;
        }
        $load_status = $treevender_packages_set->loadByPackageLink($package->getId());
        if ($load_status["status"] == false) {
            $this->failed("Unable to check if package is being used by any treevenders");
            return;
        }
        if ($treevender_packages_set->getCount() != 0) {
            $this->failed(
                sprintf(
                    "Unable to remove package it is currently being used by: %1\$s treevender('s)",
                    $treevender_packages_set->getCount()
                )
            );
            return;
        }
        $remove_status = $package->removeEntry();
        if ($remove_status["status"] == false) {
            $this->failed(
                sprintf(
                    "Unable to remove package: %1\$s",
                    $remove_status["message"]
                )
            );
            return;
        }

        $this->ok("Package removed");
    }

    protected function unlinkTransactions(Package $package): bool
    {
        $transaction_set = new TransactionsSet();
        $load_status = $transaction_set->loadByPackageLink($package->getId());
        if ($load_status["status"] == false) {
            $this->failed("Unable to check if package is being used by any transactions");
            return false;
        }
        if ($transaction_set->getCount() == 0) {
            return true;
        }
        $reply = $transaction_set->updateFieldInCollection("packageLink", null);
        if ($reply["status"] == false) {
            $this->failed("Unable to unattach transactions from package because: " . $reply["message"]);
            return false;
        }
        return true;
    }
}
