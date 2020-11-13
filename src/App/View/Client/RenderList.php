<?php

namespace App\View\Client;

use App\AvatarSet;
use App\RentalSet;
use App\StreamSet;

abstract class RenderList extends View
{
    protected RentalSet $rentalSet;
    protected AvatarSet $avatarSet;
    protected StreamSet $streamSet;
    public function process()
    {
        $table_head = array("id","Rental UID","Avatar","Port","Notecard","Timeleft/Expired","Renewals");
        $table_body = [];

        foreach ($this->rentalSet->getAllIds() as $rental_id) {
            $rental = $this->rentalSet->getObjectByID($rental_id);
            $avatar = $this->avatarSet->getObjectByID($rental->getAvatarlink());
            $stream = $this->streamSet->getObjectByID($rental->getStreamlink());
            $entry = [];
            $entry[] = $rental->getId();
            $entry[] = '<a href="[[url_base]]client/manage/' . $rental->getRental_uid() . '">' . $rental->getRental_uid() . '</a>';
            $av_detail = explode(" ", $avatar->getAvatarname());
            if ($av_detail[1] != "Resident") {
                $entry[] = $avatar->getAvatarname();
            } else {
                $entry[] = $av_detail[0];
            }
            $entry[] = $stream->getPort();
            $entry[] = "<button type=\"button\" class=\"btn btn-sm btn-outline-light\" "
            . "data-toggle=\"modal\" data-target=\"#NotecardModal\" data-rentaluid=\""
            . $rental->getRental_uid() . "\">View</button>";
            if ($rental->getExpireunixtime() > time()) {
                $entry[] = "Active - " . timeleft_hours_and_days($rental->getExpireunixtime());
            } else {
                $entry[] = "Expired - " . expired_ago($rental->getExpireunixtime());
            }
            $entry[] = $rental->getRenewals();
            $table_body[] = $entry;
        }
        $this->output->addSwapTagString("page_content", render_datatable($table_head, $table_body));
    }
}
