<?php

namespace App\Endpoint\Control\Client;

use App\Models\Rental;
use App\Models\Rentalnoticeptout;
use App\Models\Sets\NoticeSet;
use App\Models\Sets\RentalnoticeptoutSet;
use App\Template\ControlAjax;

class NoticeOptOut extends ControlAjax
{
    public function process(): void
    {
        $this->setSwapTag("redirect", "Client/Manage/" . $this->siteConfig->getPage() . "?tab=tabid6");
        $rental = new Rental();

        if ($rental->loadByRentalUid($this->siteConfig->getPage())->status == false) {
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
        if ($load->status == false) {
            $this->failed("Unable to load notice levels");
            return;
        }

        $client_opt_out = $rental->relatedRentalnoticeptout();

        $remove_client_opt_out = new RentalnoticeptoutSet();

        $enabledCounter = 0;
        $opt_out_notice_ids = $client_opt_out->uniqueNoticeLinks();
        foreach ($noticeLevels as $noticeLevel) {
            if (in_array($noticeLevel->getId(), $opt_out_notice_ids) == true) {
                $check = $this->input->post("remove-optout-" . $noticeLevel->getId())->asBool();
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
            $check = $this->input->post("add-optout-" . $noticeLevel->getId())->asBool();
            if ($check !== true) {
                continue;
            }
            $newOptOut = new Rentalnoticeptout();
            $newOptOut->setRentalLink($rental->getId());
            $newOptOut->setNoticeLink($noticeLevel->getId());
            $addStatus = $newOptOut->createEntry();
            if ($addStatus->status == false) {
                $this->failed(
                    sprintf(
                        "Unable to create new opt-out because: %1\$s",
                        $addStatus->message
                    )
                );
                return;
            }
            $enabledCounter++;
        }

        $removedCounter = $remove_client_opt_out->getCount();
        if ($remove_client_opt_out->getCount() > 0) {
            $status = $remove_client_opt_out->purgeCollection();
            if ($status->status == false) {
                $this->failed(
                    sprintf(
                        "Unable to purge unwanted opt-outs because: %1\$s",
                        $status->message
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
