<?php

namespace App\Endpoint\View\Stream;

use App\Models\Sets\RentalSet;
use App\Models\Sets\StreamSet;

abstract class Withstatus extends RenderList
{
    protected array $whereconfig = [];
    public function process(bool $usePackageNotServer = false): void
    {
        $this->streamSet = new StreamSet();
        $this->rentalSet = new RentalSet();
        if (count($this->whereconfig) > 0) {
            $this->setSwapTag("page_actions", "");
            $this->streamSet->loadWithConfig($this->whereconfig);
            $this->rentalSet = $this->streamSet->relatedRental();
            $this->rental_set_ids = $this->rentalSet->getAllIds();
        }
        if ($usePackageNotServer == true) {
            $this->packageSet = $this->streamSet->relatedPackage();
        }
        parent::process($usePackageNotServer);
    }
}
