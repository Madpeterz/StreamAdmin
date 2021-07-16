<?php

namespace App\Endpoint\View\Stream;

use App\R7\Set\AvatarSet;
use App\R7\Set\RentalSet;
use App\R7\Set\ServerSet;
use App\R7\Set\StreamSet;

abstract class RenderList extends View
{
    protected RentalSet $rentalSet;
    protected StreamSet $streamSet;
    protected array $rental_set_ids = [];
    public function process(): void
    {
        $table_head = ["id","UID","Server","Port","Status"];
        $table_body = [];

        $avatar_set = new AvatarSet();
        $avatar_set->loadIds($this->rentalSet->getAllByField("avatarLink"));
        $server_set = new ServerSet();
        $server_set->loadAll();

        foreach ($this->streamSet->getAllIds() as $streamid) {
            $stream = $this->streamSet->getObjectByID($streamid);
            $server = $server_set->getObjectByID($stream->getServerLink());


            $entry = [];
            $entry[] = $stream->getId();
            $entry[] = '<a href="[[url_base]]stream/manage/' . $stream->getStreamUid() . '">'
            . $stream->getStreamUid() . '</a>';
            $entry[] = $server->getDomain();
            $entry[] = $stream->getPort();
            if ($stream->getNeedWork() == true) {
                $entry[] = "<span class=\"needWork\">Need work</span>";
            } elseif ($stream->getRentalLink() == null) {
                $entry[] = "<span class=\"ready\">Available</span>";
            } elseif (in_array($stream->getRentalLink(), $this->rental_set_ids) == false) {
                $entry[] = "Rented but cant find rental.";
            } else {
                $rental = $this->rentalSet->getObjectByID($stream->getRentalLink());
                $avatar = $avatar_set->getObjectByID($rental->getAvatarLink());
                $av_detail = explode(" ", $avatar->getAvatarName());
                $av_name = $avatar->getAvatarName();
                if ($av_detail[1] == "Resident") {
                    $av_name = $av_detail[0];
                }
                $entry[] = '<a class="sold" href="[[url_base]]client/manage/'
                . $rental->getRentalUid() . '">Sold -> ' . $av_name . '</a>';
            }
            $table_body[] = $entry;
        }
        $this->setSwapTag("page_content", $this->renderDatatable($table_head, $table_body));
    }
}
