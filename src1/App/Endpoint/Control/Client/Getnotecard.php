<?php

namespace App\Endpoint\Control\Client;

use App\Helpers\SwapablesHelper;
use App\Models\Rental;
use App\Template\ControlAjax;

class Getnotecard extends ControlAjax
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
        $test = [
            "package" => $package,
            "template" => $template,
            "avatar" => $avatar,
            "stream" => $stream,
            "server" => $server,
        ];
        if (in_array(null, $test) == true) {
            foreach ($test as $tag => $object) {
                if ($object == null) {
                    $this->failed("Failed to load: " . $tag);
                    break;
                }
            }
            return;
        }
        $this->setSwapTag("rentaluid", $rental->getRentalUid());
        $this->redirectWithMessage((new SwapablesHelper())->getSwappedText(
            $template->getDetail(),
            $avatar,
            $rental,
            $package,
            $server,
            $stream
        ));
    }
}
