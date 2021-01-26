<?php

namespace App\Endpoint\View\Client;

use App\Models\ApirequestsSet;
use App\Models\AvatarSet;
use App\Models\NoticeSet;
use App\Models\RentalSet;
use App\Models\ServerSet;
use App\Models\StreamSet;

abstract class RenderList extends View
{
    protected RentalSet $rentalSet;
    protected AvatarSet $avatarSet;
    protected StreamSet $streamSet;
    protected ServerSet $serverSet;
    protected NoticeSet $noticeSet;
    protected ApirequestsSet $apiRequestsSet;
    public function process(): void
    {
        $table_head = ["id","Rental UID","Avatar","Port","Notecard","Timeleft/Expired","Renewals"];
        $table_body = [];

        foreach ($this->rentalSet->getAllIds() as $rental_id) {
            $rental = $this->rentalSet->getObjectByID($rental_id);
            $avatar = $this->avatarSet->getObjectByID($rental->getAvatarLink());
            $stream = $this->streamSet->getObjectByID($rental->getStreamLink());
            $entry = [];
            $entry[] = $rental->getId();
            $entry[] = '<a href="[[url_base]]client/manage/' . $rental->getRentalUid() . '">'
            . $rental->getRentalUid() . '</a>';
            $av_detail = explode(" ", $avatar->getAvatarName());

            $name = $avatar->getAvatarName();
            if ($av_detail[1] == "Resident") {
                $entry[] = $av_detail[0];
            }
            $entry[] = $name;
            $entry[] = $stream->getPort();
            $entry[] = "<button type=\"button\" class=\"btn btn-sm btn-outline-light\" "
            . "data-toggle=\"modal\" data-target=\"#NotecardModal\" data-rentaluid=\""
            . $rental->getRentalUid() . "\">View</button>";

            $status = "Expired - " . expiredAgo($rental->getExpireUnixtime());
            if ($rental->getExpireUnixtime() > time()) {
                $status = "Active - " . timeleftHoursAndDays($rental->getExpireUnixtime());
            }
            $entry[] = $status;
            $entry[] = $rental->getRenewals();
            $table_body[] = $entry;
        }
        $this->output->addSwapTagString("page_content", $this->renderDatatable($table_head, $table_body));
    }
}
