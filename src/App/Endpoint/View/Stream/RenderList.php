<?php

namespace App\Endpoints\View\Stream;

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
        $avatar_set->loadIds($this->rental_set->getAllByField("avatarlink"));
        $server_set = new ServerSet();
        $server_set->loadAll();

        foreach ($this->stream_set->getAllIds() as $streamid) {
            $stream = $this->stream_set->getObjectByID($streamid);
            $server = $this->server_set->getObjectByID($stream->getServerlink());


            $entry = [];
            $entry[] = $stream->getId();
            $entry[] = '<a href="[[url_base]]stream/manage/' . $stream->getStream_uid() . '">'
            . $stream->getStream_uid() . '</a>';
            $entry[] = $server->getDomain();
            $entry[] = $stream->getPort();
            if ($stream->getNeedwork() == true) {
                $entry[] = "<span class=\"needwork\">Need work</span>";
            } elseif ($stream->getRentallink() == null) {
                $entry[] = "<span class=\"ready\">Available</span>";
            } elseif (in_array($stream->getRentallink(), $this->rental_set_ids) == false) {
                $entry[] = "Rented but cant find rental.";
            } else {
                $rental = $this->rental_set->getObjectByID($stream->getRentallink());
                $avatar = $avatar_set->getObjectByID($rental->getAvatarlink());
                $av_detail = explode(" ", $avatar->getAvatarname());
                $av_name = $avatar->getAvatarname();
                if ($av_detail[1] == "Resident") {
                    $av_name = $av_detail[0];
                }
                $entry[] = '<a class="sold" href="[[url_base]]client/manage/'
                . $rental->getRental_uid() . '">Sold -> ' . $av_name . '</a>';
            }
            $table_body[] = $entry;
        }
        $this->setSwapTag("page_content", render_datatable($table_head, $table_body));
    }
}
