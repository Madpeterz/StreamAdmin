<?php

namespace App\Control\Client;

use App\Models\Avatar;
use App\Models\AvatarSet;
use App\Models\NoticeSet;
use App\Models\Rental;
use App\Models\Stream;
use App\Models\StreamSet;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Create extends ViewAjax
{
    public function process(): void
    {
        global $unixtime_day;

        $avatar = new Avatar();
        $stream = new Stream();
        $notice_set = new NoticeSet();
        $avatar_set = new AvatarSet();
        $input = new InputFilter();
        $stream_set = new StreamSet();

        $avataruid = $input->postFilter("avataruid");
        $streamuid = $input->postFilter("streamuid");
        $daysremaining = $input->postFilter("daysremaining", "integer");
        $status = false;
        $this->output->setSwapTagString("redirect", "client");
        $failed_on = "";

        $avatar_where_config = [
            "fields" => ["avatar_uid","avatarname","avataruuid"],
            "matches" => ["=","=","="],
            "values" => [$avataruid,$avataruid,$avataruid],
            "types" => ["s","s","s"],
            "join_with" => ["(OR)","(OR)"],
        ];

        $avatar_set->loadWithConfig($avatar_where_config);

        $stream_where_config = [
            "fields" => ["port","stream_uid"],
            "matches" => ["=","="],
            "values" => [$streamuid,$streamuid],
            "types" => ["i","s"],
            "join_with" => ["(OR)"],
        ];

        $stream_set->loadWithConfig($stream_where_config);
        if ($avatar_set->getCount() != 1) {
            $this->output->setSwapTagString("message", "Unable to find avatar");
            return;
        }
        if ($stream_set->getCount() != 1) {
            $this->output->setSwapTagString("message", "Unable to find stream");
            return;
        }
        if ($daysremaining > 999) {
            $this->output->setSwapTagString("message", "daysremaining must be 999 or less");
            return;
        }
        if ($daysremaining < 1) {
            $this->output->setSwapTagString("message", "daysremaining must be 1 or more");
            return;
        }
        if ($stream->getRentallink() > 0) {
            $this->output->setSwapTagString("message", "Stream already has a rental attached");
            return;
        }
        if ($stream_set->getCount() == 1) {
            $stream = $stream_set->getFirst();
        }
        $notice_set->loadAll();
        $sorted_linked = $notice_set->getLinkedArray("hoursremaining", "id");
        ksort($sorted_linked, SORT_NUMERIC);
        $hours_remain = $daysremaining * 24;
        $use_notice_index = 0;
        foreach ($sorted_linked as $hours => $index) {
            if ($hours > $hours_remain) {
                break;
            } else {
                $use_notice_index = $index;
            }
        }

        $avatar = $avatar_set->getFirst();
        $unixtime = time() + ($daysremaining * $unixtime_day);
        $rental = new Rental();
        $uid = $rental->createUID("rental_uid", 8, 10);
        if ($uid["status"] == false) {
            $this->output->setSwapTagString("message", "Unable to create a new Client uid");
            return;
        }
        $rental->setRental_uid($uid["uid"]);
        $rental->setAvatarlink($avatar->getId());
        $rental->setPackagelink($stream->getPackagelink());
        $rental->setStreamlink($stream->getId());
        $rental->setStartunixtime(time());
        $rental->setExpireunixtime($unixtime);
        $rental->setNoticelink($use_notice_index);
        $create_status = $rental->createEntry();
        if ($create_status["status"] == false) {
            $this->output->setSwapTagString(
                "message",
                sprintf("Unable to create a new Client: %1\$s", $create_status["message"])
            );
            return;
        }
        $stream->setRentallink($rental->getId());
        $stream->setNeedwork(0);
        $update_status = $stream->updateEntry();
        if ($update_status["status"] == false) {
            $this->output->setSwapTagString("message", "Unable to mark stream as linked to rental");
            return;
        }
        $this->output->setSwapTagString("status", "true");
        $this->output->setSwapTagString("message", "Client created");
    }
}
