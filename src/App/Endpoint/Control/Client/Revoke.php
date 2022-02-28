<?php

namespace App\Endpoint\Control\Client;

use App\Helpers\EventsQHelper;
use App\MediaServer\Logic\ApiLogicRevoke;
use App\Models\Sets\ApirequestsSet;
use App\Models\Avatar;
use App\Models\Package;
use App\Models\Rental;
use App\Models\Server;
use App\Models\Stream;
use App\Models\Sets\RentalnoticeptoutSet;
use App\Framework\ViewAjax;

class Revoke extends ViewAjax
{
    protected ?Rental $rental;
    protected ?Stream $stream;
    protected ?Server $server;
    protected ?Package $package;
    protected ?Avatar $avatar;

    public function process(): void
    {
        $this->rental = new Rental();

        $accept = $this->post("accept")->asString();
        $this->setSwapTag("redirect", null);
        if ($accept != "Accept") {
            $this->failed("Did not Accept");
            return;
        }
        if ($this->load() == false) {
            return;
        }
        if ($this->revoke() == false) {
            return;
        }
        $this->ok("Client rental revoked");
    }

    protected function load(): bool
    {
        if ($this->rental->loadByRentalUid($this->siteConfig->getPage()) == false) {
            $this->failed("Unable to find client");
            return false;
        }
        $this->stream = $this->rental->relatedStream()->getFirst();
        if ($this->stream == null) {
            $this->failed("Unable to find stream");
            return false;
        }
        $this->server = $this->stream->relatedServer()->getFirst();
        $this->package = $this->stream->relatedPackage()->getFirst();
        $this->avatar = $this->rental->relatedAvatar()->getFirst();
        if (in_array(null, [$this->server,$this->package,$this->avatar]) == true) {
            $this->failed("One or more required objects are missing");
            return false;
        }
        return true;
    }

    protected function revoke(): bool
    {
        $EventsQHelper = new EventsQHelper();
        $EventsQHelper->addToEventQ(
            "RentalEnd",
            $this->package,
            $this->avatar,
            $this->server,
            $this->stream,
            $this->rental
        );
        $this->stream->setRentalLink(null);
        $this->stream->setNeedWork(1);
        $update_status = $this->stream->updateEntry();
        if ($update_status["status"] == false) {
            $this->failed("Unable to mark stream as needs work");
            return false;
        }
        $rental_notice_opt_outs = new RentalnoticeptoutSet();
        $load = $rental_notice_opt_outs->loadByRentalLink($this->rental->getId());
        if ($load["status"] == false) {
            $this->failed("Unable to load rental notice opt-outs");
            return false;
        }
        if ($rental_notice_opt_outs->getCount() > 0) {
            $purge = $rental_notice_opt_outs->purgeCollection();
            if ($purge["status"] == false) {
                $this->failed(sprintf("Unable to remove client notice opt-outs: %1\$s", $purge["message"]));
                return false;
            }
        }

        $remove_status = $this->rental->removeEntry();
        if ($remove_status["status"] == false) {
            $this->failed(sprintf("Unable to remove client: %1\$s", $remove_status["message"]));
            return false;
        }
        return true;
    }
}
