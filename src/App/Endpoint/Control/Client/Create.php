<?php

namespace App\Endpoint\Control\Client;

use App\Helpers\RegionHelper;
use App\Helpers\ResellerHelper;
use App\Helpers\TransactionsHelper;
use App\Models\Avatar;
use App\Models\Set\AvatarSet;
use App\Models\Set\NoticeSet;
use App\Models\Rental;
use App\Models\Set\StreamSet;
use App\Template\ControlAjax;

class Create extends ControlAjax
{
    public function process(): void
    {
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
            }
            $use_notice_index = $index;
        }

        $avatar = $avatar_set->getFirst();
        $unixtime = time() + ($daysremaining * $this->siteConfig->unixtimeDay());
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
        $package = $stream->relatedPackage()->getFirst();
        $transactionHelper = new TransactionsHelper();
        $resellerHelper = new ResellerHelper();
        if ($resellerHelper->loadOrCreate($this->siteConfig->getSession()->getAvatarLinkId(), true, 0) == false) {
            $this->failed("Unable to create/load reseller for your account!");
            return;
        }
        $regionHelper = new RegionHelper();
        if ($regionHelper->loadOrCreate("website") == false) {
            $this->failed("Unable to create website region used for transaction log");
            return;
        }
        $result = $transactionHelper->createTransaction(
            $avatar,
            $package,
            $stream,
            $resellerHelper->getReseller(),
            $regionHelper->getRegion(),
            0,
            false,
            time()
        );
        if ($result == false) {
            $this->failed("Unable to create transaction!");
            return;
        }
        $server = $stream->relatedServer()->getFirst();
        $this->redirectWithMessage("Client created");
        $this->createAuditLog(
            $rental->getRentalUid(),
            "+++",
            $avatar->getAvatarName(),
            "Port: " . $stream->getPort() . " Server: " . $server->getDomain()
        );
    }
}
