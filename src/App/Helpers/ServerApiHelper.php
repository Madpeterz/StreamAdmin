<?php

namespace App\Helpers;

use App\Models\Apis;
use App\Models\Avatar;
use App\Models\Package;
use App\Models\Rental;
use App\Models\Server;
use App\Models\Stream;
use App\Models\StreamSet;
use YAPF\Core\SqlConnectedClass;
use YAPF\InputFilter\InputFilter;

class ServerApiHelper extends SqlConnectedClass
{
    protected $server = null;
    protected $package = null;
    protected $rental = null;
    protected $stream = null;
    protected $avatar = null;
    protected $message = "No actions taken";
    protected $server_api = null;
    protected $api_config = null;

    protected $callable_actions = [
        "api_enable_account" => ["eventEnableStart"],
        "api_list_djs" => ["eventClearDjs"],
        "api_change_title" => ["eventEnableStart"],
        "api_purge_djs" => ["eventClearDjs"],
        "api_disable_account" => ["eventDisableRevoke"],
        "apiServerStatus" => ["apiServerStatus"],
        "api_start" => ["optToggleStatus","eventEnableStart"],
        "api_stop" => ["optToggleStatus","eventEnableStart"],
        "api_autodj_toggle" => ["optToggleAutodj"],
        "api_autodj_next" => ["optAutodjNext"],
        "api_customize_username" => ["eventStartSyncUsername"],
        "api_recreate_account" => [],
        "api_reset_passwords" => ["optPasswordReset"],
        "api_set_passwords" => ["optPasswordReset","eventResetPasswordRevoke"],
    ];

    public function getMessage()
    {
        return $this->message;
    }
    function __construct(stream $stream = null, bool $auto_load = true)
    {
        $this->force_set_stream($stream, $auto_load);
    }
    public function eventRecreateRevoke(): bool
    {
        return $this->api_recreate_account();
    }
    public function eventEnableStart(): bool
    {
        return $this->api_enable_account();
    }
    public function eventClearDjs(): bool
    {
        return $this->api_purge_djs();
    }
    public function eventDisableExpire(): bool
    {
        return $this->api_disable_account();
    }
    public function eventDisableRevoke(): bool
    {
        return $this->api_disable_account();
    }
    public function eventEnableRenew(): bool
    {
        return $this->api_enable_account();
    }
    public function eventResetPasswordRevoke(): bool
    {
        return $this->api_reset_passwords();
    }
    public function eventStartSyncUsername(): bool
    {
        return $this->api_customize_username();
    }
    public function eventRevokeResetUsername(): bool
    {
        return $this->api_customize_username();
    }
    public function optAutodjNext(): bool
    {
        return $this->api_autodj_next();
    }
    public function optPasswordReset(): bool
    {
        return $this->api_reset_passwords();
    }
    public function optToggleAutodj(): bool
    {
        return $this->api_autodj_toggle();
    }
    public function force_set_stream(Stream $stream = null, bool $auto_load = false): void
    {
        $this->stream = $stream;
        if ($stream != null) {
            $this->stream = $stream;
            if ($auto_load == true) {
                if ($this->load_server() == true) {
                    if ($this->load_api() == true) {
                        if ($this->load_package() == true) {
                            $this->server_api->update_package($this->package);
                            if ($this->load_rental() == true) {
                                $this->load_avatar();
                            }
                        }
                    }
                }
            }
        }
    }
    public function force_set_server(Server $server): bool
    {
        $this->server = $server;
        return $this->load_api();
    }
    public function force_set_rental(Rental $rental): bool
    {
        $this->rental = $rental;
        return $this->load_avatar();
    }
    public function force_set_package(Package $package): bool
    {
        $this->package = $package;
        return true;
    }
    protected function load_api(): bool
    {
        $api = new Apis();
        $processed = false;
        if ($api->loadID($this->server->getApiLink()) == true) {
            if ($api->getId() > 1) {
                $this->api_config = $api;
                $server_api_name = "server_" . $api->getName() . "";
                if (class_exists($server_api_name) == true) {
                    $this->server_api = new $server_api_name($this->stream, $this->server, $this->package);
                    $this->message = "server API loaded";
                    return true;
                } else {
                    $this->message = "unable to load server API";
                }
            } else {
                $this->message = "Server does not support API commands";
            }
        } else {
            $this->message = "Unable to load api config";
        }
        return false;
    }
    protected function load_rental(): bool
    {
        $rental = new Rental();
        if ($rental->loadByField("streamLink", $this->stream->getId()) == true) {
            $this->rental = $rental;
            $this->message = "Rental loaded";
            return true;
        }
        $this->message = "Unable to load rental";
        return false;
    }
    protected function load_package(): bool
    {
        $package = new Package();
        if ($package->loadID($this->stream->getPackageLink()) == true) {
            $this->package = $package;
            $this->message = "Package loaded";
            return true;
        }
        $this->message = "Unable to load package";
        return false;
    }
    protected function load_server(): bool
    {
        $server = new Server();
        if ($server->loadID($this->stream->getServerLink()) == true) {
            $this->message = "Server loaded";
            $this->server = $server;
            return true;
        }
        $this->message = "Unable to load server";
        return false;
    }
    protected function load_avatar(): bool
    {
        $avatar = new Avatar();
        if ($avatar->loadID($this->rental->getAvatarLink()) == true) {
            $this->message = "Avatar loaded";
            $this->avatar = $avatar;
            return true;
        }
        $this->message = "Unable to load avatar";
        return false;
    }
    protected function flag_check(string $flagname): bool
    {
        $functionname = "get_" . $flagname;
        if (($this->api_config->$functionname() == 1) && ($this->server->$functionname() == 1)) {
            $this->message = "API flag " . $flagname . " allowed";
            return true;
        }
        return false;
    }
    protected function check_flags(array $flags): bool
    {
        $flag_accepted = false;
        foreach ($flags as $flag) {
            $flag_accepted = $this->flag_check($flag);
            if ($flag_accepted == true) {
                break;
            }
        }
        return $flag_accepted;
    }
    function rand_string(int $length): string
    {
        if ($length < 8) {
            $length = 8;
        }
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        return substr(str_shuffle($chars), 0, $length);
    }
    protected function update_account_state(bool $state): bool
    {
        if ($this->rental != null) {
            // flag to set rental to $state
        }
        $update_status = $this->server_api->set_account_state($state);
        $this->message = $this->server_api->get_last_api_message();
        if ($update_status == false) {
            // rollback here rental here as it failed
        }
        return $update_status;
    }
    public function callable_action(string $action): bool
    {
        $this->message = "no server api setup";
        if ($this->server_api != null) {
            $this->message = "Not a known callable action";
            if (array_key_exists($action, $this->callable_actions) == true) {
                $this->message = $action . " is not callable on this server/api";
                if ($this->check_flags($this->callable_actions[$action]) == true) {
                    $this->message = "Passed callable action checks";
                    return true;
                }
            }
        }
        return false;
    }
    public function api_recreate_account(): bool
    {
        global $sql;
        if ($this->callable_action(__FUNCTION__) == true) {
            $old_username = $this->stream->getAdminUsername();
            $this->stream->setAdminUsername($this->stream->getOriginalAdminUsername());
            $this->stream->setAdminPassword($this->rand_string(7 + rand(1, 6)));
            $this->stream->setDjPassword($this->rand_string(5 + rand(1, 3)));
            $this->stream->setNeedWork(false);
            $update_status = $this->stream->updateEntry();
            if ($update_status["status"] == true) {
                $status = $this->server_api->remove_account($old_username);
                if ($status == true) {
                    $status = $this->server_api->recreate_account();
                }
                $this->message = $this->server_api->get_last_api_message();
                if ($status == false) {
                    $sql->flagError();
                }
                return $status;
            }
            $sql->flagError();
            $this->message = "Unable to update password in db: " . $update_status["message"];
            return false;
        } else {
            global $current_step;
            $current_step = "recreate_not_enabled";
            return true;
        }
    }
    public function api_enable_account(): bool
    {
        if ($this->callable_action(__FUNCTION__) == true) {
            $status = $this->update_account_state(true);
            $this->message = $this->server_api->get_last_api_message();
            return $status;
        }
        return false;
    }
    protected $dj_list = [];
    protected $removed_dj_counter = 0;
    public function loaded_djs(): array
    {
        return $this->dj_list;
    }
    public function get_removed_dj_counter(): int
    {
        return $this->removed_dj_counter;
    }
    public function api_list_djs(): bool
    {
        $this->dj_list = [];
        if ($this->callable_action(__FUNCTION__) == true) {
            $reply = $this->server_api->get_account_state();
            $this->message = $this->server_api->get_last_api_message();
            if ($reply["status"] == true) {
                $this->message = "Account is disabled";
                if ($reply["state"] == true) {
                    $reply = $this->server_api->get_dj_list();
                    $this->dj_list = $reply["list"];
                    if (count($this->loaded_djs()) > 0) {
                        $this->message = implode(",", $this->loaded_djs());
                    } else {
                        $this->message = $this->server_api->get_last_api_message();
                    }
                    return $reply["status"];
                }
            }
        }
        return false;
    }
    public function api_change_title(): bool
    {
        if ($this->callable_action(__FUNCTION__) == true) {
            if ($this->avatar != null) {
                $reply = $this->server_api->change_title($this->avatar->getAvatarName() . " stream");
                return $reply;
            }
            return true;
        }
        return false;
    }
    public function api_purge_djs(): bool
    {
        if ($this->callable_action(__FUNCTION__) == true) {
            $reply = $this->server_api->get_account_state();
            $this->message = $this->server_api->get_last_api_message();
            if ($reply["status"] == true) {
                $this->message = "Account is disabled";
                if ($reply["state"] == true) {
                    if ($this->api_list_djs() == true) {
                        $all_ok = true;
                        $this->removed_dj_counter = 0;
                        foreach ($this->loaded_djs() as $djaccount) {
                            $status = $this->server_api->purge_dj_account($djaccount);
                            if ($status == true) {
                                $this->removed_dj_counter++;
                            } else {
                                $all_ok = false;
                                break;
                            }
                        }
                        if ($all_ok == true) {
                            $this->message = "Removed " . $this->get_removed_dj_counter() . " dj accounts";
                        } else {
                            $this->message = $this->server_api->get_last_api_message();
                        }
                        return $all_ok;
                    }
                }
            }
        }
        return false;
    }
    public function api_disable_account(): bool
    {
        if ($this->callable_action(__FUNCTION__) == true) {
            return $this->update_account_state(false);
        }
        return false;
    }
    public function apiServerStatus(): array
    {
        if ($this->callable_action(__FUNCTION__) == true) {
            return $this->server_api->get_server_status();
        }
        return [
            "status" => false,
        "loads" => ["1" => 0,"5" => 0,"15" => 0],
        "ram" => ["free" => 0,"max" => 0],
        "streams" => ["total" => 0,"active" => 0],
        "message" => "No api",
        ];
    }


    public function api_set_passwords(string $new_dj_password = null, string $new_admin_password = null): bool
    {
        global $sql;
        $this->message = "started";
        if (($new_dj_password == null) || ($new_admin_password == null)) {
            $this->message = "no passwords sent";
            $input = new InputFilter();
            $set_dj_password = $input->postFilter(
                "set_dj_password",
                "string",
                ["minLength" => 5,"maxLength" => 12]
            );
            $set_admin_password = $input->postFilter(
                "set_admin_password",
                "string",
                ["minLength" => 5,"maxLength" => 12]
            );
            if (($set_dj_password != null) && ($set_admin_password != null)) {
                $new_dj_password = $set_dj_password;
                $new_admin_password = $set_admin_password;
                $this->message = "got passwords from input";
            } else {
                $this->message = "input failed because:" . $input->get_why_failed();
                return false;
            }
        }
        if (($new_dj_password != null) && ($new_admin_password != null)) {
            if ($new_dj_password != $new_admin_password) {
                $status = false;
                $this->message = "started api_reset_passwords";
                if ($this->server_api != null) {
                    if ($this->check_flags(["optPasswordReset","eventResetPasswordRevoke"]) == true) {
                        $this->message = "passed flag check";
                        $this->stream->setAdminPassword($new_admin_password);
                        $this->stream->setDjPassword($new_dj_password);
                        $this->stream->setNeedWork(false);
                        $update_status = $this->stream->updateEntry();
                        if ($update_status["status"] == true) {
                            $this->message = "calling api";
                            $status = $this->server_api->optPasswordReset();
                            $this->message = $this->server_api->get_last_api_message();
                            if ($status == false) {
                                $sql->flagError();
                            }
                        } else {
                            $sql->flagError();
                            $this->message = "Unable to update password in db";
                        }
                    }
                }
            } else {
                $this->message = "DJ and Admin passwords are not allowed to match";
            }
        } else {
            $this->message = "Unable to create passwords";
        }
        return $status;
    }
    public function api_reset_passwords(): bool
    {
        return $this->api_set_passwords($this->rand_string(5 + rand(1, 3)), $this->rand_string(7 + rand(1, 6)));
    }
    public function api_start(): bool
    {
        if ($this->callable_action(__FUNCTION__) == true) {
            $status = $this->server_api->optToggleStatus(true);
            $this->message = $this->server_api->get_last_api_message();
            return $status;
        }
        return false;
    }
    public function api_stop(): bool
    {
        if ($this->callable_action(__FUNCTION__) == true) {
            $status = $this->server_api->optToggleStatus(false);
            $this->message = $this->server_api->get_last_api_message();
            return $status;
        }
        return false;
    }
    public function api_autodj_toggle(): bool
    {
        if ($this->callable_action(__FUNCTION__) == true) {
            $this->message = "No avatar setup";
            if ($this->avatar != null) {
                $this->message = "No package selected";
                if ($this->package != null) {
                    $this->message = "This package does not support autoDJ";
                    if ($this->package->getAutodj() == true) {
                        $status = $this->server_api->optToggleAutodj();
                        $this->message = $this->server_api->get_last_api_message();
                        return $status;
                    }
                }
            }
        }
        return false;
    }
    public function api_autodj_next(): bool
    {
        if ($this->callable_action(__FUNCTION__) == true) {
            $this->message = "No avatar setup";
            if ($this->avatar != null) {
                $this->message = "No package selected";
                if ($this->package != null) {
                    $this->message = "This package does not support autoDJ";
                    if ($this->package->getAutodj() == true) {
                        $status = $this->server_api->optAutodjNext();
                        $this->message = $this->server_api->get_last_api_message();
                        return $status;
                    }
                }
            }
        }
        return false;
    }
    public function get_all_accounts(bool $include_passwords = false, StreamSet $stream_set = null): array
    {
        if ($this->server_api != null) {
            $status = $this->server_api->get_account_name_list($include_passwords, $stream_set);
            $this->message = $this->server_api->get_last_api_message();
            return $status;
        }
        return ["status" => false,"usernames" => [],"passwords" => []];
    }
    protected function get_stream_customized_username(): string
    {
        $new_username = "";
        if ($this->avatar == null) {
            // reset username
            $new_username = $this->stream->getOriginalAdminUsername();
        } else {
            // customize username
            $server_accounts = $this->server_api->get_account_name_list();
            $this->message = $this->server_api->get_last_api_message();
            if ($server_accounts["status"] == true) {
                if (in_array($this->stream->getAdminUsername(), $server_accounts["usernames"]) == true) {
                    $acceptable_names = [];
                    $avname = explode(" ", strtolower($this->avatar->getAvatarName()));
                    $acceptable_names[] = $avname[0]; // Firstname
                    $acceptable_names[] = $avname[0] . "_"
                    . substr($avname[1], 0, 2); // Firstname 2 letters of last name
                    $acceptable_names[] = $avname[0] . "_"
                    . $this->stream->getPort(); // Firstname Port
                    $acceptable_names[] = $avname[0] . "_"
                    . $this->stream->getPort() . "_" . $this->package->getBitrate(); // Firstname Port Bitrate
                    $acceptable_names[] = $avname[0] . "_"
                    . $this->stream->getPort() . "_" . $this->server->getId(); // Firstname Port ServerID
                    $acceptable_names[] = $avname[0] . "_"
                    . $this->rental->getRentalUid(); // Firstname RentalUID
                    $accepted_name = "";
                    foreach ($acceptable_names as $testname) {
                        if (in_array($testname, $server_accounts["usernames"]) == false) {
                            $accepted_name = $testname;
                            break;
                        }
                    }
                    if (in_array($accepted_name, $acceptable_names) == true) {
                        $new_username = $accepted_name;
                    }
                }
            }
        }
        return $new_username;
    }
    public function api_customize_username(): bool
    {
        global $retry;
        $retry = false;
        $status = false;
        if ($this->callable_action(__FUNCTION__) == true) {
            $all_ok = true;
            $stream_state_check = $this->server_api->get_stream_state();
            $this->message = $this->server_api->get_last_api_message();
            if ($stream_state_check["status"] == true) {
                if ($stream_state_check["state"] == true) {
                    $retry = true;
                } else {
                    $all_ok = true;
                }
            } else {
                $all_ok = false;
            }
            if (($retry == false) && ($all_ok == true)) {
                $new_username = $this->get_stream_customized_username();
                if ($new_username != "") {
                    $old_username = $this->stream->getAdminUsername();
                    if ($old_username != $new_username) {
                        $this->stream->setAdminUsername($new_username);
                        $update_status = $this->stream->updateEntry();
                        if ($update_status["status"] == true) {
                            $status = $this->server_api->eventStartSyncUsername($old_username);
                            $this->message = $this->server_api->get_last_api_message();
                            if ($status == false) {
                                $this->sql->flagError();
                            }
                        } else {
                            $this->sql->flagError();
                            $this->message = "failed to save changes to DB";
                        }
                    } else {
                        $status = true;
                        $this->message = "No change needed";
                    }
                } else {
                    $status = false;
                    $this->message = "No new username found";
                }
            } elseif (($retry == true) && ($all_ok == true)) {
                $status = $this->server_api->optToggleStatus(false);
                $this->message = $this->server_api->get_last_api_message();
                if ($status == true) {
                    $this->message = "Unable to update username right now stopping server!";
                }
            }
        }
        return $status;
    }
}
