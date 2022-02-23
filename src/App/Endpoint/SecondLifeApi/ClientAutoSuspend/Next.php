<?php

namespace App\Endpoint\SecondLifeApi\ClientAutoSuspend;

use App\MediaServer\Logic\ApiLogicExpire;
use App\Models\Server;
use App\Models\Stream;
use App\Models\Sets\RentalSet;
use App\Template\SecondlifeAjax;

class Next extends SecondlifeAjax
{
    public function process(): void
    {
        if ($this->owner_override == false) {
            $this->setSwapTag("message", "SystemAPI access only - please contact support");
            return;
        }
        $rentalSet = new RentalSet();
        $whereConfig = [
            "fields" => ["apiPendingAutoSuspendAfter","apiPendingAutoSuspend"],
            "values" => [time(),true],
            "matches" => ["<=","="],
        ];
        $options = [
            "page_number" => 0,
            "max_entrys" => 2,
        ];
        $rentalSet->loadWithConfig($whereConfig, null, $options);
        if ($rentalSet->getCount() == 0) {
            $this->ok("nowork");
            return;
        }
        $rental = $rentalSet->getFirst();
        $stream = new Stream();
        $stream->loadID($rental->getStreamLink());
        $server = new Server();
        $server->loadID($stream->getServerLink());

        $rental->setApiPendingAutoSuspend(false);
        $rental->setApiPendingAutoSuspendAfter(null);
        $rental->setApiSuspended(true);
        $apilogic = new ApiLogicExpire();
        $apilogic->setStream($stream);
        $apilogic->setServer($server);
        $apilogic->setRental($rental);
        $reply = $apilogic->createNextApiRequest();
        if ($reply["status"] == false) {
            $this->setSwapTag(
                "message",
                "API server logic has failed on ApiLogicExpire: " . $reply["message"]
            );
            return;
        }
        $updateReply = $rental->updateEntry();
        if ($updateReply["status"] == false) {
            $this->failed("Unable to update rental");
            return;
        }
        $this->ok("ok");
        if ($rentalSet->getCount() > 1) {
            $this->ok("more");
            return;
        }
    }
}
