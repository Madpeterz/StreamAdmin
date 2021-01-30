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
    /**
     * getPostFormated
     * @return mixed[] [json =>  postdata]
     */
    protected function getPostFormated(array $postdata = []): array
    {
        return ['json' => $postdata];
    }
    protected function terminateAccount(string $old_username): bool
    {
        return false;
    }
    protected function createAccount(): bool
    {
        return false;
    }
    protected function removeDJ(string $djaccount): bool
    {
        $this->last_api_message = "unable to get list of djs";
        $has_dj_list = true;
        if ($this->station_djs_map == null) {
            $reply = $this->djList();
            $has_dj_list = $reply["status"];
        }
        if ($has_dj_list == true) {
            $this->last_api_message = "unable to find DJ";
            $removed = false;
            foreach ($this->station_djs_map as $key => $value) {
                if ($value == $djaccount) {
                    $this->last_api_message = "unable to remove DJ";
                    $reply = $this->restDelete("station/" . $this->stream->getApiConfigValue1() . "/streamer/" . $key);
                    if ($reply["status"] == true) {
                        $json = json_decode($reply["message"]);
                        $removed = $json->success;
                        $this->last_api_message = "DJ removed";
                        if ($json->success == false) {
                            $this->last_api_message = "Failed to remove DJ account";
                        }
                    }
                    break;
                }
            }
            return $removed;
        }
        return false;
    }
    /**
     * djList
     * @return mixed[] [status => bool, list=> array]
     */
    public function djList(): array
    {
        $this->last_api_message = "unable to get list of djs";
        $reply = $this->restGet("station/" . $this->stream->getApiConfigValue1() . "/streamers");
        if ($reply["status"] == true) {
            $this->last_api_message = "fetched DJ list";
            $json = json_decode($reply["message"]);
            $this->station_djs_map = [];
            foreach ($json as $index => $entry) {
                $this->station_djs_map[$entry->id] = $entry->streamer_username;
            }
            return ["status" => true,"list" => array_values($this->station_djs_map)];
        }
        return ["status" => false,"list" => []];
    }
    /**
     * serverStatus
     * @return mixed[] [status => bool, loads=>[1,5,15], ram=>[free,max], streams=>[total,active], message=> string]
     */
    public function serverStatus(): array
    {
        $reply = $this->restGet("status");
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
     * @return mixed[] [status => bool, state=>bool]
     */
    protected function accountState(): array
    {
        $status = false;
        $state = false;
        $this->last_api_message = "fetching account state";
        $reply = $this->restGet("admin/user/" . $this->stream->getApiConfigValue3() . "");
        $status = $reply["status"];
        if ($status == true) {
            $this->last_api_message = "got account state";
            $json = json_decode($reply["message"]);
            if (count($json->roles) >= 1) {
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
        $this->last_api_message = "unable to get station status";
        $reply = $this->restGet("station/" . $this->stream->getApiConfigValue1() . "/status");
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
        return ["status" => $reply["status"],"state" => $state,"source" => $source,"autodj" => $autodj];
    }
    /**
     * accountNameList
     * @return mixed[] [status => bool, usernames=>array,passwords=>array]
     */
    public function accountNameList(bool $include_passwords = false, StreamSet $stream_set = null): array
    {
        $reply = $this->restGet("​admin​/users");
        $usernames = [];
        $passwords = [];
        $status = $reply["status"] ;
        if ($status == true) {
            $json = json_decode($reply["message"]);
            foreach ($json as $index => $entry) {
                $usernames[] = $entry->email;
                if ($include_passwords == true) {
                    $passwords[] = $entry->new_password; /// ?
                }
            }
        }
        return ["status" => $status,"usernames" => $usernames,"passwords" => $passwords];
    }

    protected function toggleAutodj(): bool
    {
        $this->last_api_message = "Package does not support auto DJ";
        if ($this->package->getAutodj() == true) {
            $status_state = $this->streamState();
            if ($status_state["status"] == true) {
                $this->last_api_message = "server appears to be down (needs to be started before you can toggle)";
                if ($status_state["state"] == true) {
                    $options = [true => "stop",false => "start"];
                    $this->last_api_message = "Attempting to toggle auto dj";
                    $reply = $this->restPost("station/" . $this->stream->getApiConfigValue1()
                                            . "/backend/" . $options[$status_state["autodj"]]);
                    if ($reply["status"] == true) {
                        $json = json_decode($reply["message"]);
                        if ($json->success == true) {
                            $this->last_api_message = "Toggled auto DJ to: " . $options[$status_state["autodj"]] . "";
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }
    protected function autodjNext(): bool
    {
        $this->last_api_message = "No auto DJ";
        if ($this->package->getAutodj() == true) {
            $status_state = $this->streamState();
            if ($status_state["status"] == true) {
                $this->last_api_message = "AutoDJ not running";
                if ($status_state["autodj"] == true) {
                    $this->last_api_message = "Failed to skip track";
                    $reply = $this->restPost("station/" . $this->stream->getApiConfigValue1() . "/backend/skip");
                    if ($reply["status"] == true) {
                        $json = json_decode($reply["message"]);
                        $this->last_api_message = "Skip accepted";
                        return $json->success;
                    }
                }
            }
        }
        return false;
    }
    protected function stopServer(): bool
    {
        $status_state = $this->streamState();
        if ($status_state["status"] == false) {
            return false;
        }
        if ($status_state["state"] == false) {
            $this->last_api_message = "server already stopped";
            return true;
        }
        $stopped_auto_dj = !$status_state["autodj"];
        if ($stopped_auto_dj == false) {
            $this->last_api_message = "Unable to stop autoDJ";
            $reply = $this->restPost("station/" . $this->stream->getApiConfigValue1() . "/backend/stop");
            if ($reply["status"] == true) {
                $json = json_decode($reply["message"]);
                $stopped_auto_dj = $json->success;
            }
        }
        if ($stopped_auto_dj == true) {
            $this->last_api_message = "Unable to stop stream";
            $reply = $this->restPost("station/" . $this->stream->getApiConfigValue1() . "/frontend/stop");
            if ($reply["status"] == true) {
                $json = json_decode($reply["message"]);
                if ($json->success == true) {
                    $this->last_api_message = "server stopped";
                    return true;
                }
            }
        }
        return false;
    }
    protected function startServer(int $skip_auto_dj = 0): bool
    {
        $this->last_api_message = "Unable to start stream";
        $reply = $this->restPost("station/" . $this->stream->getApiConfigValue1() . "/frontend/start");
        if ($reply["status"] == true) {
            $json = json_decode($reply["message"]);
            if ($json->success == true) {
                if ($skip_auto_dj == 1) {
                    $this->last_api_message = "Server started";
                    return true;
                }
                if ($this->package->getAutodj() == true) {
                    $this->last_api_message = "Server started / Unable to start AutoDJ";
                    $reply = $this->restPost("station/" . $this->stream->getApiConfigValue1() . "/backend/start");
                    if ($reply["status"] == true) {
                        $json = json_decode($reply["message"]);
                        if ($json->success == true) {
                            $this->last_api_message = "Server+AutoDJ started";
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }
    protected function susspendServer(): bool
    {
        $reply = $this->accountState();
        $status = false;
        if ($reply["status"] == true) {
            if ($reply["state"] == false) {
                $this->last_api_message = "Account allready susspended";
                return false;
            }
            if ($this->stopServer() == false) {
                return false;
            }
            $this->last_api_message = "Attempting to remove access to server";
            $post_fields = ["roles" => []];
            $reply = $this->restPut("admin/user/" . $this->stream->getApiConfigValue3() . "", $post_fields);
            error_log(print_r($reply, true));
            if ($reply["status"] == true) {
                $json = json_decode($reply["message"]);
                if ($json->success == true) {
                    $this->last_api_message = "Account: susspended";
                }
                return $json->success;
            }
        }
        return false;
    }
    protected function unSusspendServer(): bool
    {
        $reply = $this->accountState();
        $status = false;
        if ($reply["status"] == true) {
            if ($reply["state"] == true) {
                $this->last_api_message = "Account allready active";
                return false;
            }
            $this->last_api_message = "Sending request to un_susspend to server";
            $post_fields = ["roles" => [$this->stream->getApiConfigValue2()]];
            $reply = $this->restPut("admin/user/" . $this->stream->getApiConfigValue3() . "", $post_fields);
            if ($reply["status"] == true) {
                $this->last_api_message = "Request failed";
                $json = json_decode($reply["message"]);
                if ($json->success == true) {
                    $this->last_api_message = "Account: active";
                }
                return $json->success;
            }
        }
    }
    protected function changePassword(): bool
    {
        return false;
    }
    protected function changeTitleNow(string $newtitle = "Not set"): bool
    {
        return false;
    }
}
