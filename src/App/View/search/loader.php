<?php

$this->output->setSwapTagString("html_title", "Search");
$this->output->setSwapTagString("page_title", "Search results [Not loaded]");
$this->output->setSwapTagString("page_actions", "");

$input = new inputFilter();
$search = $input->getFilter("search");
if (strlen($search) >= 3) {
    $this->output->setSwapTagString("page_title", "Search results for: " . $search);
    $server_set = new server_set();
    $server_set->loadAll();

    // avatars
    $where_config = [
        "fields" => ["avataruuid","avatarname","avatar_uid"],
        "matches" => ["% LIKE %","% LIKE %","% LIKE %"],
        "values" => [$search,$search,$search],
        "types" => ["s","s","s"],
        "join_with" => ["OR","OR"],
    ];
    $search_avatar_set = new avatar_set();
    $search_avatar_set->loadWithConfig($where_config);

    // clients
    $where_config = [
        "fields" => ["rental_uid","message"],
        "matches" => ["% LIKE %","% LIKE %"],
        "values" => [$search,$search],
        "types" => ["s","s"],
        "join_with" => ["OR"],
    ];
    if ($search_avatar_set->getCount() > 0) {
        $where_config["fields"][] = "avatarlink";
        $where_config["matches"][] = "IN";
        $where_config["values"][] = $search_avatar_set->get_unique_array("id");
        $where_config["types"][] = "i";
        $where_config["join_with"][] = "OR";
    }
    $search_rental_set = new rental_set();
    $search_rental_set->loadWithConfig($where_config);

    // streams
    $where_config = [
        "fields" => ["adminusername","port","stream_uid"],
        "matches" => ["% LIKE %","LIKE","% LIKE %"],
        "values" => [$search,$search,$search],
        "types" => ["s","i","s"],
        "join_with" => ["OR","OR"],
    ];
    if ($search_rental_set->getCount() > 0) {
        $where_config["fields"][] = "id";
        $where_config["matches"][] = "IN";
        $where_config["values"][] = $search_rental_set->get_unique_array("streamlink");
        $where_config["types"][] = "i";
        $where_config["join_with"][] = "OR";
    }
    $search_stream_set = new stream_set();
    $search_stream_set->loadWithConfig($where_config);

    // servers
    $where_config = [
        "fields" => ["domain","controlpanel_url"],
        "matches" => ["% LIKE %","% LIKE %"],
        "values" => [$search,$search],
        "types" => ["s","s"],
        "join_with" => ["OR"],
    ];
    if ($search_stream_set->getCount() > 0) {
        $where_config["fields"][] = "id";
        $where_config["matches"][] = "IN";
        $where_config["values"][] = $search_stream_set->get_unique_array("serverlink");
        $where_config["types"][] = "i";
        $where_config["join_with"][] = "OR";
    }
    $search_server_set = new server_set();
    $search_server_set->loadWithConfig($where_config);

    $search_rental_set_again = new rental_set();
    $entry = $search_stream_set->get_unique_array("rentallink");
    $seen = $search_rental_set->get_unique_array("id");
    $repeat_search_entrys = [];
    foreach ($entry as $rentallink) {
        if (in_array($rentallink, $seen) == false) {
            $repeat_search_entrys[] = $rentallink;
            $seen[] = $rentallink;
        }
    }
    if (count($repeat_search_entrys) > 0) {
        $where_config = [
            "fields" => ["id"],
            "matches" => ["IN"],
            "values" => [$repeat_search_entrys],
            "types" => ["i"],
        ];
        $search_rental_set_again->loadWithConfig($where_config);
    }

    /*
        Clients
    */
    foreach ($search_rental_set_again->getAllIds() as $rental_id) {
        $rental = $search_rental_set_again->getObjectByID($rental_id);
        $search_rental_set->add_to_collected($rental);
    }
    $avatar_set = new avatar_set();
    $avatar_set->loadIds($search_rental_set->getAllByField("avatarlink"));

    $stream_set = new stream_set();
    $stream_set->loadIds($search_rental_set->getAllByField("streamlink"));

    $package_set = new package_set();
    $package_set->loadIds($stream_set->getAllByField("packagelink"));
    $table_head = ["Rental UID","Avatar","Port","Notecard","Timeleft/Expired","Renewals"];
    $table_body = [];
    foreach ($search_rental_set->getAllIds() as $rental_id) {
        $rental = $search_rental_set->getObjectByID($rental_id);
        $avatar = $avatar_set->getObjectByID($rental->getAvatarlink());
        $stream = $stream_set->getObjectByID($rental->get_streamlink());
        $entry = [];
        $entry[] = '<a href="[[url_base]]client/manage/' . $rental->getRental_uid() . '">' . $rental->getRental_uid() . '</a>';
        $av_detail = explode(" ", $avatar->getAvatarname());
        if ($av_detail[1] != "Resident") {
            $entry[] = $avatar->getAvatarname();
        } else {
            $entry[] = $av_detail[0];
        }
        $entry[] = $stream->get_port();
        $entry[] = "<button type=\"button\" class=\"btn btn-sm btn-outline-light\" data-toggle=\"modal\" data-target=\"#NotecardModal\" data-rentaluid=\"" . $rental->getRental_uid() . "\">View</button>";
        if ($rental->get_expireunixtime() > time()) {
            $entry[] = "Active - " . timeleft_hours_and_days($rental->get_expireunixtime());
        } else {
            $entry[] = "Expired - " . expired_ago($rental->get_expireunixtime());
        }
        $entry[] = $rental->get_renewals();
        $table_body[] = $entry;
    }
    $pages["Clients [" . $search_rental_set->getCount() . "]"] = render_table($table_head, $table_body);
    /*
        Avatars
    */
    $table_head = ["UID","Name"];
    $table_body = [];
    foreach ($search_avatar_set->getAllIds() as $avatar_id) {
        $avatar = $search_avatar_set->getObjectByID($avatar_id);
        $entry = [];
        $entry[] = '<a href="[[url_base]]avatar/manage/' . $avatar->get_avatar_uid() . '">' . $avatar->get_avatar_uid() . '</a>';
        $entry[] = $avatar->getAvatarname();
        $table_body[] = $entry;
    }
    $pages["Avatars [" . $search_avatar_set->getCount() . "]"] = render_table($table_head, $table_body);
    /*
        Streams
    */
    $table_head = ["UID","Server","Port","Status"];
    $table_body = [];


    $rental_set = new rental_set();
    $rental_set->loadIds($search_stream_set->getAllByField("rentallink"));
    $avatar_set = new avatar_set();
    $avatar_set->loadIds($rental_set->getAllByField("avatarlink"));
    $rental_set_ids = $rental_set->getAllIds();

    foreach ($search_stream_set->getAllIds() as $streamid) {
        $stream = $search_stream_set->getObjectByID($streamid);
        $server = $server_set->getObjectByID($stream->get_serverlink());
        $entry = [];
        $entry[] = '<a href="[[url_base]]stream/manage/' . $stream->get_stream_uid() . '">' . $stream->get_stream_uid() . '</a>';
        $entry[] = $server->get_domain();
        $entry[] = $stream->get_port();
        if ($stream->get_needwork() == false) {
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
                $entry[] = "<span class=\"ready\">Ready</span>";
            }
        } else {
            $entry[] = "<span class=\"needwork\">Need work</span>";
        }
        $table_body[] = $entry;
    }
    $pages["Streams [" . $search_stream_set->getCount() . "]"] = render_table($table_head, $table_body);
    /*
        servers
    */
    $table_head = ["Domain"];
    $table_body = [];

    foreach ($search_server_set->getAllIds() as $server_id) {
        $server = $server_set->getObjectByID($server_id);
        $entry = [];
        $entry[] = '<a href="[[url_base]]server/manage/' . $server->getId() . '">' . $server->get_domain() . '</a>';
        $table_body[] = $entry;
    }
    $pages["Servers [" . $search_server_set->getCount() . "]"] = render_table($table_head, $table_body);

    $paged_info = new paged_info();
    $this->output->setSwapTagString("page_content", $paged_info->render($pages));
} else {
    $this->output->setSwapTagString("page_content", "Sorry search requires 3 or more letters");
}
