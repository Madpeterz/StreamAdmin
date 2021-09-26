<?php

namespace App\Endpoint\Control\Client;

use App\R7\Model\Avatar;
use App\R7\Set\AvatarSet;
use App\R7\Set\NoticeSet;
use App\R7\Model\Rental;
use App\R7\Model\Stream;
use App\R7\Set\StreamSet;
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

        $avataruid = $input->postString("avataruid");
        if ($avataruid  == null) {
            $this->failed("Avatar failed:" . $input->getWhyFailed());
        }
        $streamuid = $input->postString("streamuid");
        if ($streamuid == null) {
            $this->failed("Stream UID failed:" . $input->getWhyFailed());
            return;
        }
        $daysremaining = $input->postInteger("daysremaining", false, true);
        if ($daysremaining == null) {
            $this->failed("Days remaining failed:" . $input->getWhyFailed());
            return;
        }
        if ($daysremaining > 360) {
            $this->failed("Attempt to create a client with more than 360 days remaining");
            return;
        }
        $this->setSwapTag("redirect", "client");

        $avatar_where_config = [
            "fields" => ["avatarUid","avatarName","avatarUUID"],
            "matches" => ["=","=","="],
            "values" => [$avataruid,$avataruid,$avataruid],
            "types" => ["s","s","s"],
            "join_with" => ["OR","OR"],
        ];

        $avatar_set->loadWithConfig($avatar_where_config);
        if ($avatar_set->getCount() != 1) {
            $this->failed("Unable to find avatar");
            return;
        }

        $stream_where_config = [
            "fields" => ["port","streamUid"],
            "matches" => ["=","="],
            "values" => [$streamuid,$streamuid],
            "types" => ["i","s"],
            "join_with" => ["OR"],
        ];

        $stream_set->loadWithConfig($stream_where_config);
        if ($stream_set->getCount() != 1) {
            $this->failed("Unable to find stream");
            return;
        }
        if ($stream->getRentalLink() > 0) {
            $this->failed("Stream already has a rental attached");
            return;
        }
        if ($stream_set->getCount() == 1) {
            $stream = $stream_set->getFirst();
        }
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
        if ($uid["status"] == false) {
            $this->failed("Unable to create a new Client uid");
            return;
        }
        $rental->setRentalUid($uid["uid"]);
        $rental->setAvatarLink($avatar->getId());
        $rental->setPackageLink($stream->getPackageLink());
        $rental->setStreamLink($stream->getId());
        $rental->setStartUnixtime(time());
        $rental->setExpireUnixtime($unixtime);
        $rental->setNoticeLink($use_notice_index);
        $create_status = $rental->createEntry();
        if ($create_status["status"] == false) {
            $this->failed(sprintf("Unable to create a new Client: %1\$s", $create_status["message"]));
            return;
        }
        $stream->setRentalLink($rental->getId());
        $stream->setNeedWork(0);
        $update_status = $stream->updateEntry();
        if ($update_status["status"] == false) {
            $this->failed("Unable to mark stream as linked to rental");
            return;
        }
        $this->ok("Client created");
    }
}
