<?php

namespace App\Endpoint\Control\Package;

use App\Models\Package;
use App\Models\Sets\RentalSet;
use App\Models\Sets\StreamSet;
use App\Models\Sets\TransactionsSet;
use App\Models\Sets\TreevenderpackagesSet;
use App\Template\ControlAjax;

class Remove extends ControlAjax
{
    public function process(): void
    {

        $package = new Package();
        $stream_set = new StreamSet();
        $rental_set = new RentalSet();
        $treevender_packages_set = new TreevenderpackagesSet();

        $accept = $this->input->post("accept");
        $this->setSwapTag("redirect", "package");
        if ($accept != "Accept") {
            $this->failed("Did not Accept");
            $this->setSwapTag("redirect", "package/manage/" . $this->siteConfig->getPage() . "");
            return;
        }
        if ($package->loadByPackageUid($this->siteConfig->getPage())->status == false) {
            $this->failed("Unable to find package");
            return;
        }
        $stream_set = $package->relatedStream();
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
        if ($load_status->status == false) {
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
        if ($load_status->status == false) {
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
        if ($remove_status->status == false) {
            $this->failed(
                sprintf(
                    "Unable to remove package: %1\$s",
                    $remove_status->message
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
        if ($load_status->status == false) {
            $this->failed("Unable to check if package is being used by any transactions");
            return false;
        }
        if ($transaction_set->getCount() == 0) {
            return true;
        }
        $reply = $transaction_set->updateFieldInCollection("packageLink", null);
        if ($reply->status == false) {
            $this->failed("Unable to unattach transactions from package because: " . $reply->message);
            return false;
        }
        return true;
    }
}
