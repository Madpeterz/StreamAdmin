<?php

namespace App\Endpoint\View\Stream;

use App\Models\Sets\AvatarSet;
use App\Models\Sets\RentalSet;
use App\Models\Sets\ServerSet;
use App\Models\Sets\StreamSet;

abstract class RenderList extends View
{
    protected RentalSet $rentalSet;
    protected StreamSet $streamSet;
    protected array $rental_set_ids = [];
    public function process(): void
    {
        $table_head = ["id","UID","Server","Port","Status"];
        $table_body = [];

        $avatar_set = $this->rentalSet->relatedAvatar();
        $server_set = $this->streamSet->relatedServer();

        foreach ($this->streamSet as $stream) {
            $server = $server_set->getObjectByID($stream->getServerLink());
            $entry = [];
            $entry[] = $stream->getId();
            $entry[] = '<a href="[[SITE_URL]]stream/manage/' . $stream->getStreamUid() . '">'
            . $stream->getStreamUid() . '</a>';
            $entry[] = $server->getDomain();
            $entry[] = $stream->getPort();
            $state = "Rented but cant find rental.";
            if ($stream->getNeedWork() == true) {
                $state = "<span class=\"needWork\">Need work</span>";
            } elseif ($stream->getRentalLink() == null) {
                $state = "<span class=\"ready\">Available</span>";
            } elseif (in_array($stream->getRentalLink(), $this->rental_set_ids) == true) {
                $rental = $this->rentalSet->getObjectByID($stream->getRentalLink());
                $avatar = $avatar_set->getObjectByID($rental->getAvatarLink());
                $av_detail = explode(" ", $avatar->getAvatarName());
                $av_name = $avatar->getAvatarName();
                if ($av_detail[1] == "Resident") {
                    $av_name = $av_detail[0];
                }
                $state = '<a class="sold" href="[[SITE_URL]]client/manage/'
                . $rental->getRentalUid() . '">Sold -> ' . $av_name . '</a>';
            }
            $entry[] = $state;
            $table_body[] = $entry;
        }
        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body, 4));
    }
}
