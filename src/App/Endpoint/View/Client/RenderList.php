<?php

namespace App\Endpoint\View\Client;

use App\Models\Sets\AvatarSet;
use App\Models\Sets\NoticeSet;
use App\Models\Sets\RentalSet;
use App\Models\Sets\ServerSet;
use App\Models\Sets\StreamSet;

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
        if ($this->siteConfig->getSlConfig()->getClientsDisplayServer() == true) {
            $this->serverSet = $this->streamSet->relatedServer();
            $table_head[] = "Server";
        }


        foreach ($this->rentalSet as $rental) {
            $avatar = $this->avatarSet->getObjectByID($rental->getAvatarLink());
            $stream = $this->streamSet->getObjectByID($rental->getStreamLink());
            $entry = [];
            $entry[] = $rental->getId();
            $entry[] = '<a href="[[SITE_URL]]client/manage/' . $rental->getRentalUid() . '">'
            . $rental->getRentalUid() . '</a>';
            $av_detail = explode(" ", $avatar->getAvatarName());

            $name = $avatar->getAvatarName();
            if ($av_detail[1] == "Resident") {
                $name = $av_detail[0];
            }
            $entry[] = $name;
            $entry[] = $stream->getPort();
            $entry[] = "<button type=\"button\" class=\"btn btn-sm btn-outline-light\" "
            . "data-toggle=\"modal\" data-target=\"#NotecardModal\" data-rentaluid=\""
            . $rental->getRentalUid() . "\">View</button>";

            $timeleft = "-" . $this->expiredAgo($rental->getExpireUnixtime());
            if ($rental->getExpireUnixtime() > time()) {
                $timeleft = $this->timeRemainingHumanReadable($rental->getExpireUnixtime());
            }
            $entry[] = $timeleft;
            $noticeLevel = $this->noticeSet->getObjectByID($rental->getNoticeLink());
            $entry[] = $noticeLevel->getName();
            $entry[] = $rental->getRenewals();
            if ($this->siteConfig->getSlConfig()->getClientsDisplayServer() == true) {
                $server = $this->serverSet->getObjectByID($stream->getServerLink());
                $entry[] = $server->getDomain();
            }
            $table_body[] = $entry;
        }
        $this->output->addSwapTagString("page_content", $this->renderDatatable($table_head, $table_body, 3));
    }
}
