<?php

namespace App\Endpoint\Control\Client;

use App\MediaServer\Logic\ApiLogicRevoke;
use App\R7\Set\ApirequestsSet;
use App\R7\Set\ApisSet;
use App\R7\Set\AvatarSet;
use App\R7\Set\PackageSet;
use App\R7\Set\RentalSet;
use App\R7\Model\Server;
use App\R7\Set\ServerSet;
use App\R7\Set\StreamSet;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Bulkremove extends ViewAjax
{
    public function process(): void
    {
        $whereconfig = [
        "fields" => ["expireUnixtime"],
        "values" => [time()],
        "types" => ["i"],
        "matches" => ["<="],
        ];
        $input = new InputFilter();
        $template_parts["page_actions"] = "";
        $rental_set = new RentalSet();
        $stream_set = new StreamSet();
        $package_set = new PackageSet();
        $avatar_set = new AvatarSet();
        $server_set = new ServerSet();
        $api_requests_set = new ApirequestsSet();
        $apis_set = new ApisSet();
        $rental_set->loadWithConfig($whereconfig);
        $avatar_set->loadIds($rental_set->getAllByField("avatarLink"));
        $package_set->loadIds($rental_set->getAllByField("packageLink"));
        $stream_set->loadIds($rental_set->getAllByField("streamLink"));
        $api_requests_set->loadIds($rental_set->getAllByField("id"), "rentalLink");
        $apis_set->loadAll();
        $server_set->loadAll();
        $removed_counter = 0;
        $skipped_counter = 0;
        $this->setSwapTag("redirect", "client/bulkremove");
        foreach ($rental_set->getAllIds() as $rental_id) {
            $rental = $rental_set->getObjectByID($rental_id);
            if ($rental->getMessage() != null) {
                if (strlen($rental->getMessage()) > 0) {
                    $skipped_counter++;
                    continue;
                }
            }
            if ($api_requests_set->getObjectByField("rentalLink", $rental_id) != null) {
                $skipped_counter++;
                continue;
            }
            $accept = $input->postFilter("rental" . $rental->getRentalUid());
            if ($accept != "purge") {
                $skipped_counter++;
                continue;
            }
            $stream = $stream_set->getObjectByID($rental->getStreamLink());
            if ($stream == null) {
                $this->setSwapTag(
                    "message",
                    sprintf("Unable to find stream attached to rental %1\$s", $rental->getRentalUid())
                );
                return;
            }
            $server = new Server();
            if ($server->loadID($stream->getServerLink()) == false) {
                $this->setSwapTag(
                    "message",
                    sprintf("Unable to find server attached to stream for rental %1\$s", $rental->getRentalUid())
                );
                return;
            }
            $stream->setRentalLink(null);
            $stream->setNeedWork(1);
            $update_status = $stream->updateEntry();
            if ($update_status["status"] == false) {
                $this->setSwapTag(
                    "message",
                    sprintf("Error releasing stream from rental %1\$s", $rental->getRentalUid())
                );
                return;
            }
            $all_ok = true;
            $remove_status = $rental->removeEntry();
            $all_ok = $remove_status["status"];
            if ($all_ok == false) {
                $this->setSwapTag(
                    "message",
                    sprintf("Error removing old rental %1\$s", $remove_status["message"])
                );
                return;
            }
            // Server API support
            $apilogic = new ApiLogicRevoke();
            $apilogic->setStream($stream);
            $apilogic->setServer($server);
            $reply = $apilogic->createNextApiRequest();
            if ($reply["status"] == false) {
                $this->setSwapTag("status", false);
                $this->setSwapTag("message", "Bad reply: " . $reply["message"]);
                return;
            }
            $removed_counter++;
        }
        $this->setSwapTag("status", true);
        $this->setSwapTag(
            "message",
            sprintf("Removed %1\$s rentals! and skipped %2\$s", $removed_counter, $skipped_counter)
        );
        $this->setSwapTag("redirect", "client");
    }
}
