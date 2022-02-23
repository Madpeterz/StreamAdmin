<?php

namespace App\Endpoint\Control\Client;

use App\Helpers\SwapablesHelper;
use App\Models\Avatar;
use App\Models\Package;
use App\Models\Rental;
use App\Models\Server;
use App\Models\Stream;
use App\Models\Template;
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
        $avatar = new Avatar();
        if ($avatar->loadID($rental->getAvatarLink()) == false) {
            $this->failed("Unable to load avatar");
            return;
        }

        $stream = new Stream();
        if ($stream->loadID($rental->getStreamLink()) == false) {
            $this->failed("Unable to load stream");
            return;
        }

        $package = new Package();
        if ($package->loadID($stream->getPackageLink()) == false) {
            $this->failed("Unable to load package");
            return;
        }

        $server = new Server();
        if ($server->loadID($stream->getServerLink()) == false) {
            $this->failed("Unable to load server");
            return;
        }

        $template = new Template();
        if ($template->loadID($package->getTemplateLink()) == false) {
            $this->failed("Unable to load server");
            return;
        }

        $viewnotecard = $template->getDetail();
        $swapables_helper = new SwapablesHelper();
        $this->ok($swapables_helper->getSwappedText($viewnotecard, $avatar, $rental, $package, $server, $stream));
    }
}
