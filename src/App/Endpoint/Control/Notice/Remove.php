<?php

namespace App\Endpoint\Control\Notice;

use App\R7\Set\NotecardSet;
use App\R7\Model\Notice;
use App\R7\Set\RentalSet;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Remove extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $notice = new Notice();
        $notecard_set = new NotecardSet();

        $accept = $input->postString("accept");
        $this->setSwapTag("redirect", "notice");
        if ($accept != "Accept") {
            $this->failed("Did not Accept");
            $this->setSwapTag("redirect", "notice/manage/" . $this->page . "");
            return;
        }
        if (in_array($this->page, [6,10]) == true) {
            $this->failed("Selected notice is protected");
            return;
        }

        if ($notice->loadID($this->page) == false) {
            $this->failed("Unable to find notice");
            return;
        }

        $RentalSet = new RentalSet();
        $whereConfig[] = [
            "fields" => ["noticeLink"],
            "values" => [$notice->getId()],
        ];
        $count = $RentalSet->countInDB($whereConfig);
        if ($count === null) {
            $this->failed("Unable to remove notice, unable to check in use.");
            return;
        }
        if ($count > 0) {
            $this->failed("Unable to remove notice it is currently in use by " . $count . " rentals!");
            return;
        }

        $load_status = $notecard_set->loadOnField("noticeLink", $notice->getId());
        if ($load_status["status"] == false) {
            $this->failed(
                "Unable to check if notice is being used by any pending notecards"
            );
            return;
        }
        if ($notecard_set->getCount() != 0) {
            $this->failed(
                sprintf(
                    "Unable to remove notice it is being used by %1\$s pending notecards!",
                    $notecard_set->getCount()
                )
            );
            return;
        }
        $remove_status = $notice->removeEntry();
        if ($remove_status["status"] == false) {
            $this->failed(
                sprintf("Unable to remove notice: %1\$s", $remove_status["message"])
            );
            return;
        }
        $this->ok("Notice removed");
    }
}
