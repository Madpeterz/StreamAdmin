<?php

namespace App\MediaServer;

use App\MediaServer\Abstracts\PublicApi;
use App\R7\Set\StreamSet;

class Azurecast extends PublicApi
{
    // $this->last_api_message
    protected $station_djs_map = null;
    protected function getClientAuth(): void
    {
        $this->options['headers']['Authorization'] = 'Bearer ' . $this->server->getApiPassword();
    }
    protected function apiUserId(): string
    {
        return $this->stream->getApiConfigValue3();
    }

    protected function apiRoleId(): string
    {
        return $this->stream->getApiConfigValue2();
    }

    protected function apiStationId(): string
    {
        return $this->stream->getApiConfigValue1();
    }
    /**
     * getPostFormated
     * @return mixed[] [json =>  postdata]
     */
    protected function getPostFormated(array $postdata = []): array
    {
        return ['json' => $postdata];
    }
    protected function terminateAccount(string $not_used): bool
    {
        $this->last_api_message = "Skipped terminateAccount not supported on this api";
        return false;
    }
    protected function createAccount(): bool
    {
        $this->last_api_message = "Skipped createAccount not supported on this api";
        return false;
    }
    protected function removeDJ(string $djaccount): bool
    {
        $has_dj_list = true;
        if ($this->station_djs_map == null) {
            $reply = $this->djList();
            $has_dj_list = $reply["status"];
        }
        if ($has_dj_list == false) {
            $this->last_api_message = "unable to get list of djs";
            return false;
        }
        $this->last_api_message = "Unable to find DJ account";
        foreach ($this->station_djs_map as $key => $value) {
            if ($value != $djaccount) {
                continue;
            }
            $reply = $this->restDelete("station/" . $this->apiStationId() . "/streamer/" . $key);
            if ($reply["status"] == false) {
                $this->last_api_message = "unable to remove DJ";
                return false;
            }
            $json = json_decode($reply["message"]);
            if ($json->success == false) {
                $this->last_api_message = "Failed to remove DJ account";
                return false;
            }
            $this->last_api_message = "DJ removed";
            break;
        }
        return true;
    }
    /**
     * djList
     * @return mixed[] [status => bool, list=> array]
     */
    public function djList(): array
    {
        $reply = $this->restGet("station/" . $this->apiStationId() . "/streamers");
        if ($reply["status"] == false) {
            $this->last_api_message = "unable to get list of djs";
            return ["status" => false,"list" => []];
        }
        $this->last_api_message = "fetched DJ list";
        $json = json_decode($reply["message"]);
        $this->station_djs_map = [];
        foreach ($json as $index => $entry) {
            $this->station_djs_map[$entry->id] = $entry->streamer_username;
        }
        return ["status" => true,"list" => array_values($this->station_djs_map)];
    }
    /**
     * serverStatus
     * @return mixed[] [status => bool, loads=>[1,5,15], ram=>[free,max], streams=>[total,active], message=> string]
     */
    public function serverStatus(): array
    {
        $reply = $this->restGet("status");
        $this->last_api_message = $reply;
        if ($reply["status"] == true) {
            $json = json_decode($reply["message"]);
            return [
                "status" => $json->online,
                "loads" => ["1" => 0,"5" => 0,"15" => 0],
                "ram" => ["free" => 0,"max" => 0],
                "streams" => ["total" => 0,"active" => 0],
                "message" => "Limited reply",
            ];
        }
        return [
            "status" => false,
            "loads" => ["1" => 0,"5" => 0,"15" => 0],
            "ram" => ["free" => 0,"max" => 0],
            "streams" => ["total" => 0,"active" => 0],
            "message" => "Limited reply",
        ];
    }
    /**
     * accountState
     * states:
     *  true = enabled
     *  false = disabled
     * @return mixed[] [status => bool, state=>bool, "message" => ""]
     */
    protected function accountState(): array
    {
        $status = false;
        $state = false;
        $this->last_api_message = "fetching account state";
        $reply = $this->restGet("admin/user/" . $this->apiUserId() . "");
        $status = $reply["status"];
        if ($status == true) {
            $this->last_api_message = "got account state";
            $json = json_decode($reply["message"]);
            if (count($json->roles) >= 1) {
                $state = true;
            }
        }
        return ["status" => $status,"state" => $state, "message" => $reply["message"]];
    }
    /**
     * streamState
     * @return mixed[] [status => bool, state=>bool,source=>bool, autodj=>bool, message => string]
     */
    public function streamState(): array
    {
        $this->last_api_message = "unable to get station status";
        $reply = $this->restGet("station/" . $this->apiStationId() . "/status");
        $state = false;
        $source = false;
        $autodj = false;
        if ($reply["status"] == true) {
            $this->last_api_message = "stream appears to be down";
            $json = json_decode($reply["message"]);
            if ($json->frontend_running == true) {
                $this->last_api_message = "stream up";
                $state = true;
                $source = true;
                if ($json->backend_running == true) {
                    $this->last_api_message = "stream up/auto dj up";
                    $autodj = true;
                }
            }
        }
        return [
            "status" => $reply["status"],
            "state" => $state,
            "source" => $source,
            "autodj" => $autodj,
            "message" => $this->last_api_message,
        ];
    }
    /**
     * accountNameList
     * @return mixed[] [status => bool, usernames=>array,passwords=>array]
     */
    public function accountNameList(bool $include_passwords = false, StreamSet $stream_set = null): array
    {
        $reply = $this->restGet("admin/users");
        $usernames = [];
        $passwords = [];
        if ($reply["status"] == false) {
            $this->last_api_message = "Unable to fetch list of accounts: " . $reply["message"];
            return ["status" => false,"usernames" => [],"passwords" => []];
        }
        $this->last_api_message = "Got account list";
        $json = json_decode($reply["message"]);
        foreach ($json as $index => $entry) {
            $usernames[] = $entry->email;
            if ($include_passwords == true) {
                $passwords[] = $entry->new_password; /// ? why is it new_password and not password
            }
        }
        return ["status" => true,"usernames" => $usernames,"passwords" => $passwords];
    }

    protected function toggleAutodj(): bool
    {
        if ($this->package->getAutodj() == false) {
            $this->last_api_message = "Package does not support auto DJ";
            return false;
        }
        $status_state = $this->streamState();
        if ($status_state["status"] == false) {
            $this->last_api_message = "server appears to be down (needs to be started before you can toggle)";
            return false;
        }
        $options = [true => "stop",false => "start"];
        $reply = $this->restPost("station/" . $this->apiStationId()
                                . "/backend/" . $options[$status_state["autodj"]]);
        if ($reply["status"] == false) {
            $this->last_api_message = "Unable to toggle auto dj";
            return false;
        }
        $json = json_decode($reply["message"]);
        if ($json->success == false) {
            $this->last_api_message = "Unable to toggle autoDJ please check if you have a playlist";
            return false;
        }
        $this->last_api_message = "Toggled auto DJ to: " . $options[$status_state["autodj"]] . "";
        return true;
    }
    protected function autodjNext(): bool
    {
        if ($this->package->getAutodj() == false) {
            $this->last_api_message = "No auto DJ";
            return false;
        }
        $status_state = $this->streamState();
        if ($status_state["status"] == false) {
            $this->last_api_message = "Unable to get stream status";
            return false;
        }
        if ($status_state["autodj"] == false) {
            $this->last_api_message = "AutoDJ not running";
            return false;
        }
        $reply = $this->restPost("station/" . $this->apiStationId() . "/backend/skip");
        if ($reply["status"] == false) {
            $this->last_api_message = "Failed to skip track: " . $reply["message"];
            return false;
        }
        $json = json_decode($reply["message"]);
        $this->last_api_message = "Skip accepted";
        return $json->success;
    }
    protected function stopServer(): bool
    {
        $status_state = $this->streamState();
        if ($status_state["status"] == false) {
            $this->last_api_message = "Unable to get stream status";
            return false;
        }
        if ($status_state["state"] == false) {
            $this->last_api_message = "server already stopped";
            return true;
        }
        $stopped_auto_dj = true;
        if ($status_state["autodj"] == true) {
            $this->last_api_message = "Unable to stop autoDJ";
            $reply = $this->restPost("station/" . $this->apiStationId() . "/backend/stop");
            if ($reply["status"] == true) {
                $json = json_decode($reply["message"]);
                $stopped_auto_dj = $json->success;
            }
        }
        if ($stopped_auto_dj == false) {
            $this->last_api_message = "AutoDJ is still running even after requesting stop!";
            return false;
        }
        $reply = $this->restPost("station/" . $this->apiStationId() . "/frontend/stop");
        if ($reply["status"] == false) {
            $this->last_api_message = "Unable to stop stream";
            return false;
        }
        $json = json_decode($reply["message"]);
        if ($json->success == false) {
            $this->last_api_message = "Requesting stop server but it failed";
            return false;
        }
        $this->last_api_message = "server stopped";
        return true;
    }
    protected function startServer(int $skip_auto_dj = 0): bool
    {
        $reply = $this->restPost("station/" . $this->apiStationId() . "/frontend/start");
        if ($reply["status"] == false) {
            $this->last_api_message = "Unable to request start stream";
            return false;
        }
        $json = json_decode($reply["message"]);
        if ($json->success == false) {
            $this->last_api_message = "Unable to start stream";
            return false;
        }
        if (($skip_auto_dj == 1) || ($this->package->getAutodj() == false)) {
            $this->last_api_message = "Server started";
            return true;
        }
        $this->last_api_message = "Server started / Unable to start AutoDJ";
        $reply = $this->restPost("station/" . $this->apiStationId() . "/backend/start");
        if ($reply["status"] == false) {
            $this->last_api_message = "Server started / Unable to request AutoDJ";
            return true;
        }
        $json = json_decode($reply["message"]);
        if ($json->success == false) {
            $this->last_api_message = "Server started / Unable to start AutoDJ";
            return true;
        }
        $this->last_api_message = "Server and AutoDJ started";
        return true;
    }
    protected function susspendServer(): bool
    {
        $reply = $this->accountState();
        if ($reply["status"] == false) {
            $this->last_api_message = "Unable to request susspend server";
            return false;
        }
        if ($reply["state"] == false) {
            $this->last_api_message = "Account allready susspended";
            return true;
        }
        if ($this->stopServer() == false) {
            $this->last_api_message = "Unable to stop stream in prep to susspend";
            return false;
        }
        $post_fields = ["roles" => [null]];
        $reply = $this->restPut("admin/user/" . $this->apiUserId() . "", $post_fields);
        if ($reply["status"] == false) {
            $this->last_api_message = "Unable to request remove access to server: " . $reply["message"];
            return false;
        }
        $json = json_decode($reply["message"]);
        if ($json->success == true) {
            $this->last_api_message = "Account: susspended";
            return true;
        }
        $this->last_api_message = "Unable to remove access to server";
        return false;
    }

    protected function unSusspendServer(): bool
    {
        $reply = $this->accountState();
        if ($reply["status"] == false) {
            $this->last_api_message = "Unable to get account state";
            return false;
        }
        if ($reply["state"] == true) {
            $this->last_api_message = "Account allready active";
            return false;
        }
        $this->last_api_message = "Sending request to un_susspend to server";
        $post_fields = ["roles" => [$this->apiRoleId()]];
        $reply = $this->restPut("admin/user/" . $this->apiUserId(), $post_fields);
        if ($reply["status"] == false) {
            $this->last_api_message = "Request failed";
            return false;
        }
        $json = json_decode($reply["message"]);
        if ($json->success == true) {
            $this->last_api_message = "Account: active";
        }
        return $json->success;
    }
    protected function changePassword(): bool
    {
        $values = [
        "new_password" => $this->stream->getAdminPassword(),
        ];
        $reply = $this->restPut("admin/user/" . $this->apiUserId(), $values);
        if ($reply["status"] == false) {
            $this->last_api_message = "Request failed";
            return false;
        }
        $this->last_api_message = "Password change request received";
        $json = json_decode($reply["message"]);
        return $json->success;
    }
    protected function changeTitleNow(string $newtitle = "Not set"): bool
    {
        $this->last_api_message = "Skipped changeTitle not supported on this api";
        return false;
    }
}
