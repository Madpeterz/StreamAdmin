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
        $rental_set->loadWithConfig($whereconfig);
        $avatar_set = $rental_set->relatedAvatar();
        $package_set = $rental_set->relatedPackage();
        $stream_set = $rental_set->relatedStream();
        $rental_notice_opt_outs = $rental_set->relatedRentalnoticeptout();
        $server_set = $stream_set->relatedServer();
        $removed_counter = 0;
        $skipped_counter = 0;
        $this->setSwapTag("redirect", "client/bulkremove");
        $EventsQHelper = new EventsQHelper();
        foreach ($rental_set as $rental) {
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
            $removed_counter++;
        }
        $this->ok(sprintf("Removed %1\$s rentals!", $removed_counter));
        if ($skipped_counter > 0) {
            $this->ok(sprintf("Removed %1\$s rentals! and skipped %2\$s", $removed_counter, $skipped_counter));
        }
        $this->setSwapTag("redirect", "client");
    }
}
