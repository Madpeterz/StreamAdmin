<?php

namespace App\Endpoint\Control\Stream;

use App\Helpers\NoticesHelper;
use App\Models\Avatar;
use App\Models\Rental;
use App\Models\Stream;
use App\Template\ControlAjax;

class Restore extends ControlAjax
{
    public function process(): void
    {
        $accept = $this->input->post("accept")->asString();
        if ($accept != "Accept") {
            $this->failed("Did not Accept");
            $this->setSwapTag("redirect", "stream/manage/" . $this->siteConfig->getPage() . "");
            return;
        }
        $stream = new Stream();
        if ($stream->loadByStreamUid($this->siteConfig->getPage())->status == false) {
            $this->failed("Unable to find stream");
            return;
        }
        $transaction_set = $stream->relatedTransactions();
        $newest_new_avatar_id = 0;
        $newest_new_unixtime = 0;
        foreach ($transaction_set as $transaction) {
            if ($transaction->getRenew() == 0) {
                if ($transaction->getUnixtime() >= $newest_new_unixtime) {
                    $newest_new_unixtime = $newest_new_unixtime;
                    $newest_new_avatar_id = $transaction->getAvatarLink();
                }
            }
        }
        if ($newest_new_avatar_id == 0) {
            $this->failed("Unable to find avatar to restore to");
            return;
        }
        $avatar = new Avatar();
        if ($avatar->loadId($newest_new_avatar_id)->status == false) {
            $this->failed("Unable to load avatar");
            return;
        }

        $noticesHelper = new NoticesHelper();
        $use_notice_index = $noticesHelper->getNoticeLevel(0);

        $rental = new Rental();
        $rental->setAvatarLink($avatar->getId());
        $rental->setExpireUnixtime(time());
        $rental->setMessage("Restored rental!");
        $uid_rental = $rental->createUID("rentalUid", 8);
        if ($uid_rental->status == false) {
            $this->failed("Unable to assign uid");
            return;
        }
        $rental->setRentalUid($uid_rental->uid);
        $rental->setStreamLink($stream->getId());
        $rental->setPackageLink($stream->getPackageLink());
        $rental->setNoticeLink($use_notice_index);
        $create = $rental->createEntry();
        if ($create->status == false) {
            $this->failed("Failed to create rental");
            return;
        }
        $stream->setNeedWork(0);
        $stream->setRentalLink($rental->getId());
        $update = $stream->updateEntry();
        if ($update->status == false) {
            $this->failed("Failed to update stream");
            return;
        }
        $this->redirectWithMessage("Stream reassigned");
    }
}