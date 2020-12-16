<?php

namespace App\Control\Client;

use App\Models\ApirequestsSet;
use App\Models\ApisSet;
use App\Models\AvatarSet;
use App\Models\PackageSet;
use App\Models\RentalSet;
use App\Models\Server;
use App\Models\ServerSet;
use App\Models\StreamSet;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Bulkremove extends ViewAjax
{
    public function process(): void
    {
        $whereconfig = [
        "fields" => ["expireunixtime"],
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
        $avatar_set->loadIds($rental_set->getAllByField("avatarlink"));
        $package_set->loadIds($rental_set->getAllByField("packagelink"));
        $stream_set->loadIds($rental_set->getAllByField("streamlink"));
        $api_requests_set->loadIds($rental_set->getAllByField("id"), "rentallink");
        $apis_set->loadAll();
        $server_set->loadAll();
        $removed_counter = 0;
        $skipped_counter = 0;
        $status = true;
        $this->output->setSwapTagString("redirect", "client/bulkremove");
        $status = true;
        foreach ($rental_set->getAllIds() as $rental_id) {
            $rental = $rental_set->getObjectByID($rental_id);
            if (strlen($rental->getMessage()) != 0) {
                $skipped_counter++;
                continue;
            }
            if ($api_requests_set->getObjectByField("rentallink", $rental_id) != null) {
                $skipped_counter++;
                continue;
            }
            $accept = $input->postFilter("rental" . $rental->getRental_uid() . "");
            if ($accept != "purge") {
                $skipped_counter++;
                continue;
            }
            $stream = $stream_set->getObjectByID($rental->getStreamlink());
            if ($stream == null) {
                $status = false;
                $this->output->setSwapTagString(
                    "message",
                    sprintf("Unable to find stream attached to rental %1\$s", $rental->getRental_uid())
                );
                break;
            }
            $server = new Server();
            if ($server->loadID($stream->getServerlink()) == false) {
                $status = false;
                $this->output->setSwapTagString(
                    "message",
                    sprintf("Unable to find server attached to stream for rental %1\$s", $rental->getRental_uid())
                );
                break;
            }
            $stream->setRentallink(null);
            $stream->setNeedwork(1);
            $update_status = $stream->updateEntry();
            if ($update_status["status"] == false) {
                $status = false;
                $this->output->setSwapTagString(
                    "message",
                    sprintf("Error releasing stream from rental %1\$s", $rental->getRental_uid())
                );
                break;
            }
            $all_ok = true;
            $message = "";
            $remove_status = $rental->removeEntry();
            $all_ok = $remove_status["status"];
            if ($all_ok == false) {
                $message = sprintf("Error removing old rental %1\$s", $remove_status["message"]);
                break;
            }
            // Server API support
            $rental = null;
            include "shared/media_server_apis/logic/revoke.php";
            $status = $api_serverlogic_reply;
            if ($status != true) {
                $this->output->setSwapTagString("message", $why_failed);
            }
            $removed_counter++;
        }
        if ($status == true) {
            $status = true;
            $this->output->setSwapTagString("status", "true");
            $this->output->setSwapTagString(
                "message",
                sprintf("Removed %1\$s rentals! and skipped %2\$s", $removed_counter, $skipped_counter)
            );
            $this->output->setSwapTagString("redirect", "client");
        }
    }
}
