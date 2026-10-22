<?php

namespace App\Endpoint\View\Client;

use App\Models\Set\AvatarSet;
use App\Models\Set\NoticeSet;
use App\Models\Set\RentalSet;
use App\Models\Set\ServerSet;
use App\Models\Set\StreamSet;

abstract class RenderList extends View
{
    protected RentalSet $rentalSet;
    protected AvatarSet $avatarSet;
    protected StreamSet $streamSet;
    protected ServerSet $serverSet;
    protected NoticeSet $noticeSet;
    public function process(): void
    {
        $this->noticeSet = $this->rentalSet->relatedNotice();
        $table_head = ["id","Rental UID","Avatar","Port","Notecard","Timeleft","Status","Renewals"];
        $table_body = [];
        if ($this->siteConfig->getSlConfig()->_ClientsDisplayServer == true) {
            $this->serverSet = $this->streamSet->relatedServer();
            $table_head[] = "Server";
        }


        foreach ($this->rentalSet as $rental) {
            $avatar = $this->avatarSet->getObjectByID($rental->_AvatarLink);
            $stream = $this->streamSet->getObjectByID($rental->_StreamLink);
            $entry = [];
            $entry[] = $rental->_Id;
            $entry[] = '<a href="[[SITE_URL]]client/manage/' . $rental->_RentalUid . '">'
            . $rental->_RentalUid . '</a>';
            $av_detail = explode(" ", $avatar->_AvatarName);

            $name = $avatar->_AvatarName;
            if ($av_detail[1] == "Resident") {
                $name = $av_detail[0];
            }
            $entry[] = $name;
            $entry[] = $stream->_Port;
            $entry[] = "<button type=\"button\" class=\"btn btn-sm btn-outline-light\" "
            . "data-toggle=\"modal\" data-target=\"#NotecardModal\" data-rentaluid=\""
            . $rental->_RentalUid . "\">View</button>";

            $timeleft = "-" . $this->expiredAgo($rental->_ExpireUnixtime);
            if ($rental->_ExpireUnixtime > time()) {
                $timeleft = $this->timeRemainingHumanReadable($rental->_ExpireUnixtime);
            }
            $entry[] = $timeleft;
            $noticeLevel = $this->noticeSet->getObjectByID($rental->_NoticeLink);
            $entry[] = $noticeLevel->_Name;
            $entry[] = $rental->_Renewals;
            if ($this->siteConfig->getSlConfig()->_ClientsDisplayServer == true) {
                $server = $this->serverSet->getObjectByID($stream->_ServerLink);
                $entry[] = $server->_Domain;
            }
            $table_body[] = $entry;
        }
        $this->output->addSwapTagString("page_content", $this->renderDatatable($table_head, $table_body, 3));
    }
}
