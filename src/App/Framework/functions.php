<?php

use App\R7\Model\Apirequests;
use App\R7\Model\Detail;
use App\R7\Model\Rental;
use App\R7\Model\Server;
use App\R7\Model\Stream;

function createPendingApiRequest(
    ?Server $server,
    Stream $stream,
    ?Rental $rental,
    string $eventname,
    string $errormessage = "error: %1\$s %2\$s",
    bool $save_to_why_failed = false
): bool {
    global $why_failed, $no_api_action;
    if ($server == null) {
        if ($stream != null) {
            $server = new Server();
            $server->loadID($stream->getServerLink());
        }
    }
    if ($server->isLoaded() == false) {
        $why_failed = "Server is missing and unable to be loaded";
        if ($save_to_why_failed == false) {
            echo sprintf($errormessage, $eventname, $why_failed);
        }
        return false;
    }
    if ($eventname == "core_send_details") {
        $detail = new Detail();
        $detail->setRentalLink($rental->getId());
        $create_status = $detail->createEntry();
        $status = $create_status["status"];
        if ($status == false) {
            $why_failed = "Failed creating detail:" . sprintf($errormessage, $eventname, $create_status["message"]);
        }
        return $status;
    } else {
        $no_api_action = false;
        $api_request = new Apirequests();
        $api_request->setServerLink($server->getId());
        if ($rental != null) {
            $api_request->setRentalLink($rental->getId());
        }
        $api_request->setStreamLink($stream->getId());
        $api_request->setEventname($eventname);
        $api_request->setMessage("in Q");
        $api_request->setLastAttempt(time());
        $reply = $api_request->createEntry();
        if ($reply["status"] == false) {
            if ($save_to_why_failed == true) {
                $why_failed = sprintf($errormessage, $eventname, $reply["message"]);
            } else {
                echo sprintf($errormessage, $eventname, $reply["message"]);
            }
            return false;
        }
        $why_failed = "passed";
        return true;
    }
}

function expiredAgo($unixtime = 0, bool $use_secs = false): string
{
    $dif = time() - $unixtime;
    if ($dif < 0) {
        return "Active";
    }
    return timeleftHoursAndDays(time() + $dif, $use_secs);
}
function timeleftHoursAndDays($unixtime = 0, bool $use_secs = false): string
{
    $dif = $unixtime - time();
    if ($dif <= 0) {
        return "Expired";
    }
    $mins = floor(($dif / 60));
    $hours = floor(($mins / 60));
    $days = floor($hours / 24);
    if ($days > 0) {
        $hours -= $days * 24;
        return $days . " days, " . $hours . " hours";
    }
    if (($use_secs == false) && ($hours > 0)) {
        $mins -= $hours * 60;
        return $hours . " hours, " . $mins . " mins";
    }
    if ($use_secs == false) {
        return $mins . " mins";
    }
    $dif -= $mins * 60;
    if ($mins > 0) {
        return $mins . " mins, " . $dif . " secs";
    }
    return $dif . " secs";
}
function is_checked(bool $input_value): string
{
    if ($input_value == true) {
        return " checked ";
    }
    return "";
}
