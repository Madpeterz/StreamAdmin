<?php

$status = false;
if ($owner_override == true) {
    $order_config = ["ordering_enabled" => true,"order_field" => "last_attempt","order_dir" => "DESC"];
    $limits_config = ["page_number" => 0,"max_entrys" => 1];
    $api_requests_set = new api_requests_set();
    $message = "not set";
    if ($api_requests_set->loadWithConfig(null, $order_config, $limits_config)["status"] == true) {
        if ($api_requests_set->getCount() > 0) {
            $api_request = $api_requests_set->get_first();
            $load_path = "endpoints/api/apirequests/" . $api_request->get_eventname() . ".php";
            $api_request->set_attempts($api_request->get_attempts() + 1);
            $api_request->set_last_attempt(time());
            $api_request->set_message("started processing");
            $save_status = $api_request->updateEntry();
            if ($save_status["status"] == true) {
                if (file_exists($load_path) == true) {
                    if ($sql->sqlSave(false) == true) {
                        include $load_path;
                    } else {
                        $message = "Unable to mark event as processing DB issue";
                    }
                } else {
                    $soft_fail = true;
                    $message = "Unable to find event: " . $api_request->get_eventname();
                }
            } else {
                $message = "Unable to mark event as processing Obj issue";
            }
            if ($soft_fail == true) {
                $api_request->set_message($message);
                $save_status = $api_request->updateEntry();
                if ($save_status["status"] == false) {
                    $soft_fail = false;
                    $message = "Failed to update api request attempt details";
                }
            }
        } else {
            $status = true;
            $message = "nowork";
        }
    } else {
        $message = "Unable to load next api request";
    }
    echo $message;
} else {
    echo "This API is owner only";
}
