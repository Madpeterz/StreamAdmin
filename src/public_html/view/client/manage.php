<?php

$view_reply->add_swap_tag_string("html_title", "~ Manage");
$view_reply->add_swap_tag_string("page_title", "Editing client");
$view_reply->set_swap_tag_string("page_actions", "<a href='[[url_base]]client/revoke/" . $page . "'><button type='button' class='btn btn-danger'>Revoke</button></a>");


$rental = new rental();
if ($rental->load_by_field("rental_uid", $page) == true) {
    $pages = [];
    $avatar = new avatar();
    $avatar->load($rental->get_avatarlink());
    $view_reply->add_swap_tag_string("page_title", ": " . $rental->get_rental_uid() . " [" . $avatar->get_avatarname() . "]");
    $form = new form();
    $form->target("client/update/" . $page . "");
    $form->required(true);
    $form->col(6);
        $form->group("Timeleft: " . timeleft_hours_and_days($rental->get_expireunixtime()) . "");
        $form->direct_add("<sub>" . date('l jS \of F Y h:i:s A', $rental->get_expireunixtime()) . "</sub><br/><br/>");
        $form->number_input("adjustment_days", "Adjustment [Days]", 0, 3, "Max 999");
        $form->number_input("adjustment_hours", "Adjustment [Hours]", 0, 2, "Max 23");
        $form->select("adjustment_dir", "Adjustment (Type)", false, array(false => "Remove",true => "Add"));
    $form->col(6);
        $form->group("Transfer");
        $form->text_input("transfer_avataruid", "Avatar UID <a data-toggle=\"modal\" data-target=\"#AvatarPicker\" href=\"#\" target=\"_blank\">Find</a>", 8, "", "Avatar UID (Not SL UUID)");
    $form->split();
    $form->col(6);
        $form->group("Message");
        $form->textarea("message", "Message", 9999, $rental->get_message(), "Any rental with a message will not be listed on the Fast removal system! Max length 9999");
    $pages["Config"] = $form->render("Update", "primary");

    $where_config = array(
        "fields" => array("streamlink","unixtime"),
        "values" => array($rental->get_streamlink(),$rental->get_startunixtime()),
        "types" => array("i","i"),
        "matches" => array("=",">="),
    );
    $order_by = array("ordering_enabled" => true,"order_field" => "unixtime","order_dir" => "DESC");
    $transactions_set = new transactions_set();
    $transactions_set->load_with_config($where_config, $order_by);

    $reseller_set = new reseller_set();
    $region_set = new region_set();
    $avatar_set = new avatar_set();
    $region_set->load_ids($transactions_set->get_all_by_field("regionlink"));
    $reseller_set->load_ids($transactions_set->get_all_by_field("resellerlink"));
    $avatar_set->load_ids(array_merge($transactions_set->get_all_by_field("avatarlink"), $reseller_set->get_all_by_field("avatarlink")), "id", "i", false);

    $table_head = array("id","Transaction UID","Avatar","Reseller","Region","Amount","Datetime");
    $table_body = [];
    foreach ($transactions_set->get_all_ids() as $transaction_id) {
        $transaction = $transactions_set->get_object_by_id($transaction_id);
        $avatar = $avatar_set->get_object_by_id($transaction->get_avatarlink());
        $region = $region_set->get_object_by_id($transaction->get_regionlink());
        $reseller = $reseller_set->get_object_by_id($transaction->get_resellerlink());
        $reseller_av = $avatar_set->get_object_by_id($reseller->get_avatarlink());
        $entry = [];
        $entry[] = $transaction->get_id();
        $entry[] = $transaction->get_transaction_uid();
        $entry[] = $avatar->get_avatarname();
        $entry[] = $reseller_av->get_avatarname();
        $entry[] = $region->get_name();
        $entry[] = $transaction->get_amount();
        $entry[]  = date('l jS \of F Y h:i:s A', $transaction->get_unixtime());
        $table_body[] = $entry;
    }

    $stream = new stream();
    if ($stream->load($rental->get_streamlink()) == true) {
        $server = new server();
        if ($server->load($stream->get_serverlink()) == true) {
            if ($server->get_apilink() > 1) {
                $package = new package();
                if ($package->load($stream->get_packagelink()) == true) {
                    $serverapi_helper = new serverapi_helper();
                    $serverapi_helper->force_set_rental($rental);
                    $serverapi_helper->force_set_server($server);
                    $serverapi_helper->force_set_package($package);
                    $serverapi_helper->force_set_stream($stream, false);
                    $mygrid = new grid();

                    $api_actions = array(
                        "stop" => "danger",
                        "start" => "success",
                        "autodj_next" => "info",
                        "autodj_toggle" => "secondary",
                        "customize_username" => "warning",
                        "reset_passwords" => "warning",
                        "enable_account" => "success",
                        "disable_account" => "danger",
                        "list_djs" => "info",
                        "purge_djs" => "danger",
                    );
                    foreach ($api_actions as $key => $value) {
                        if ($serverapi_helper->callable_action("api_" . $key) == true) {
                            $form = new form();
                            $form->target("client/api/" . $page . "/" . $key);
                            $buttontext = str_replace("_", " ", ucfirst($key));
                            $mygrid->add_content($form->render($buttontext, $value, true), 4);
                        }
                    }
                    $mygrid->add_content("<hr/>", 12);
                    if ($serverapi_helper->callable_action("api_set_passwords") == true) {
                        $form = new form();
                        $form->target("client/api/" . $page . "/set_passwords");
                        $form->group("API force set passwords");
                        $form->text_input("set_dj_password", "Set DJ password", 6, $stream->get_djpassword(), "DJ/Stream password");
                        $form->text_input("set_admin_password", "Set Admin password", 6, $stream->get_adminpassword(), "Admin password");
                        $mygrid->add_content($form->render("Set passwords", "warning", true), 6);
                    }
                    $pages["API"] = $mygrid->get_output();
                    $avname = explode(" ", strtolower($avatar->get_avatarname()));
                    $syncname = "" . $avname[0] . "_" . $package->get_bitrate() . "_" . $stream->get_port() . "";
                    if ($serverapi_helper->callable_action("api_customize_username") == true) {
                        $pages["API"] .= "<br/>customize username changes the admin username for the stream following this ruleset<br/><ol>
                        <li>Firstname eg:\"" . $avname[0] . "\"</li>
                        <li>Firstname 2 letters of last name:\"" . $avname[0] . "_" . substr($avname[1], 0, 2) . "\"</li>
                        <li>Firstname Port: \"" . $avname[0] . "_" . $stream->get_port() . "\"</li>
                        <li>Firstname Port Bitrate: \"" . $avname[0] . "_" . $stream->get_port() . "_" . $package->get_bitrate() . "\"</li>
                        <li>Firstname Port ServerID: \"" . $avname[0] . "_" . $stream->get_port() . "_" . $server->get_id() . "\"</li>
                        <li>Firstname RentalUID: \"" . $avname[0] . "_" . $rental->get_rental_uid() . "\"</li>
                        </ol>";
                    }
                }
            }
        }
    }
    $paged_info = new paged_info();
    $view_reply->set_swap_tag_string("page_content", $paged_info->render($pages));
    $view_reply->add_swap_tag_string("page_content", "<br/><h4>Transactions</h4>");
    $view_reply->add_swap_tag_string("page_content", render_datatable($table_head, $table_body));
} else {
    $view_reply->redirect("client?bubblemessage=unable to find client&bubbletype=warning");
}
