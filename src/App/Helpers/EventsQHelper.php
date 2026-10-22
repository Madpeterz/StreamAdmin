<?php

namespace App\Helpers;

use App\Config;
use App\Models\Avatar;
use App\Models\Eventsq;
use App\Models\Package;
use App\Models\Rental;
use App\Models\Server;
use App\Models\Slconfig;
use App\Models\Stream;

class EventsQHelper
{
    protected ?Config $config;
    public function __construct()
    {
        global $system;
        $this->config = $system;
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
        if ($this->config->getSlConfig()->_EventsAPI == false) {
            return;
        }
        $eventq = new Eventsq();
        $eventq->_EventMessage = $this->makeJsonString(
            $package,
            $avatar,
            $server,
            $stream,
            $rental,
            $amountpaid,
            $transactionAv
        );
        $eventq->_EventName = $name;
        $eventq->_EventUnixtime = time();
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
            $reply["package"] = $package->_Name;
        }
        if ($avatar != null) {
            $reply["uuid"] = $avatar->_AvatarUUID;
            $reply["name"] = $avatar->_AvatarName;
        }
        if ($server != null) {
            $reply["server"] = $server->_Domain;
        }
        if ($stream != null) {
            $reply["port"] = $stream->_Port;
        }
        if ($rental != null) {
            $reply["uid"] = $rental->_RentalUid;
        }
        if ($amountpaid != null) {
            $reply["amount"] = $amountpaid;
        }
        if (($transactionAv != null) && ($avatar != null)) {
            if ($transactionAv->_Id != $avatar->_Id) {
                $reply["viaProxy"] = true;
                $reply["uuidProxy"] = $transactionAv->_AvatarUUID;
                $reply["nameProxy"] = $transactionAv->_AvatarName;
            }
        }
        $reply["unixtime"] = time();
        return json_encode($reply);
    }
}
