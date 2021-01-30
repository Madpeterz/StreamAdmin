<?php

namespace App\MediaServer;

use App\MediaServer\Abstracts\PublicApi;
use App\R7\Set\StreamSet;

class Centova3 extends PublicApi
{
    /**
     * centovaProcessApiCall
     * @return mixed[] [status => bool, data=> jsonObject]
     */
    protected function centovaProcessApiCall(array $post_data, array $args): array
    {
        $post_data["f"] = "json";
        $post_data["a[password]"] = "" . $this->server->getApiUsername() . "|" . $this->server->getApiPassword() . "";
        foreach ($args as $key => $value) {
            $post_data["a[" . $key . "]"] = $value;
        }
        $reply = $this->restPost("", $post_data);
        if ($reply["status"] == true) {
            $this->last_api_message = "curl ok";
            return ["status" => true,"data" => json_decode($reply["message"], true)];
        } else {
            $this->last_api_message = "curl failed with message: " . $reply["message"] . "";
            return ["status" => false,"data" => []];
        }
    }
    /**
     * centovaServerclassApiCall
     * @return mixed[] [status => bool, data=> jsonObject]
     */
    protected function centovaServerclassApiCall(string $method, array $args = [], $post_data = []): array
    {
        $post_data["xm"] = "server." . $method . "";
        $post_data["a[username]"] = $this->stream->getAdminUsername();
        return $this->centovaProcessApiCall($post_data, $args);
    }
    /**
     * centovaSystemclassApiCall
     * @return mixed[] [status => bool, data=> jsonObject]
     */
    protected function centovaSystemclassApiCall(string $method, array $args = []): array
    {
        return $this->centovaProcessApiCall(["xm" => "system." . $method . ""], $args);
    }
    protected function simpleReplyOk(array $reply, bool $debug = false): bool
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
                                $this->last_api_message = "Reply from server: "
                                . $reply["data"]["response"]["message"] . "";
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
    protected function terminateAccount(string $old_username): bool
    {
        return $this->simpleReplyOk($this->centovaSystemclassApiCall("terminate", ["username" => $old_username]));
    }
    protected function createAccount(): bool
    {
        global $slconfig;
        if ($this->package->getApiTemplate() == null) {
            return false;
        }
        $post_data = [
            "port" => $this->stream->getPort(),
            "maxclients" => $this->package->getListeners(),
            "adminPassword" => $this->stream->getAdminPassword(),
            "sourcepassword" => $this->stream->getDjPassword(),
            "maxbitrate" => $this->package->getBitrate(),
            "username" => $this->stream->getAdminUsername(),
            "email" => $slconfig->getApiDefaultEmail(),
            "usesource" => 2,
            "autostart" => 1,
            "template" => $this->package->getApiTemplate(),
        ];
        if ($this->package->getAutodj() == true) {
            $post_data["autostart"] = 0;
            $post_data["usesource"] = 1;
            $post_data["diskquota"] = $this->package->getAutodjSize() * 1000;
        }
        $reply = $this->centovaSystemclassApiCall("provision", $post_data);
        if ($this->simpleReplyOk($reply) == false) {
            return false;
        }
        return $this->susspendServer();
    }
    protected function removeDJ(string $djaccount): bool
    {
        $reply = $this->centovaServerclassApiCall("managedj", ["action" => "terminate","djusername" => $djaccount]);
        return $this->simpleReplyOk($reply);
    }
    /**
     * djList
     * @return mixed[] [status => bool, list=> array]
     */
    public function djList(): array
    {
        $reply = $this->centovaServerclassApiCall("managedj", ["action" => "list"]);
        $status = false;
        $list = [];
        if ($this->simpleReplyOk($reply) == true) {
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
    /**
     * serverStatus
     * @return mixed[] [status => bool, loads=>[1,5,15], ram=>[free,max], streams=>[total,active], message=> string]
     */
    public function serverStatus(): array
    {
        $status = false;
        $loads = ["1" => 0,"5" => 0,"15" => 0];
        $ram = ["free" => 0,"max" => 0];
        $streams = ["total" => 0,"active" => 0];
        $message = "Unable to fetch status";
        $reply = $this->centovaSystemclassApiCall("version");
        if ($this->simpleReplyOk($reply) == true) {
            $server_status = $reply["data"]["response"]["data"]["web"];
            $status = true;
            $loads = [
                "1" => $server_status["other"]["Load (1m)"][1],
                "5" => $server_status["other"]["Load (5m)"][1],
                "15" => $server_status["other"]["Load (15m)"][1],
            ];
            $streams = ["total" => $server_status["accounts"],"active" => $server_status["activeaccounts"]];
            $ram = [
                "free" => floor($server_status["memfree"] / 1000),
                "max" => floor($server_status["memtotal"] / 1000),
            ];
            $message = "loaded";
        }
        return ["status" => $status,"loads" => $loads,"ram" => $ram,"streams" => $streams,"message" => $message];
    }
    /**
     * accountData
     * @return mixed[] [status => bool, data=>array]
     */
    protected function accountData(): array
    {
        $reply = $this->centovaServerclassApiCall("getaccount");
        if ($this->simpleReplyOk($reply) == true) {
            return ["status" => true,"data" => $reply["data"]["response"]["data"]["account"]];
        }
        return ["status" => false,"data" => []];
    }
    /**
     * accountState
     * states:
     *  true = enabled
     *  false = disabled
     * @return mixed[] [status => bool, state=>bool]
     */
    protected function accountState(): array
    {
        $reply = $this->accountData();
        $status = $reply["status"];
        $state = false;
        if ($status == true) {
            if ($reply["data"]["status"] != "disabled") {
                $state = true;
            }
        }
        return ["status" => $status,"state" => $state];
    }
    /**
     * streamState
     * @return mixed[] [status => bool, state=>bool,source=>bool, autodj=>bool]
     */
    public function streamState(): array
    {
        $this->last_api_message = "Unable to fetch stream state";
        $reply = $this->centovaServerclassApiCall("getstatus", ["mountpoints" => "all"]);
        $status = false;
        $server_status = false;
        $stream_connected = false;
        $auto_dj = false;
        if ($this->simpleReplyOk($reply) == false) {
            return ["status" => $status,"state" => $server_status,"source" => $stream_connected,"autodj" => $auto_dj];
        }
        $status = true;
        $server_status = $reply["data"]["response"]["data"]["status"];
        $this->last_api_message = "Server appears to be down";
        if ($server_status["serverstate"] == 0) {
            return ["status" => $status,"state" => $server_status,"source" => $stream_connected,"autodj" => $auto_dj];
        }
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
        return ["status" => $status,"state" => $server_status,"source" => $stream_connected,"autodj" => $auto_dj];
    }
    /**
     * accountNameList
     * @return mixed[] [status => bool, usernames=>array,passwords=>array]
     */
    public function accountNameList(bool $include_passwords = false, StreamSet $stream_set = null): array
    {
        $current_usernames = [];
        $current_passwords = [];
        $all_ok = true;
        if ($include_passwords == true) {
            if ($stream_set == null) {
                $stream_set = new StreamSet();
                $stream_set->loadByField("serverLink", $this->server->getId());
                if ($stream_set->getCount() == 0) {
                    $all_ok = false;
                    $this->last_api_message = "Unable to find streams attached to server";
                }
            }
        }
        if ($all_ok == true) {
            $reply = $this->centovaSystemclassApiCall("listaccounts", ["start" => 0,"limit" => 1000]);
            if ($this->simpleReplyOk($reply) == true) {
                $server_accounts = $reply["data"]["response"]["data"];
                foreach ($server_accounts as $entry) {
                    $current_usernames[] = $entry["username"];
                }
                if ($include_passwords == true) {
                    foreach ($stream_set->getAllIds() as $streamid) {
                        $stream = $stream_set->getObjectByID($streamid);
                        $reply = $this->centovaServerclassApiCall("getaccount");
                        if ($this->simpleReplyOk($reply) == true) {
                            $accountinfo = $reply["data"]["response"]["data"]["account"];
                            $current_passwords[$stream->getAdminUsername()] = [
                                "admin" => $accountinfo["adminPassword"],
                                "dj" => $accountinfo["sourcepassword"],
                            ];
                        }
                    }
                }
            }
        }
        return ["status" => $all_ok,"usernames" => $current_usernames,"passwords" => $current_passwords];
    }

    protected function syncUsername(string $old_username): bool
    {
        $reply = $this->centovaSystemclassApiCall(
            "rename",
            ["username" => $old_username,"newusername" => $this->stream->getAdminUsername()]
        );
        if ($this->simpleReplyOk($reply) == true) {
            return true;
        }
        return false;
    }
    protected function toggleAutodj(): bool
    {
        $reply = $this->centovaServerclassApiCall("getstatus", ["mountpoints" => "all"]);
        if ($this->simpleReplyOk($reply) == true) {
            $server_status = $reply["data"]["response"]["data"]["status"];
            if ($server_status["serverstate"] == 1) {
                // server up
                if ($server_status["sourcestate"] == 0) {
                    // Nothing connected start autoDJ
                    return $this->simpleReplyOk($this->centovaServerclassApiCall("switchsource", ["state" => "up"]));
                } else {
                    // somthing connected
                    $autodj_source_types = ["liquidsoap","icescc"];
                    if (in_array($server_status["sourcetype"], $autodj_source_types) == true) {
                        // autoDJ connected stop it
                        return $this->simpleReplyOk(
                            $this->centovaServerclassApiCall(
                                "switchsource",
                                ["state" => "down"]
                            )
                        );
                    } else {
                        $this->last_api_message = "DJ connected unable to start autoDJ";
                        return true;
                    }
                }
            } else {
                // server down
                return $this->startServer();
            }
        }
        return false;
    }
    protected function autodjNext(): bool
    {
        return $this->simpleReplyOk($this->centovaServerclassApiCall("nextsong"));
    }
    protected function stopServer(): bool
    {
        $streamstate = $this->streamState();
        if (($streamstate["status"] == true) && ($streamstate["state"] == true)) {
            return $this->simpleReplyOk($this->centovaServerclassApiCall("stop"));
        } else {
            $this->last_api_message = "Skipped server is already stopped";
            return true;
        }
    }
    protected function startServer(int $skip_auto_dj = 0): bool
    {
        $streamstate = $this->streamState();
        if (($streamstate["status"] == true) && ($streamstate["state"] == false)) {
            return $this->simpleReplyOk($this->centovaServerclassApiCall("start", ["noapps" => $skip_auto_dj]));
        } else {
            $this->last_api_message = "Skipped server is already up";
            return true;
        }
    }
    protected function susspendServer(): bool
    {
        return $this->simpleReplyOk(
            $this->centovaSystemclassApiCall(
                "setstatus",
                ["username" => $this->stream->getAdminUsername(),"status" => "disabled"]
            )
        );
    }
    protected function unSusspendServer(): bool
    {
        return $this->simpleReplyOk(
            $this->centovaSystemclassApiCall(
                "setstatus",
                ["username" => $this->stream->getAdminUsername(),"status" => "enabled"]
            )
        );
    }
    protected function changePassword(): bool
    {
        return $this->simpleReplyOk(
            $this->centovaServerclassApiCall(
                "reconfigure",
                [
                    "adminPassword" => $this->stream->getAdminPassword(),
                    "sourcepassword" => $this->stream->getDjPassword(),
                ]
            )
        );
    }
    protected function changeTitleNow(string $newtitle = "Not set"): bool
    {
        return $this->simpleReplyOk($this->centovaServerclassApiCall("reconfigure", ["title" => $newtitle]));
    }
}
