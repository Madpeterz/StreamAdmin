<?php

namespace App\Endpoint\Control\Client;

use App\Helpers\SwapablesHelper;
use App\Models\Rental;
use App\Template\ControlAjax;

class GetNotecard extends ControlAjax
{
    public function process(): void
    {
        $rental = new Rental();
        if ($rental->loadByRentalUid($this->siteConfig->getPage())->status == false) {
            $this->failed("Unable to load rental");
            return;
        }
        $package = $rental->relatedPackage()->getFirst();
        $template = $package?->relatedTemplate()->getFirst();
        $avatar = $rental->relatedAvatar()->getFirst();
        $stream = $rental->relatedStream()->getFirst();
        $server = $stream?->relatedServer()->getFirst();
        $test = [$package, $template, $avatar, $stream, $server];
        if (in_array(null, $test) == false) {
            $this->failed("One or more required objects did not load");
            return;
        }
        $this->ok((new SwapablesHelper())->getSwappedText(
            $template->getDetail(),
            $avatar,
            $rental,
            $package,
            $server,
            $stream
        ));
    }
}
