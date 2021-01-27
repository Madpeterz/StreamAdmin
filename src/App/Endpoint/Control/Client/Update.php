<?php

namespace App\Endpoint\Control\Client;

use App\Models\Avatar;
use App\Models\NoticeSet;
use App\Models\Rental;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Update extends ViewAjax
{
    protected $actions_taken = "";
    protected $isseus = "";
    protected $message = "";
    protected function transerRental(Rental $rental, string $transfer_avataruid): void
    {
        $avatar = new Avatar();
        $avatar_from = new Avatar();
        if ($avatar->loadByField("avatarUid", $transfer_avataruid) == false) {
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

        $notice_set = new NoticeSet();

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
        if ($adjustment_dir == false) {
            $adjustment_text = "Removed";
            $new_unixtime = $rental->getExpireUnixtime() - $adjustment_unixtime;
        } else {
            $new_unixtime = $rental->getExpireUnixtime() + $adjustment_unixtime;
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

        $notice_set->loadAll();
        $dif_array = [];
        foreach ($notice_set->getAllIds() as $notice_id) {
            $notice = $notice_set->getObjectByID($notice_id);
            if ($notice->getHoursRemaining() > 0) {
                $dif_array[$notice->getId()] = (time() + ($notice->getHoursRemaining() * $unixtime_hour));
            }
        }
        $closest_diff = null;
        $closest_diff_index = 0;
        foreach ($dif_array as $key => $value) {
            $diff = abs($new_unixtime - $value);
            if ($closest_diff == null) {
                $closest_diff = $diff;
                $closest_diff_index = $key;
            } else {
                if ($diff < $closest_diff) {
                    $closest_diff = $diff;
                    $closest_diff_index = $key;
                }
            }
        }
        if ($closest_diff_index != 0) {
            if ($rental->getNoticeLink() != $closest_diff_index) {
                $rental->setNoticeLink($closest_diff_index);
            }
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
        $adjustment_days = $input->postFilter("adjustment_days", "integer");
        $adjustment_hours = $input->postFilter("adjustment_hours", "integer");
        $adjustment_dir = $input->postFilter("adjustment_dir", "bool"); // array(false=>"Remove",true=>"Add")
        // transfer
        $transfer_avataruid = $input->postFilter("transfer_avataruid");
        // message
        $this->message = $input->postFilter("message");
        if (strlen($this->message) < 1) {
            $this->message = null;
        }

        if ($rental->loadByField("rentalUid", $this->page) == false) {
            $this->setSwapTag("message", "Unable to find client");
            $this->setSwapTag("redirect", "client");
            return;
        }

        if (strlen($transfer_avataruid) == 8) {
            $this->transerRental($rental, $transfer_avataruid);
            if ($this->issues != "") {
                $this->setSwapTag("message", $this->issues);
                return;
            }
        }

        if (($adjustment_days > 0) || ($adjustment_hours > 0)) {
            $this->adjustTimeleft($rental, $adjustment_days, $adjustment_hours, $adjustment_dir);
            if ($this->issues != "") {
                $this->setSwapTag("message", $this->issues);
                return;
            }
        }
        if ($this->message != $rental->getMessage()) {
            $rental->setMessage($this->message);
            $this->actions_taken .= "\n Message Updated";
        }
        if ($this->actions_taken == "") {
            $this->setSwapTag("message", "? No actions taken ? ");
            return;
        }
        if ($this->issues != "") {
            $this->setSwapTag("message", $this->issues);
            return;
        }
        $change_status = $rental->updateEntry();
        if ($change_status["status"] != true) {
            $this->setSwapTag(
                "message",
                sprintf(
                    "Unable to update because: %1\$s",
                    $change_status["message"]
                )
            );
            return;
        }
        $this->setSwapTag("status", true);
        $this->setSwapTag("redirect", "client/manage/" . $this->page);
        $this->setSwapTag("message", $this->actions_taken);
    }
}
