<?php

namespace App\Endpoint\Control\Client;

use App\Models\Avatar;
use App\Models\Sets\AvatarSet;
use App\Models\Sets\NoticeSet;
use App\Models\Rental;
use App\Models\Sets\StreamSet;
use App\Template\ControlAjax;

class Create extends ControlAjax
{
    public function process(): void
    {
        global $unixtime_day;

        $avatar = new Avatar();

        $avataruid = $this->input->post("avataruid")->checkStringLengthMin(3)->asString();
        if ($avataruid  == null) {
            $this->failed("Avatar failed:" . $this->input->getWhyFailed());
        }
        $streamuid = $this->input->post("streamuid")->checkStringLengthMin(4)->asString();
        if ($streamuid == null) {
            $this->failed("Stream UID failed:" . $this->input->getWhyFailed());
            return;
        }
        $daysremaining = $this->input->post("daysremaining")->checkInRange(1, 360)->asInt();
        if ($daysremaining == null) {
            $this->failed("Days remaining failed:" . $this->input->getWhyFailed());
            return;
        }
        $this->setSwapTag("redirect", "client");

        $avatar_where_config = [
            "fields" => ["avatarUid","avatarName","avatarUUID"],
            "matches" => ["=","=","="],
            "values" => [$avataruid,$avataruid,$avataruid],
            "types" => ["s","s","s"],
            "joinWith" => ["OR","OR"],
        ];

        $avatar_set = new AvatarSet();
        $avatar_set->loadWithConfig($avatar_where_config);
        if ($avatar_set->getCount() != 1) {
            $this->failed("Unable to find avatar: " . $avatar_set->getLastSql());
            return;
        }

        $stream_where_config = [
            "fields" => ["port","streamUid"],
            "matches" => ["=","="],
            "values" => [$streamuid,$streamuid],
            "types" => ["i","s"],
            "joinWith" => ["OR"],
        ];
        $stream_set = new StreamSet();
        $stream_set->loadWithConfig($stream_where_config);
        if ($stream_set->getCount() != 1) {
            $this->failed("Unable to find stream");
            return;
        }

        $stream = $stream_set->getFirst();
        if ($stream->getRentalLink() > 0) {
            $this->failed("Stream already has a rental attached");
            return;
        }

        $notice_set = new NoticeSet();
        $notice_set->loadAll();
        $sorted_linked = $notice_set->getLinkedArray("hoursRemaining", "id");
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
        $uid = $rental->createUID("rentalUid", 8);
        if ($uid->status == false) {
            $this->failed("Unable to create a new Client uid");
            return;
        }
        $rental->setRentalUid($uid->uid);
        $rental->setAvatarLink($avatar->getId());
        $rental->setPackageLink($stream->getPackageLink());
        $rental->setStreamLink($stream->getId());
        $rental->setStartUnixtime(time());
        $rental->setExpireUnixtime($unixtime);
        $rental->setNoticeLink($use_notice_index);
        $create_status = $rental->createEntry();
        if ($create_status->status == false) {
            $this->failed(sprintf("Unable to create a new Client: %1\$s", $create_status->message));
            return;
        }
        $stream->setRentalLink($rental->getId());
        $stream->setNeedWork(0);
        $update_status = $stream->updateEntry();
        if ($update_status->status == false) {
            $this->failed("Unable to mark stream as linked to rental");
            return;
        }
        $this->redirectWithMessage("Client created");
    }
}
