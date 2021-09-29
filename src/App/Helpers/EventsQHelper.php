<?php

namespace App\Helpers;

use App\R7\Model\Avatar;
use App\R7\Model\Eventsq;
use App\R7\Model\Package;
use App\R7\Model\Rental;
use App\R7\Model\Server;
use App\R7\Model\Slconfig;
use App\R7\Model\Stream;

class EventsQHelper
{
    protected ?Slconfig $config;
    public function __construct()
    {
        global $slconfig;
        $this->config = $slconfig;
    }
    public function addToEventQ(
        string $name,
        ?Package $package = null,
        ?Avatar $avatar = null,
        ?Server $server = null,
        ?Stream $stream = null,
        ?Rental $rental = null,
        ?int $amountpaid = null,
        ?Avatar $transactionAv = null
    ): void {
        if ($this->config->getEventsAPI() == false) {
            return;
        }
        $eventq = new Eventsq();
        $eventq->setEventMessage($this->makeJsonString(
            $package,
            $avatar,
            $server,
            $stream,
            $rental,
            $amountpaid,
            $transactionAv
        ));
        $eventq->setEventName($name);
        $eventq->setEventUnixtime(time());
        $eventq->createEntry();
    }
    protected function makeJsonString(
        ?Package $package = null,
        ?Avatar $avatar = null,
        ?Server $server = null,
        ?Stream $stream = null,
        ?Rental $rental = null,
        ?int $amountpaid = null,
        ?Avatar $transactionAv = null
    ): string {
        $reply = [];
        if ($package != null) {
            $reply["package"] = $package->getName();
        }
        if ($avatar != null) {
            $reply["uuid"] = $avatar->getAvatarUUID();
            $reply["name"] = $avatar->getAvatarName();
        }
        if ($server != null) {
            $reply["server"] = $server->getDomain();
        }
        if ($stream != null) {
            $reply["port"] = $stream->getPort();
        }
        if ($rental != null) {
            $reply["uid"] = $rental->getRentalUid();
        }
        if ($amountpaid != null) {
            $reply["amount"] = $amountpaid;
        }
        if (($transactionAv != null) && ($avatar != null)) {
            if ($transactionAv->getId() != $avatar->getId()) {
                $reply["viaProxy"] = true;
                $reply["uuidProxy"] = $transactionAv->getAvatarUUID();
                $reply["nameProxy"] = $transactionAv->getAvatarName();
            }
        }
        $reply["unixtime"] = time();
        return json_encode($reply);
    }
}
