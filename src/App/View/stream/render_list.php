<?php

$table_head = ["id","UID","Server","Port","Status"];
$table_body = [];

$avatar_set = new avatar_set();
$avatar_set->loadIds($rental_set->getAllByField("avatarlink"));

foreach ($stream_set->getAllIds() as $streamid) {
    $stream = $stream_set->getObjectByID($streamid);
    $server = $server_set->getObjectByID($stream->getServerlink());


    $entry = [];
    $entry[] = $stream->getId();
    $entry[] = '<a href="[[url_base]]stream/manage/' . $stream->getStream_uid() . '">' . $stream->getStream_uid() . '</a>';
    $entry[] = $server->getDomain();
    $entry[] = $stream->getPort();
    if ($stream->getNeedwork() == false) {
        if ($stream->getRentallink() != null) {
            if (in_array($stream->getRentallink(), $rental_set_ids) == true) {
                $rental = $rental_set->getObjectByID($stream->getRentallink());
                $avatar = $avatar_set->getObjectByID($rental->getAvatarlink());
                $av_detail = explode(" ", $avatar->getAvatarname());
                $av_name = $avatar->getAvatarname();
                if ($av_detail[1] == "Resident") {
                    $av_name = $av_detail[0];
                }
                $entry[] = '<a class="sold" href="[[url_base]]client/manage/' . $rental->getRental_uid() . '">Sold -> ' . $av_name . '</a>';
            } else {
                $entry[] = "Rented but cant find rental.";
            }
        } else {
            $entry[] = "<span class=\"ready\">Available</span>";
        }
    } else {
        $entry[] = "<span class=\"needwork\">Need work</span>";
    }
    $table_body[] = $entry;
}
$this->output->setSwapTagString("page_content", render_datatable($table_head, $table_body));
