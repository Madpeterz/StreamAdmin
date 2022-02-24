<?php

namespace App\Endpoint\Control\Client;

use App\Helpers\EventsQHelper;
use App\MediaServer\Logic\ApiLogicRevoke;
use App\Models\Sets\ApirequestsSet;
use App\Models\Sets\ApisSet;
use App\Models\Sets\AvatarSet;
use App\Models\Sets\PackageSet;
use App\Models\Sets\RentalSet;
use App\Models\Server;
use App\Models\Sets\RentalnoticeptoutSet;
use App\Models\Sets\ServerSet;
use App\Models\Sets\StreamSet;
use App\Framework\ViewAjax;

class Bulkremove extends ViewAjax
{
    public function process(): void
    {
        $whereconfig = [
        "fields" => ["expireUnixtime","noticeLink"],
        "values" => [time(),6],
        "matches" => ["<=","="],
        ];

        $template_parts["page_actions"] = "";
        $rental_set = new RentalSet();
        $stream_set = new StreamSet();
        $package_set = new PackageSet();
        $avatar_set = new AvatarSet();
        $server_set = new ServerSet();
        $api_requests_set = new ApirequestsSet();
        $apis_set = new ApisSet();
        $rental_notice_opt_outs = new RentalnoticeptoutSet();
        $rental_set->loadWithConfig($whereconfig);
        $avatar_set->loadByValues($rental_set->getAllByField("avatarLink"));
        $package_set->loadByValues($rental_set->getAllByField("packageLink"));
        $stream_set->loadByValues($rental_set->getAllByField("streamLink"));
        $api_requests_set->loadByValues($rental_set->getAllIds(), "rentalLink");
        $rental_notice_opt_outs->loadByValues($rental_set->getAllIds(), "rentalLink");
        $apis_set->loadAll();
        $server_set->loadAll();
        $removed_counter = 0;
        $skipped_counter = 0;
        $this->setSwapTag("redirect", "client/bulkremove");
        $EventsQHelper = new EventsQHelper();
        foreach ($rental_set as $rental) {
            if ($api_requests_set->getObjectByField("rentalLink", $rental->getId()) != null) {
                $skipped_counter++;
                continue;
            }
            $accept = $this->input->post("rental" . $rental->getRentalUid());
            if ($accept != "purge") {
                $skipped_counter++;
                continue;
            }
            $client_rental_notice_opt_outs = new RentalnoticeptoutSet();
            // import opt outs from global load for this Client only
            foreach ($rental_notice_opt_outs as $notice_opt_out) {
                if ($notice_opt_out->getRentalLink() == $rental->getId()) {
                    $client_rental_notice_opt_outs->addToCollected($notice_opt_out);
                }
            }

            if ($client_rental_notice_opt_outs->getCount() > 0) {
                $purge = $client_rental_notice_opt_outs->purgeCollection();
                if ($purge["status"] == false) {
                    $this->failed(sprintf(
                        "Failed to purge some client notice opt outs because %1\$s",
                        $purge["message"]
                    ));
                    return;
                }
            }



            $stream = $stream_set->getObjectByID($rental->getStreamLink());
            if ($stream == null) {
                $this->failed(sprintf("Unable to find stream attached to rental %1\$s", $rental->getRentalUid()));
                return;
            }
            $avatar = $avatar_set->getObjectByID($rental->getAvatarLink());
            if ($avatar == null) {
                $this->failed(sprintf("Unable to find avatar attached to rental %1\$s", $rental->getRentalUid()));
                return;
            }
            $package = $package_set->getObjectByID($rental->getPackageLink());
            if ($package == null) {
                $this->failed(sprintf("Unable to find package attached to rental %1\$s", $rental->getRentalUid()));
                return;
            }
            $server = $server_set->getObjectByID($stream->getServerLink());
            if ($server == null) {
                $this->failed(sprintf("Unable to find server attached to stream %1\$s", $stream->getStreamUid()));
                return;
            }

            $EventsQHelper->addToEventQ(
                "RentalEnd",
                $package,
                $avatar,
                $server,
                $stream,
                $rental
            );


            $stream->setRentalLink(null);
            $stream->setNeedWork(1);
            $update_status = $stream->updateEntry();
            if ($update_status["status"] == false) {
                $this->failed(sprintf("Error releasing stream from rental %1\$s", $rental->getRentalUid()));
                return;
            }
            $all_ok = true;
            $remove_status = $rental->removeEntry();
            $all_ok = $remove_status["status"];
            if ($all_ok == false) {
                $this->failed(sprintf("Error removing old rental %1\$s", $remove_status["message"]));
                return;
            }
            // Server API support
            $apilogic = new ApiLogicRevoke();
            $apilogic->setStream($stream);
            $apilogic->setServer($server);
            $reply = $apilogic->createNextApiRequest();
            if ($reply["status"] == false) {
                $this->failed("Bad reply: " . $reply["message"]);
                return;
            }
            $removed_counter++;
        }
        $this->ok(sprintf("Removed %1\$s rentals!", $removed_counter));
        if ($skipped_counter > 0) {
            $this->ok(sprintf("Removed %1\$s rentals! and skipped %2\$s", $removed_counter, $skipped_counter));
        }
        $this->setSwapTag("redirect", "client");
    }
}
