<?php
$view_reply->set_swap_tag_string("html_title","Search");
$view_reply->set_swap_tag_string("page_title","Search results [Not loaded]");
$view_reply->set_swap_tag_string("page_actions","");

$input = new inputFilter();
$search = $input->getFilter("search");
if(strlen($search) >= 3)
{
    $view_reply->set_swap_tag_string("page_title","Search results for: ".$search);
    $server_set = new server_set();
    $server_set->loadAll();

    // avatars
    $where_config = array(
        "fields"=>array("avataruuid","avatarname","avatar_uid"),
        "matches"=>array("% LIKE %","% LIKE %","% LIKE %"),
        "values"=>array($search,$search,$search),
        "types"=>array("s","s","s"),
        "join_with"=>array("OR","OR")
    );
    $search_avatar_set = new avatar_set();
    $search_avatar_set->load_with_config($where_config);

    // clients
    $where_config = array(
        "fields"=>array("rental_uid","message"),
        "matches"=>array("% LIKE %","% LIKE %"),
        "values"=>array($search,$search),
        "types"=>array("s","s"),
        "join_with"=>array("OR")
    );
    if($search_avatar_set->get_count() > 0)
    {
        $where_config["fields"][] = "avatarlink";
        $where_config["matches"][] = "IN";
        $where_config["values"][] = $search_avatar_set->get_unique_array("id");
        $where_config["types"][] = "i";
        $where_config["join_with"][] = "OR";
    }
    $search_rental_set = new rental_set();
    $search_rental_set->load_with_config($where_config);

    // streams
    $where_config = array(
        "fields"=>array("adminusername","port","stream_uid"),
        "matches"=>array("% LIKE %","LIKE","% LIKE %"),
        "values"=>array($search,$search,$search),
        "types"=>array("s","i","s"),
        "join_with"=>array("OR","OR")
    );
    if($search_rental_set->get_count() > 0)
    {
        $where_config["fields"][] = "id";
        $where_config["matches"][] = "IN";
        $where_config["values"][] = $search_rental_set->get_unique_array("streamlink");
        $where_config["types"][] = "i";
        $where_config["join_with"][] = "OR";
    }
    $search_stream_set = new stream_set();
    $search_stream_set->load_with_config($where_config);

    // servers
    $where_config = array(
        "fields"=>array("domain","controlpanel_url"),
        "matches"=>array("% LIKE %","% LIKE %"),
        "values"=>array($search,$search),
        "types"=>array("s","s"),
        "join_with"=>array("OR")
    );
    if($search_stream_set->get_count() > 0)
    {
        $where_config["fields"][] = "id";
        $where_config["matches"][] = "IN";
        $where_config["values"][] = $search_stream_set->get_unique_array("serverlink");
        $where_config["types"][] = "i";
        $where_config["join_with"][] = "OR";
    }
    $search_server_set = new server_set();
    $search_server_set->load_with_config($where_config);

    $search_rental_set_again = new rental_set();
    $entry = $search_stream_set->get_unique_array("rentallink");
    $seen = $search_rental_set->get_unique_array("id");
    $repeat_search_entrys = array();
    foreach($entry as $rentallink)
    {
        if(in_array($rentallink,$seen) == false)
        {
            $repeat_search_entrys[] = $rentallink;
            $seen[] = $rentallink;
        }
    }
    if(count($repeat_search_entrys) > 0)
    {
        $where_config = array(
            "fields"=>array("id"),
            "matches"=>array("IN"),
            "values"=>array($repeat_search_entrys),
            "types"=>array("i"),
        );
        $search_rental_set_again->load_with_config($where_config);
    }

    /*
        Clients
    */
    foreach($search_rental_set_again->get_all_ids() as $rental_id)
    {
        $rental = $search_rental_set_again->get_object_by_id($rental_id);
        $search_rental_set->add_to_collected($rental);
    }
    $avatar_set = new avatar_set();
    $avatar_set->load_ids($search_rental_set->get_all_by_field("avatarlink"));

    $stream_set = new stream_set();
    $stream_set->load_ids($search_rental_set->get_all_by_field("streamlink"));

    $package_set = new package_set();
    $package_set->load_ids($stream_set->get_all_by_field("packagelink"));
    $table_head = array("Rental UID","Avatar","Port","Notecard","Timeleft/Expired","Renewals");
    $table_body = array();
    foreach($search_rental_set->get_all_ids() as $rental_id)
    {
        $rental = $search_rental_set->get_object_by_id($rental_id);
        $avatar = $avatar_set->get_object_by_id($rental->get_avatarlink());
        $stream = $stream_set->get_object_by_id($rental->get_streamlink());
        $entry = array();
        $entry[] = '<a href="[[url_base]]client/manage/'.$rental->get_rental_uid().'">'.$rental->get_rental_uid().'</a>';
        $av_detail = explode(" ",$avatar->get_avatarname());
        if($av_detail[1] != "Resident") $entry[] = $avatar->get_avatarname();
        else $entry[] = $av_detail[0];
        $entry[] = $stream->get_port();
        $entry[] = "<button type=\"button\" class=\"btn btn-sm btn-outline-light\" data-toggle=\"modal\" data-target=\"#NotecardModal\" data-rentaluid=\"".$rental->get_rental_uid()."\">View</button>";
        if($rental->get_expireunixtime() > time())
        {
            $entry[] = "Active - ".timeleft_hours_and_days($rental->get_expireunixtime());
        }
        else
        {
            $entry[] = "Expired - ".expired_ago($rental->get_expireunixtime());
        }
        $entry[] = $rental->get_renewals();
        $table_body[] = $entry;
    }
    $pages["Clients [".$search_rental_set->get_count()."]"] = render_table($table_head,$table_body);
    /*
        Avatars
    */
    $table_head = array("UID","Name");
    $table_body = array();
    foreach($search_avatar_set->get_all_ids() as $avatar_id)
    {
        $avatar = $search_avatar_set->get_object_by_id($avatar_id);
        $entry = array();
        $entry[] = '<a href="[[url_base]]avatar/manage/'.$avatar->get_avatar_uid().'">'.$avatar->get_avatar_uid().'</a>';
        $entry[] = $avatar->get_avatarname();
        $table_body[] = $entry;
    }
    $pages["Avatars [".$search_avatar_set->get_count()."]"] = render_table($table_head,$table_body);
    /*
        Streams
    */
    $table_head = array("UID","Server","Port","Status");
    $table_body = array();


    $rental_set = new rental_set();
    $rental_set->load_ids($search_stream_set->get_all_by_field("rentallink"));
    $avatar_set = new avatar_set();
    $avatar_set->load_ids($rental_set->get_all_by_field("avatarlink"));
    $rental_set_ids = $rental_set->get_all_ids();

    foreach($search_stream_set->get_all_ids() as $streamid)
    {
        $stream = $search_stream_set->get_object_by_id($streamid);
        $server = $server_set->get_object_by_id($stream->get_serverlink());
        $entry = array();
        $entry[] = '<a href="[[url_base]]stream/manage/'.$stream->get_stream_uid().'">'.$stream->get_stream_uid().'</a>';
        $entry[] = $server->get_domain();
        $entry[] = $stream->get_port();
        if($stream->get_needwork() == false)
        {
            if($stream->get_rentallink() != null)
            {
                if(in_array($stream->get_rentallink(),$rental_set_ids) == true)
                {
                    $rental = $rental_set->get_object_by_id($stream->get_rentallink());
                    $avatar = $avatar_set->get_object_by_id($rental->get_avatarlink());
                    $av_detail = explode(" ",$avatar->get_avatarname());
                    $av_name = $avatar->get_avatarname();
                    if($av_detail[1] == "Resident") $av_name = $av_detail[0];
                    $entry[] = '<a class="sold" href="[[url_base]]client/manage/'.$rental->get_rental_uid().'">Sold -> '.$av_name.'</a>';
                }
                else $entry[] = "Rented but cant find rental.";
            }
            else $entry[] = "<span class=\"ready\">Ready</span>";
        }
        else $entry[] = "<span class=\"needwork\">Need work</span>";
        $table_body[] = $entry;
    }
    $pages["Streams [".$search_stream_set->get_count()."]"] = render_table($table_head,$table_body);
    /*
        servers
    */
    $table_head = array("Domain");
    $table_body = array();

    foreach($search_server_set->get_all_ids() as $server_id)
    {
        $server = $server_set->get_object_by_id($server_id);
        $entry = array();
        $entry[] = '<a href="[[url_base]]server/manage/'.$server->get_id().'">'.$server->get_domain().'</a>';
        $table_body[] = $entry;
    }
    $pages["Servers [".$search_server_set->get_count()."]"] = render_table($table_head,$table_body);

    $paged_info = new paged_info();
    $view_reply->set_swap_tag_string("page_content",$paged_info->render($pages));
}
else
{
    $view_reply->set_swap_tag_string("page_content","Sorry search requires 3 or more letters");
}
?>
