<?php

$table_head = array("id","UID","Server","Port","Status");
$table_body = [];

$avatar_set = new avatar_set();
$avatar_set->load_ids($rental_set->get_all_by_field("avatarlink"));

foreach ($stream_set->get_all_ids() as $streamid) {
    $stream = $stream_set->get_object_by_id($streamid);
    $server = $server_set->get_object_by_id($stream->get_serverlink());


    $entry = [];
    $entry[] = $stream->get_id();
    $entry[] = '<a href="[[url_base]]stream/manage/' . $stream->get_stream_uid() . '">' . $stream->get_stream_uid() . '</a>';
    $entry[] = $server->get_domain();
    $entry[] = $stream->get_port();
    if ($stream->get_needwork() == false) {
        if ($stream->get_rentallink() != null) {
            if (in_array($stream->get_rentallink(), $rental_set_ids) == true) {
                $rental = $rental_set->get_object_by_id($stream->get_rentallink());
                $avatar = $avatar_set->get_object_by_id($rental->get_avatarlink());
                $av_detail = explode(" ", $avatar->get_avatarname());
                $av_name = $avatar->get_avatarname();
                if ($av_detail[1] == "Resident") {
                    $av_name = $av_detail[0];
                }
                $entry[] = '<a class="sold" href="[[url_base]]client/manage/' . $rental->get_rental_uid() . '">Sold -> ' . $av_name . '</a>';
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
