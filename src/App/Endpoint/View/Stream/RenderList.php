<?php

namespace App\Endpoint\View\Stream;

use App\Models\AvatarSet;
use App\Models\RentalSet;
use App\Models\ServerSet;
use App\Models\StreamSet;

abstract class RenderList extends View
{
    protected RentalSet $rentalSet;
    protected StreamSet $streamSet;
    protected Array $rental_set_ids = [];
    public function process(): void
    {
        $table_head = ["id","UID","Server","Port","Status"];
        $table_body = [];

        $avatar_set = new AvatarSet();
        $avatar_set->loadIds($this->rental_set->getAllByField("avatarLink"));
        $server_set = new ServerSet();
        $server_set->loadAll();

        foreach ($this->stream_set->getAllIds() as $streamid) {
            $stream = $this->stream_set->getObjectByID($streamid);
            $server = $this->server_set->getObjectByID($stream->getServerLink());


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
                $rental = $this->rental_set->getObjectByID($stream->getRentalLink());
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
        $this->setSwapTag("page_content", render_datatable($table_head, $table_body));
    }
}
