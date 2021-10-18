<?php

namespace App\Endpoint\Control\Client;

use App\R7\Model\Rental;
use App\R7\Model\Rentalnoticeptout;
use App\R7\Set\NoticeSet;
use App\R7\Set\RentalnoticeptoutSet;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class NoticeOptout extends ViewAjax
{
    public function process(): void
    {
        $this->setSwapTag("redirect", "Client/Manage/" . $this->page . "?tab=tabid6");
        $rental = new Rental();
        $input = new InputFilter();

        if ($rental->loadByRentalUid($this->page) == false) {
            $this->failed("Unable to find client");
            $this->setSwapTag("redirect", "client");
            return;
        }

        $noticeLevels = new NoticeSet();
        $whereConfig = [
            "fields" => ["id"],
            "values" => [10],
            "matches" => ["!="],
        ];
        $load = $noticeLevels->loadWithConfig($whereConfig);
        if ($load["status"] == false) {
            $this->failed("Unable to load notice levels");
            return;
        }

        $client_opt_out = new RentalnoticeptoutSet();
        $load = $client_opt_out->loadByRentalLink($rental->getId());
        if ($load["status"] == false) {
            $this->failed("Unable to load current opt-outs");
            $this->setSwapTag("redirect", "client");
            return;
        }

        $remove_client_opt_out = new RentalnoticeptoutSet();

        $enabledCounter = 0;
        $opt_out_notice_ids = $client_opt_out->getUniqueArray("noticeLink");
        foreach ($noticeLevels as $noticeLevel) {
            if (in_array($noticeLevel->getId(), $opt_out_notice_ids) == true) {
                $check = $input->postBool("remove-optout-" . $noticeLevel->getId());
                if ($check !== true) {
                    continue;
                }
                $opt_out = $client_opt_out->getObjectByField("noticeLink", $noticeLevel->getId());
                if ($opt_out == null) {
                    $this->failed(
                        sprintf(
                            "Unable to find loaded opt-out for notice level: %1\$s",
                            $noticeLevel->getName()
                        )
                    );
                    return;
                }
                $remove_client_opt_out->addToCollected($opt_out);
                continue;
            }
            $check = $input->postBool("add-optout-" . $noticeLevel->getId());
            if ($check !== true) {
                continue;
            }
            $newOptOut = new Rentalnoticeptout();
            $newOptOut->setRentalLink($rental->getId());
            $newOptOut->setNoticeLink($noticeLevel->getId());
            $addStatus = $newOptOut->createEntry();
            if ($addStatus["status"] == false) {
                $this->failed(
                    sprintf(
                        "Unable to create new opt-out because: %1\$s",
                        $addStatus["message"]
                    )
                );
                return;
            }
            $enabledCounter++;
        }

        $removedCounter = $remove_client_opt_out->getCount();
        if ($remove_client_opt_out->getCount() > 0) {
            $status = $remove_client_opt_out->purgeCollection();
            if ($status["status"] == false) {
                $this->failed(
                    sprintf(
                        "Unable to purge unwanted opt-outs because: %1\$s",
                        $status["message"]
                    )
                );
                return;
            }
        }

        if (($enabledCounter + $removedCounter) == 0) {
            $this->failed("No changes made");
            return;
        }

        $this->ok(
            sprintf(
                "Opt-outs updated enabled: %1\$s and removed %2\$s",
                $enabledCounter,
                $removedCounter
            )
        );
    }
}
