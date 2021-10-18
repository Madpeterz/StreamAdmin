<?php

namespace App\Endpoint\Control\Client;

use App\Helpers\NoticesHelper;
use App\R7\Model\Avatar;
use App\R7\Set\NoticeSet;
use App\R7\Model\Rental;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Update extends ViewAjax
{
    protected $actions_taken = "";
    protected $isseus = "";
    protected $message = "";
    protected $apiAllowSuspend = true;
    protected function transerRental(Rental $rental, string $transfer_avataruid): void
    {
        $avatar = new Avatar();
        $avatar_from = new Avatar();
        if ($avatar->loadByAvatarUid($transfer_avataruid) == false) {
            $this->issues .= "Unable to find avatar to transfer to";
            return;
        }
        if ($avatar_from->loadID($rental->getAvatarLink()) == false) {
            $this->issues .= "Unable to find avatar to transfer from";
            return;
        }
        $rental->setAvatarLink($avatar->getId());
        $this->actions_taken .= "\n Ownership transfered";
        $this->message .= sprintf(
            "\n %1\$s - Transfer to: %2\$s [%3\$s] from %4\$s [%5\$s]",
            date("F j, Y, g:i a", time()),
            $avatar->getAvatarName(),
            $avatar->getAvatarUid(),
            $avatar_from->getAvatarName(),
            $avatar_from->getAvatarUid()
        );
    }
    protected function adjustTimeleft(
        Rental $rental,
        int $adjustment_days,
        int $adjustment_hours,
        string $adjustment_dir
    ): void {
        global $unixtime_hour;

        $total_adjust_hours = 0;
        if ($adjustment_hours > 0) {
            $total_adjust_hours += $adjustment_hours;
        }
        if ($adjustment_days > 0) {
            $total_adjust_hours += ($adjustment_days * 24);
        }
        if ($total_adjust_hours <= 0) {
            $this->issues .= "[Adjustment] Attempted adjustment but failed no adjustment given?";
            return;
        }
        $adjustment_unixtime = $unixtime_hour * $total_adjust_hours;
        $adjustment_text = "Added";
        $new_unixtime = $rental->getExpireUnixtime() + $adjustment_unixtime;
        if ($adjustment_dir == false) {
            $adjustment_text = "Removed";
            $new_unixtime = $rental->getExpireUnixtime() - $adjustment_unixtime;
        }

        $add_days = 0;
        while ($total_adjust_hours >= 24) {
            $add_days += 1;
            $total_adjust_hours -= 24;
        }
        $adjustment_amount = $total_adjust_hours;
        $adjustment_type = "hour";
        $adjustment_multi = "";
        if ($add_days > 0) {
            $adjustment_amount = $add_days;
            $adjustment_type = "day";
        }
        if ($adjustment_amount > 1) {
            $adjustment_multi = "s";
        }

        $adjustment_message = sprintf(
            "%1\$s - %2\$s %3\$s %4\$s%5\$s \n",
            date("F j, Y, g:i a", time()),
            $adjustment_text,
            $adjustment_amount,
            $adjustment_type,
            $adjustment_multi
        );
        $this->message = "" . $adjustment_message . "" . $this->message . "";
        $hours_remain = ceil(($new_unixtime - time()) / $unixtime_hour);
        $noticeHelper = new NoticesHelper();
        $this->setSwapTag("noticeLevelChanged", false);
        $this->setSwapTag("hoursRemain", $hours_remain);

        $foundNoticeid = $noticeHelper->getNoticeLevel($hours_remain);
        if ($rental->getNoticeLink() != $foundNoticeid) {
            $rental->setNoticeLink($foundNoticeid);
            $this->setSwapTag("noticeLevelChanged", true);
            $this->actions_taken .= "\n Adjusted notice level";
        }
        $rental->setExpireUnixtime($new_unixtime);
        $this->actions_taken .= "\n Adjusted timeleft";
    }

    public function process(): void
    {
        $rental = new Rental();
        $input = new InputFilter();

        $this->actions_taken = "";
        $this->issues = "";

        // adjustment
        $adjustment_days = $input->postInteger("adjustment_days");
        $adjustment_hours = $input->postInteger("adjustment_hours");
        $adjustment_dir = $input->postBool("adjustment_dir");
        if ($adjustment_dir === null) {
            $adjustment_dir = false;
        }
        // transfer
        $transfer_avataruid = $input->postString("transfer_avataruid");
        // message
        $this->message = $input->postString("message");
        if (strlen($this->message) < 1) {
            $this->message = null;
        }
        // API flag
        $this->apiAllowSuspend = $input->postBool("apiAllowSuspend");
        if ($this->apiAllowSuspend === null) {
            $this->apiAllowSuspend = false;
        }

        if ($rental->loadByRentalUid($this->page) == false) {
            $this->failed("Unable to find client");
            $this->setSwapTag("redirect", "client");
            return;
        }

        if (strlen($transfer_avataruid) == 8) {
            $this->transerRental($rental, $transfer_avataruid);
            if ($this->issues != "") {
                $this->failed($this->issues);
                return;
            }
        }

        if (($adjustment_days > 0) || ($adjustment_hours > 0)) {
            $this->adjustTimeleft($rental, $adjustment_days, $adjustment_hours, $adjustment_dir);
            if ($this->issues != "") {
                $this->failed($this->issues);
                return;
            }
        }
        if ($this->message != $rental->getMessage()) {
            $rental->setMessage($this->message);
            $this->actions_taken .= "\n Message Updated";
        }

        if ($this->apiAllowSuspend != $rental->getApiAllowAutoSuspend()) {
            $rental->setApiAllowAutoSuspend($this->apiAllowSuspend);
            $this->actions_taken .= "\n API allow auto suspend Updated";
        }

        if ($this->actions_taken == "") {
            $this->ok("? No actions taken ? ");
            return;
        }
        if ($this->issues != "") {
            $this->failed($this->issues);
            return;
        }
        if ($rental->getExpireUnixtime() > time()) {
            $rental->setApiSuspended(false);
            $rental->setApiPendingAutoSuspend(false);
            $rental->setApiPendingAutoSuspendAfter(null);
        }
        $change_status = $rental->updateEntry();
        if ($change_status["status"] != true) {
            $this->failed(sprintf(
                "Unable to update because: %1\$s",
                $change_status["message"]
            ));
            return;
        }
        $this->setSwapTag("redirect", "client/manage/" . $this->page);
        $this->ok($this->actions_taken);
    }
}
