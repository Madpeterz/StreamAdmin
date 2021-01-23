<?php

namespace App\Helpers;

use App\MediaServer\Abstracts\PublicApi;
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
    protected ?Server $server = null;
    protected ?Package $package = null;
    protected ?Rental $rental = null;
    protected ?Stream $stream = null;
    protected ?Avatar $avatar = null;
    protected $message = "No actions taken";
    protected ?PublicApi $serverApi = null;
    protected ?Apis $api_config = null;

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

    public function getMessage(): string
    {
        return $this->message;
    }
    public function __construct(stream $stream = null, bool $auto_load = true)
    {
        $this->forceSetStream($stream, $auto_load);
    }
    public function eventRecreateRevoke(): bool
    {
        return $this->apiRecreateAccount();
    }
    public function eventEnableStart(): bool
    {
        return $this->apiEnableAccount();
    }
    public function eventClearDjs(): bool
    {
        return $this->apiPurgeDjs();
    }
    public function eventDisableExpire(): bool
    {
        return $this->apiDisableAccount();
    }
    public function eventDisableRevoke(): bool
    {
        return $this->apiDisableAccount();
    }
    public function eventEnableRenew(): bool
    {
        return $this->apiEnableAccount();
    }
    public function eventResetPasswordRevoke(): bool
    {
        return $this->apiResetPasswords();
    }
    public function eventStartSyncUsername(): bool
    {
        return $this->apiCustomizeUsername();
    }
    public function eventRevokeResetUsername(): bool
    {
        return $this->apiCustomizeUsername();
    }
    public function optAutodjNext(): bool
    {
        return $this->apiAutodjNext();
    }
    public function optPasswordReset(): bool
    {
        return $this->apiResetPasswords();
    }
    public function optToggleAutodj(): bool
    {
        return $this->apiAutodjToggle();
    }
    public function forceSetStream(Stream $stream = null, bool $auto_load = false): void
    {
        $this->stream = $stream;
        if ($stream != null) {
            $this->stream = $stream;
            if ($auto_load == true) {
                if ($this->loadServer() == true) {
                    if ($this->loadApi() == true) {
                        if ($this->loadPackage() == true) {
                            $this->serverApi->updatePackage($this->package);
                            if ($this->loadRental() == true) {
                                $this->loadAvatar();
                            }
                        }
                    }
                }
            }
        }
    }
    public function forceSetServer(Server $server): bool
    {
        $this->server = $server;
        return $this->loadApi();
    }
    public function forceSetRental(Rental $rental): bool
    {
        $this->rental = $rental;
        return $this->loadAvatar();
    }
    public function forceSetPackage(Package $package): bool
    {
        $this->package = $package;
        return true;
    }
    protected function loadApi(): bool
    {
        $api = new Apis();
        $processed = false;
        if ($api->loadID($this->server->getApiLink()) == true) {
            if ($api->getId() > 1) {
                $this->api_config = $api;
                $serverApiName = "App\\MediaServer\\" . ucfirst($api->getName());
                if (class_exists($serverApiName) == true) {
                    $this->serverApi = new $serverApiName($this->stream, $this->server, $this->package);
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
    protected function loadRental(): bool
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
    protected function loadPackage(): bool
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
    protected function loadServer(): bool
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
    protected function loadAvatar(): bool
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
    protected function flagCheck(string $flagname): bool
    {
        $functionname = "get" . ucfirst($flagname);
        if (($this->api_config->$functionname() == 1) && ($this->server->$functionname() == 1)) {
            $this->message = "API flag " . $flagname . " allowed";
            return true;
        }
        return false;
    }
    protected function checkFlags(array $flags): bool
    {
        $flag_accepted = false;
        foreach ($flags as $flag) {
            $flag_accepted = $this->flagCheck($flag);
            if ($flag_accepted == true) {
                break;
            }
        }
        return $flag_accepted;
    }
    protected function randString(int $length): string
    {
        if ($length < 8) {
            $length = 8;
        }
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        return substr(str_shuffle($chars), 0, $length);
    }
    protected function updateAccountState(bool $state): bool
    {
        if ($this->rental != null) {
            // flag to set rental to $state
        }
        $update_status = $this->serverApi->setAccountState($state);
        $this->message = $this->serverApi->getLastApiMessage();
        if ($update_status == false) {
            // rollback here rental here as it failed
        }
        return $update_status;
    }
    public function callableAction(string $action): bool
    {
        $this->message = "no server api setup";
        if ($this->serverApi != null) {
            $this->message = "Not a known callable action";
            if (array_key_exists($action, $this->callable_actions) == true) {
                $this->message = $action . " is not callable on this server/api";
                if ($this->checkFlags($this->callable_actions[$action]) == true) {
                    $this->message = "Passed callable action checks";
                    return true;
                }
            }
        }
        return false;
    }
    public function apiRecreateAccount(): bool
    {
        global $sql;
        if ($this->callableAction(__FUNCTION__) == true) {
            $old_username = $this->stream->getAdminUsername();
            $this->stream->setAdminUsername($this->stream->getOriginalAdminUsername());
            $this->stream->setAdminPassword($this->randString(7 + rand(1, 6)));
            $this->stream->setDjPassword($this->randString(5 + rand(1, 3)));
            $this->stream->setNeedWork(false);
            $update_status = $this->stream->updateEntry();
            if ($update_status["status"] == true) {
                $status = $this->serverApi->removeAccount($old_username);
                if ($status == true) {
                    $status = $this->serverApi->recreateAccount();
                }
                $this->message = $this->serverApi->getLastApiMessage();
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
    public function apiEnableAccount(): bool
    {
        if ($this->callableAction(__FUNCTION__) == true) {
            $status = $this->updateAccountState(true);
            $this->message = $this->serverApi->getLastApiMessage();
            return $status;
        }
        return false;
    }
    protected $dj_list = [];
    protected $removed_dj_counter = 0;
    /**
     * loadedDjs
     * @return mixed[]
     */
    public function loadedDjs(): array
    {
        return $this->dj_list;
    }
    public function getRemovedDjCounter(): int
    {
        return $this->removed_dj_counter;
    }
    public function apiListDjs(): bool
    {
        $this->dj_list = [];
        if ($this->callableAction(__FUNCTION__) == true) {
            $reply = $this->serverApi->getAccountState();
            $this->message = $this->serverApi->getLastApiMessage();
            if ($reply["status"] == true) {
                $this->message = "Account is disabled";
                if ($reply["state"] == true) {
                    $reply = $this->serverApi->djList();
                    $this->dj_list = $reply["list"];
                    if (count($this->loadedDjs()) > 0) {
                        $this->message = implode(",", $this->loadedDjs());
                    } else {
                        $this->message = $this->serverApi->getLastApiMessage();
                    }
                    return $reply["status"];
                }
            }
        }
        return false;
    }
    public function apiChangeTitle(): bool
    {
        if ($this->callableAction(__FUNCTION__) == true) {
            if ($this->avatar != null) {
                $reply = $this->serverApi->changeTitle($this->avatar->getAvatarName() . " stream");
                return $reply;
            }
            return true;
        }
        return false;
    }
    public function apiPurgeDjs(): bool
    {
        if ($this->callableAction(__FUNCTION__) == true) {
            $reply = $this->serverApi->getAccountState();
            $this->message = $this->serverApi->getLastApiMessage();
            if ($reply["status"] == true) {
                $this->message = "Account is disabled";
                if ($reply["state"] == true) {
                    if ($this->apiListDjs() == true) {
                        $all_ok = true;
                        $this->removed_dj_counter = 0;
                        foreach ($this->loadedDjs() as $djaccount) {
                            $status = $this->serverApi->purgeDjAccount($djaccount);
                            if ($status == true) {
                                $this->removed_dj_counter++;
                            } else {
                                $all_ok = false;
                                break;
                            }
                        }
                        if ($all_ok == true) {
                            $this->message = "Removed " . $this->getRemovedDjCounter() . " dj accounts";
                        } else {
                            $this->message = $this->serverApi->getLastApiMessage();
                        }
                        return $all_ok;
                    }
                }
            }
        }
        return false;
    }
    public function apiDisableAccount(): bool
    {
        if ($this->callableAction(__FUNCTION__) == true) {
            return $this->updateAccountState(false);
        }
        return false;
    }
    /**
     * apiServerStatus
     * @return mixed[] [status => bool, loads=>[1,5,15], ram=>[free,max], streams=>[total,active], message=> string]
     */
    public function apiServerStatus(): array
    {
        if ($this->callableAction(__FUNCTION__) == true) {
            return $this->serverApi->serverStatus();
        }
        return [
            "status" => false,
            "loads" => ["1" => 0,"5" => 0,"15" => 0],
            "ram" => ["free" => 0,"max" => 0],
            "streams" => ["total" => 0,"active" => 0],
            "message" => "No api",
        ];
    }


    public function apiSetPasswords(string $new_dj_password = null, string $new_admin_password = null): bool
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
                $this->message = "input failed because:" . $input->getWhyFailed();
                return false;
            }
        }
        if (($new_dj_password != null) && ($new_admin_password != null)) {
            if ($new_dj_password != $new_admin_password) {
                $status = false;
                $this->message = "started api_reset_passwords";
                if ($this->serverApi != null) {
                    if ($this->checkFlags(["optPasswordReset","eventResetPasswordRevoke"]) == true) {
                        $this->message = "passed flag check";
                        $this->stream->setAdminPassword($new_admin_password);
                        $this->stream->setDjPassword($new_dj_password);
                        $this->stream->setNeedWork(false);
                        $update_status = $this->stream->updateEntry();
                        if ($update_status["status"] == true) {
                            $this->message = "calling api";
                            $status = $this->serverApi->optPasswordReset();
                            $this->message = $this->serverApi->getLastApiMessage();
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
    public function apiResetPasswords(): bool
    {
        return $this->apiSetPasswords($this->randString(5 + rand(1, 3)), $this->randString(7 + rand(1, 6)));
    }
    public function apiStart(): bool
    {
        if ($this->callableAction(__FUNCTION__) == true) {
            $status = $this->serverApi->optToggleStatus(true);
            $this->message = $this->serverApi->getLastApiMessage();
            return $status;
        }
        return false;
    }
    public function apiStop(): bool
    {
        if ($this->callableAction(__FUNCTION__) == true) {
            $status = $this->serverApi->optToggleStatus(false);
            $this->message = $this->serverApi->getLastApiMessage();
            return $status;
        }
        return false;
    }
    public function apiAutodjToggle(): bool
    {
        if ($this->callableAction(__FUNCTION__) == true) {
            $this->message = "No avatar setup";
            if ($this->avatar != null) {
                $this->message = "No package selected";
                if ($this->package != null) {
                    $this->message = "This package does not support autoDJ";
                    if ($this->package->getAutodj() == true) {
                        $status = $this->serverApi->optToggleAutodj();
                        $this->message = $this->serverApi->getLastApiMessage();
                        return $status;
                    }
                }
            }
        }
        return false;
    }
    public function apiAutodjNext(): bool
    {
        if ($this->callableAction(__FUNCTION__) == true) {
            $this->message = "No avatar setup";
            if ($this->avatar != null) {
                $this->message = "No package selected";
                if ($this->package != null) {
                    $this->message = "This package does not support autoDJ";
                    if ($this->package->getAutodj() == true) {
                        $status = $this->serverApi->optAutodjNext();
                        $this->message = $this->serverApi->getLastApiMessage();
                        return $status;
                    }
                }
            }
        }
        return false;
    }
    /**
     * getAllAccounts
     * @return mixed[] [status => bool, usernames=> array, passwords=> array]
     */
    public function getAllAccounts(bool $include_passwords = false, StreamSet $stream_set = null): array
    {
        if ($this->serverApi != null) {
            $status = $this->serverApi->accountNameList($include_passwords, $stream_set);
            $this->message = $this->serverApi->getLastApiMessage();
            return $status;
        }
        return ["status" => false,"usernames" => [],"passwords" => []];
    }
    protected function getStreamCustomizedUsername(): string
    {
        $new_username = "";
        if ($this->avatar == null) {
            // reset username
            $new_username = $this->stream->getOriginalAdminUsername();
        } else {
            // customize username
            $server_accounts = $this->serverApi->accountNameList();
            $this->message = $this->serverApi->getLastApiMessage();
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
    public function apiCustomizeUsername(): bool
    {
        global $retry;
        $retry = false;
        $status = false;
        if ($this->callableAction(__FUNCTION__) == true) {
            $all_ok = true;
            $stream_state_check = $this->serverApi->StreamState();
            $this->message = $this->serverApi->getLastApiMessage();
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
                $new_username = $this->getStreamCustomizedUsername();
                if ($new_username != "") {
                    $old_username = $this->stream->getAdminUsername();
                    if ($old_username != $new_username) {
                        $this->stream->setAdminUsername($new_username);
                        $update_status = $this->stream->updateEntry();
                        if ($update_status["status"] == true) {
                            $status = $this->serverApi->eventStartSyncUsername($old_username);
                            $this->message = $this->serverApi->getLastApiMessage();
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
                $status = $this->serverApi->optToggleStatus(false);
                $this->message = $this->serverApi->getLastApiMessage();
                if ($status == true) {
                    $this->message = "Unable to update username right now stopping server!";
                }
            }
        }
        return $status;
    }
}
