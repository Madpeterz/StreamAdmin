<?php

class server_centova3_only extends server_public_api
{

    protected function process_centova_api_call(array $post_data, array $args): array
    {
        $post_data["f"] = "json";
        $post_data["a[password]"] = "" . $this->server->getApi_username() . "|" . $this->server->get_api_password() . "";
        foreach ($args as $key => $value) {
            $post_data["a[" . $key . "]"] = $value;
        }
        $reply = $this->rest_post("", $post_data);
        if ($reply["status"] == true) {
            $this->last_api_message = "curl ok";
            return ["status" => true,"data" => json_decode($reply["message"], true)];
        } else {
            $this->last_api_message = "curl failed with message: " . $reply["message"] . "";
            return ["status" => false,"data" => []];
        }
    }
    protected function centova_serverclass_api_call(string $method, array $args = [], $post_data = []): array
    {
        $post_data["xm"] = "server." . $method . "";
        $post_data["a[username]"] = $this->stream->getAdminusername();
        return $this->process_centova_api_call($post_data, $args);
    }
    protected function centova_systemclass_api_call(string $method, array $args = []): array
    {
        return $this->process_centova_api_call(["xm" => "system." . $method . ""], $args);
    }
    protected function simple_reply_ok(array $reply, bool $debug = false): bool
    {
        if ($debug == true) {
            print_r($reply);
        }
        if (array_key_exists("status", $reply) == true) {
            if ($reply["status"] == true) {
                $this->last_api_message = "Curl ok but badly formated reply";
                if (array_key_exists("data", $reply) == true) {
                    if (is_array($reply["data"]) == true) {
                        $this->last_api_message = "Curl connected ok but invaild response from server";
                        if (array_key_exists("response", $reply["data"]) == true) {
                            if (array_key_exists("message", $reply["data"]["response"]) == true) {
                                $this->last_api_message = "Reply from server: " . $reply["data"]["response"]["message"] . "";
                            }
                        }
                        if (array_key_exists("type", $reply["data"]) == true) {
                            if ($reply["data"]["type"] == "success") {
                                return true;
                            }
                        }
                    }
                }
            }
        }
        return false;
    }
}
class server_centova3 extends server_centova3_only
{
    protected function terminate_account(string $old_username): bool
    {
        return $this->simple_reply_ok($this->centova_systemclass_api_call("terminate", ["username" => $old_username]));
    }
    protected function create_account(): bool
    {
        global $slconfig;
        if ($this->package->getApi_template() != null) {
            $servertype = new servertypes();
            if ($servertype->loadID($this->package->getServertypelink()) == true) {
                $post_data = [
                    "port" => $this->stream->getPort(),
                    "maxclients" => $this->package->getListeners(),
                    "adminpassword" => $this->stream->getAdminpassword(),
                    "sourcepassword" => $this->stream->getDjpassword(),
                    "maxbitrate" => $this->package->getBitrate(),
                    "username" => $this->stream->getAdminusername(),
                    "email" => $slconfig->get_api_default_email(),
                    "usesource" => 2,
                    "autostart" => 1,
                    "template" => $this->package->getApi_template(),
                ];
                /*
                if($servertype->getId() == 1)
                {
                    $post_data["servertype"] = "ShoutCast";
                }
                else if($servertype->getId() == 2)
                {
                    $post_data["servertype"] = "ShoutCast2";
                }
                else if($servertype->getId() == 3)
                {
                    $post_data["servertype"] = "IceCast";
                }
                */
                if ($this->package->getAutodj() == true) {
                    $post_data["autostart"] = 0;
                    $post_data["usesource"] = 1;
                    $post_data["diskquota"] = $this->package->getAutodj_size() * 1000;
                }
                $reply = $this->centova_systemclass_api_call("provision", $post_data);
                if ($this->simple_reply_ok($reply) == true) {
                    return $this->susspend_server();
                }
            } else {
                $this->last_api_message = "Unable to find servertype linked to package";
            }
        } else {
            $this->last_api_message = "Package does not have a template!";
        }
        return false;
    }
    protected function remove_dj(string $djaccount): bool
    {
        $reply = $this->centova_serverclass_api_call("managedj", ["action" => "terminate","djusername" => $djaccount]);
        return $this->simple_reply_ok($reply);
    }
    protected function dj_list(): array
    {
        $reply = $this->centova_serverclass_api_call("managedj", ["action" => "list"]);
        $status = false;
        $list = [];
        if ($this->simple_reply_ok($reply) == true) {
            $status = true;
            $djlist_data = $reply["data"]["response"]["data"];
            if (is_array($djlist_data) == true) {
                foreach ($djlist_data as $djentry) {
                    $list[] = $djentry["djusername"];
                }
            }
        } else {
            // Handle broken API [v3.2.12]
            if (array_key_exists("data", $reply) == true) {
                $reply = $reply["data"];
                if (array_key_exists("response", $reply) == true) {
                    $reply = $reply["response"];
                    if (array_key_exists("message", $reply) == true) {
                        $reply = $reply["message"];
                        if (strpos($reply, "Invalid argument supplied for foreach()") !== false) {
                            $status = true;
                            $this->last_api_message = "No DJ accounts";
                        }
                    }
                }
            }
        }
        return ["status" => $status,"list" => $list];
    }
    protected function server_status(): array
    {
        $status = false;
        $loads = ["1" => 0,"5" => 0,"15" => 0];
        $ram = ["free" => 0,"max" => 0];
        $streams = ["total" => 0,"active" => 0];
        $message = "Unable to fetch status";
        $reply = $this->centova_systemclass_api_call("version");
        if ($this->simple_reply_ok($reply) == true) {
            $server_status = $reply["data"]["response"]["data"]["web"];
            $status = true;
            $loads = ["1" => $server_status["other"]["Load (1m)"][1],"5" => $server_status["other"]["Load (5m)"][1],"15" => $server_status["other"]["Load (15m)"][1]];
            $streams = ["total" => $server_status["accounts"],"active" => $server_status["activeaccounts"]];
            $ram = ["free" => floor($server_status["memfree"] / 1000),"max" => floor($server_status["memtotal"] / 1000)];
            $message = "loaded";
        }
        return ["status" => $status,"loads" => $loads,"ram" => $ram,"streams" => $streams,"message" => $message];
    }
    protected function account_data()
    {
        $reply = $this->centova_serverclass_api_call("getaccount");
        if ($this->simple_reply_ok($reply) == true) {
            return ["status" => true,"data" => $reply["data"]["response"]["data"]["account"]];
        }
        return ["status" => false,"data" => []];
    }
    protected function account_state(): array
    {
        $reply = $this->account_data();
        $status = $reply["status"];
        $state = false;
        if ($status == true) {
            if ($reply["data"]["status"] != "disabled") {
                $state = true;
            }
        }
        return ["status" => $status,"state" => $state];
    }
    protected function stream_state(): array
    {
        $this->last_api_message = "Unable to fetch stream state";
        $reply = $this->centova_serverclass_api_call("getstatus", ["mountpoints" => "all"]);
        $status = false;
        $server_status = false;
        $stream_connected = false;
        $auto_dj = false;
        if ($this->simple_reply_ok($reply) == true) {
            $status = true;
            $server_status = $reply["data"]["response"]["data"]["status"];
            $this->last_api_message = "Server appears to be down";
            if ($server_status["serverstate"] == 1) {
                // server up
                $server_status = true;
                $this->last_api_message = "Source/AutoDJ appears to be down";
                if ($server_status["sourcestate"] == 1) {
                    $stream_connected = true;
                    $this->last_api_message = "Stream open";
                    $autodj_source_types = ["liquidsoap","icescc"];
                    if (in_array($server_status["sourcetype"], $autodj_source_types) == true) {
                        $this->last_api_message = "DJ connected";
                        $auto_dj = true;
                        $stream_connected = false;
                    }
                }
            }
        }
        return ["status" => $status,"state" => $server_status,"source" => $stream_connected,"autodj" => $auto_dj];
    }
    protected function account_name_list(bool $include_passwords = false, stream_set $stream_set = null): array
    {
        $current_usernames = [];
        $current_passwords = [];
        $all_ok = true;
        if ($include_passwords == true) {
            if ($stream_set == null) {
                $stream_set = new stream_set();
                $stream_set->loadByField("serverlink", $this->server->getId());
                if ($stream_set->getCount() == 0) {
                    $all_ok = false;
                    $this->last_api_message = "Unable to find streams attached to server";
                }
            }
        }
        if ($all_ok == true) {
            $reply = $this->centova_systemclass_api_call("listaccounts", ["start" => 0,"limit" => 1000]);
            if ($this->simple_reply_ok($reply) == true) {
                $server_accounts = $reply["data"]["response"]["data"];
                foreach ($server_accounts as $entry) {
                    $current_usernames[] = $entry["username"];
                }
                if ($include_passwords == true) {
                    foreach ($stream_set->getAllIds() as $streamid) {
                        $stream = $stream_set->getObjectByID($streamid);
                        $reply = $this->centova_serverclass_api_call("getaccount");
                        if ($this->simple_reply_ok($reply) == true) {
                            $accountinfo = $reply["data"]["response"]["data"]["account"];
                            $current_passwords[$stream->getAdminusername()] = ["admin" => $accountinfo["adminpassword"],"dj" => $accountinfo["sourcepassword"]];
                        }
                    }
                }
            }
        }
        return ["status" => $all_ok,"usernames" => $current_usernames,"passwords" => $current_passwords];
    }

    protected function sync_username(string $old_username): bool
    {
        $reply = $this->centova_systemclass_api_call("rename", ["username" => $old_username,"newusername" => $this->stream->getAdminusername()]);
        if ($this->simple_reply_ok($reply) == true) {
            return true;
        }
        return false;
    }
    protected function toggle_autodj(): bool
    {
        $reply = $this->centova_serverclass_api_call("getstatus", ["mountpoints" => "all"]);
        if ($this->simple_reply_ok($reply) == true) {
            $server_status = $reply["data"]["response"]["data"]["status"];
            if ($server_status["serverstate"] == 1) {
                // server up
                if ($server_status["sourcestate"] == 0) {
                    // Nothing connected start autoDJ
                    return $this->simple_reply_ok($this->centova_serverclass_api_call("switchsource", ["state" => "up"]));
                } else {
                    // somthing connected
                    $autodj_source_types = ["liquidsoap","icescc"];
                    if (in_array($server_status["sourcetype"], $autodj_source_types) == true) {
                        // autoDJ connected stop it
                        return $this->simple_reply_ok($this->centova_serverclass_api_call("switchsource", ["state" => "down"]));
                    } else {
                        $this->last_api_message = "DJ connected unable to start autoDJ";
                        return true;
                    }
                }
            } else {
                // server down
                return $this->start_server();
            }
        }
        return false;
    }
    protected function autodj_next(): bool
    {
        return $this->simple_reply_ok($this->centova_serverclass_api_call("nextsong"));
    }
    protected function stop_server(): bool
    {
        $streamstate = $this->stream_state();
        if (($streamstate["status"] == true) && ($streamstate["state"] == true)) {
            return $this->simple_reply_ok($this->centova_serverclass_api_call("stop"));
        } else {
            $this->last_api_message = "Skipped server is already stopped";
            return true;
        }
    }
    protected function start_server(int $skip_auto_dj = 0): bool
    {
        $streamstate = $this->stream_state();
        if (($streamstate["status"] == true) && ($streamstate["state"] == false)) {
            return $this->simple_reply_ok($this->centova_serverclass_api_call("start", ["noapps" => $skip_auto_dj]));
        } else {
            $this->last_api_message = "Skipped server is already up";
            return true;
        }
    }
    protected function susspend_server(): bool
    {
        return $this->simple_reply_ok($this->centova_systemclass_api_call("setstatus", ["username" => $this->stream->getAdminusername(),"status" => "disabled"]));
    }
    protected function un_susspend_server(): bool
    {
        return $this->simple_reply_ok($this->centova_systemclass_api_call("setstatus", ["username" => $this->stream->getAdminusername(),"status" => "enabled"]));
    }
    protected function change_password(): bool
    {
        return $this->simple_reply_ok($this->centova_serverclass_api_call("reconfigure", ["adminpassword" => $this->stream->getAdminpassword(),"sourcepassword" => $this->stream->getDjpassword()]));
    }
    protected function change_title_now(string $newtitle = "Not set"): bool
    {
        return $this->simple_reply_ok($this->centova_serverclass_api_call("reconfigure", ["title" => $newtitle]));
    }
}
