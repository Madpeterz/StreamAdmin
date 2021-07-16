<?php

namespace App\Endpoint\Control\Client;

use App\Helpers\SwapablesHelper;
use App\R7\Model\Avatar;
use App\R7\Model\Package;
use App\R7\Model\Rental;
use App\R7\Model\Server;
use App\R7\Model\Stream;
use App\Template\ViewAjax;

class Getnotecard extends ViewAjax
{
    public function process(): void
    {
        $rental = new Rental();
        if ($rental->loadByField("rentalUid", $this->page) == false) {
            $this->setSwapTag("message", "Unable to load rental");
        }
        $this->setSwapTag("status", true);
        $avatar = new Avatar();
        $avatar->loadID($rental->getAvatarLink());

        $stream = new Stream();
        $stream->loadID($rental->getStreamLink());

        $package = new Package();
        $package->loadID($stream->getPackageLink());

        $server = new Server();
        $server->loadID($stream->getServerLink());

        $viewnotecard = ""
        . "Assigned to: [[AVATAR_FULLNAME]][[NL]]"
        . "===========================[[NL]][[NL]]"
        . "Package: [[PACKAGE_NAME]][[NL]]"
        . "Listeners: [[PACKAGE_LISTENERS]][[NL]]"
        . "Bitrate: [[PACKAGE_BITRATE]]kbps[[NL]]"
        . "===========================[[NL]][[NL]]"
        . "Control panel: [[SERVER_CONTROLPANEL]][[NL]]"
        . "ip: [[SERVER_DOMAIN]][[NL]]"
        . "port: [[STREAM_PORT]][[NL]]"
        . "===========================[[NL]][[NL]]"
        . "Admin user: [[STREAM_ADMINUSERNAME]][[NL]]"
        . "Admin pass: [[STREAM_ADMINPASSWORD]][[NL]]"
        . "Encoder/Stream password: [[STREAM_DJPASSWORD]][[NL]]"
        . "===========================[[NL]][[NL]]"
        . "Expires: [[RENTAL_EXPIRES_DATETIME]]";
        $swapables_helper = new SwapablesHelper();
        $this->setSwapTag(
            "message",
            $swapables_helper->getSwappedText($viewnotecard, $avatar, $rental, $package, $server, $stream)
        );
    }
}
