<?php

namespace App\Endpoint\Control\Client;

use App\Helpers\SwapablesHelper;
use App\Models\Rental;
use App\Framework\ViewAjax;

class Getnotecard extends ViewAjax
{
    public function process(): void
    {
        $rental = new Rental();
        if ($rental->loadByRentalUid($this->siteConfig->getPage()) == false) {
            $this->failed("Unable to load rental");
            return;
        }
        $package = $rental->relatedPackage();
        $template = $package->relatedTemplate();
        $swapables_helper = new SwapablesHelper();
        $this->ok($swapables_helper->getSwappedText(
            $template->getDetail(),
            $rental->relatedAvatar(),
            $rental,
            $package,
            $stream->relatedServer(),
            $rental->relatedStream()
        ));
    }
}
