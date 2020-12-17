<?php

namespace App\Endpoints\View\Stream;

use App\Models\RentalSet;
use App\Models\StreamSet;

abstract class Withstatus extends RenderList
{
    protected array $whereconfig = [];
    public function process(): void
    {
        $this->streamSet = new StreamSet();
        $rental_set = new RentalSet();
        if (count($this->whereconfig) > 0) {
            $this->output->setSwapTagString("page_actions", "");
            $this->streamSet->loadWithConfig($this->whereconfig);
            $rental_set->loadIds($this->streamSet->getAllByField("rentallink"));
            $this->rental_set_ids = $rental_set->getAllIds();
        }
        parent::process();
    }
}
