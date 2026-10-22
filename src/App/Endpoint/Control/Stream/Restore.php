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
            if ($transaction->_Renew == 0) {
                if ($transaction->_Unixtime >= $newest_new_unixtime) {
                    $newest_new_unixtime = $newest_new_unixtime;
                    $newest_new_avatar_id = $transaction->_AvatarLink;
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
        $rental->_AvatarLink = $avatar->_Id;
        $rental->_ExpireUnixtime = time();
        $rental->_Message = "Restored rental!";
        $uid_rental = $rental->createUID("rentalUid", 8);
        if ($uid_rental->status == false) {
            $this->failed("Unable to assign uid: " . $uid_rental->message);
            return;
        }
        $rental->_RentalUid = $uid_rental->uid;
        $rental->_StreamLink = $stream->_Id;
        $rental->_PackageLink = $stream->_PackageLink;
        $rental->_NoticeLink = $use_notice_index;
        $rental->_StartUnixtime = time();
        $create = $rental->createEntry();
        if ($create->status == false) {
            $this->failed("Failed to create rental: " . $create->message);
            return;
        }
        $stream->_NeedWork = 0;
        $stream->_RentalLink = $rental->_Id;
        $update = $stream->updateEntry();
        if ($update->status == false) {
            $this->failed("Failed to update stream: " . $update->message);
            return;
        }
        $server = $stream->relatedServer()->getFirst();
        $this->redirectWithMessage("Stream restored");
        $this->createAuditLog(
            $stream->_StreamUid,
            "restored",
            $avatar->_AvatarName,
            "Port: " . $stream->_Port . " Server: " . $server->_Domain
        );
    }
}
