<?php

namespace App\Helpers\ServerApi;

use App\R7\Set\StreamSet;
use YAPF\InputFilter\InputFilter;

abstract class ServerApiHelperMain extends FunctionsServerApiHelper
{
    protected function setMessage(string $message): void
    {
        error_log($message);
        $this->message = $message;
    }
    public function apiCreateAccount(): bool
    {
        global $sql;
        $this->setMessage("Starting function:" . __FUNCTION__);
        $this->stream->setAdminUsername($this->stream->getOriginalAdminUsername());
        $this->stream->setAdminPassword($this->randString(7 + rand(1, 6)));
        $this->stream->setDjPassword($this->randString(5 + rand(1, 3)));
        $this->stream->setNeedWork(false);
        $update_status = $this->stream->updateEntry();
        if ($update_status["status"] == false) {
            $sql->flagError();
            $this->setMessage("Unable to update password in db: " . $update_status["message"]);
            return false;
        }
        $this->setMessage("Update ok passing onto server API now");
        $status = $this->serverApi->eventCreateStream();
        $this->setMessage($this->serverApi->getLastApiMessage());
        return $status;
    }
    public function apiRecreateAccount(): bool
    {
        global $sql;
        $this->setMessage("Starting function:" . __FUNCTION__);
        if ($this->callableAction(__FUNCTION__) == false) {
            global $current_step;
            $current_step = "recreate_not_enabled";
            return true;
        }
        $old_username = $this->stream->getAdminUsername();
        $this->stream->setAdminUsername($this->stream->getOriginalAdminUsername());
        $this->stream->setAdminPassword($this->randString(7 + rand(1, 6)));
        $this->stream->setDjPassword($this->randString(5 + rand(1, 3)));
        $this->stream->setNeedWork(false);
        $update_status = $this->stream->updateEntry();
        if ($update_status["status"] == false) {
            $this->setMessage("Unable to update password in db: " . $update_status["message"]);
            return false;
        }
        $status = $this->serverApi->removeAccount($old_username);
        $this->setMessage($this->serverApi->getLastApiMessage());
        if ($status == false) {
            return false;
        }
        $status = $this->serverApi->recreateAccount();
        $this->setMessage($this->serverApi->getLastApiMessage());
        return $status;
    }

    public function apiEnableAccount(): bool
    {
        $this->setMessage("Starting function:" . __FUNCTION__);
        if ($this->callableAction(__FUNCTION__) == false) {
            return false;
        }
        $status = $this->updateAccountState(true);
        $this->setMessage($this->serverApi->getLastApiMessage());
        return $status;
    }

    public function apiListDjs(): bool
    {
        $this->setMessage("Starting function:" . __FUNCTION__);
        $this->dj_list = [];
        if ($this->callableAction(__FUNCTION__) == false) {
            return false;
        }
        $reply = $this->serverApi->getAccountState();
        if ($reply["status"] == false) {
            $this->setMessage($this->serverApi->getLastApiMessage());
            return false;
        }
        if ($reply["state"] == false) {
            $this->setMessage("Account is disabled");
            return false;
        }
        $reply = $this->serverApi->djList();
        $this->dj_list = $reply["list"];
        $this->setMessage($this->serverApi->getLastApiMessage());
        if (count($this->loadedDjs()) > 0) {
            $this->message = implode(",", $this->loadedDjs());
        }
        return $reply["status"];
    }
    public function apiChangeTitle(): bool
    {
        $this->setMessage("Starting function:" . __FUNCTION__);
        if ($this->callableAction(__FUNCTION__) == false) {
            return false;
        }
        if ($this->avatar == null) {
            $this->setMessage("Unknown avatar");
            return false;
        }
        $this->setMessage("Calling change title now");
        $status = $this->serverApi->changeTitle($this->avatar->getAvatarName() . " stream");
        $this->setMessage($this->serverApi->getLastApiMessage());
        return $status;
    }
    public function apiPurgeDjs(): bool
    {
        $this->setMessage("Starting function:" . __FUNCTION__);
        if ($this->callableAction(__FUNCTION__) == false) {
            return false;
        }
        $reply = $this->serverApi->getAccountState();
        if ($reply["status"] == false) {
            $this->setMessage($this->serverApi->getLastApiMessage());
            return false;
        }
        if ($reply["state"] == false) {
            $this->setMessage("Account is disabled");
            return false;
        }
        if ($this->apiListDjs() == false) {
            return false;
        }
        $all_ok = true;
        $this->removed_dj_counter = 0;
        foreach ($this->loadedDjs() as $djaccount) {
            $status = $this->serverApi->purgeDjAccount($djaccount);
            if ($status == false) {
                $all_ok = false;
                break;
            }
            $this->removed_dj_counter++;
        }
        $this->setMessage($this->serverApi->getLastApiMessage());
        if ($all_ok == true) {
            $this->setMessage("Removed " . $this->getRemovedDjCounter() . " dj accounts");
        }
        return $all_ok;
    }
    public function apiDisableAccount(): bool
    {
        $this->setMessage("Starting function:" . __FUNCTION__);
        if ($this->callableAction(__FUNCTION__) == false) {
            return false;
        }
        return $this->updateAccountState(false);
    }
    /**
     * apiServerStatus
     * @return mixed[] [status => bool, loads=>[1,5,15], ram=>[free,max], streams=>[total,active], message=> string]
     */
    public function apiServerStatus(): array
    {
        $this->setMessage("Starting function:" . __FUNCTION__);
        if ($this->callableAction(__FUNCTION__) == false) {
            return [
                "status" => false,
                "loads" => ["1" => 0,"5" => 0,"15" => 0],
                "ram" => ["free" => 0,"max" => 0],
                "streams" => ["total" => 0,"active" => 0],
                "message" => "No api",
            ];
        }
        $status = $this->serverApi->serverStatus();
        $this->setMessage($this->serverApi->getLastApiMessage());
        return $status;
    }


    public function apiSetPasswords(string $new_dj_password = null, string $new_admin_password = null): bool
    {
        $this->setMessage("Starting function:" . __FUNCTION__);
        global $sql;
        $this->setMessage("started");
        if ($this->serverApi == null) {
            $this->setMessage("Server API is not loaded");
            return false;
        }
        if ($this->checkFlags(["optPasswordReset","eventResetPasswordRevoke"]) == false) {
            $this->setMessage("Password reset / Password revoke not allowed");
            return false;
        }

        if (($new_dj_password == null) || ($new_admin_password == null)) {
            $this->setMessage("no passwords sent");
            $input = new InputFilter();
            $set_dj_password = $input->postFilter(
                "set_dj_password",
                "string",
                ["minLength" => 5,"maxLength" => 12]
            );
            if ($input->getWhyFailed() == "") {
                $set_admin_password = $input->postFilter(
                    "set_admin_password",
                    "string",
                    ["minLength" => 5,"maxLength" => 12]
                );
            }
            if ($set_dj_password == null) {
                $this->setMessage("DJ password is empty because: " . $input->getWhyFailed());
                return false;
            }
            if ($set_admin_password == null) {
                $this->setMessage("Admin password is empty because: " . $input->getWhyFailed());
                return false;
            }
            $new_dj_password = $set_dj_password;
            $new_admin_password = $set_admin_password;
            $this->setMessage("got passwords from input");
        }
        if (($new_dj_password == null) || ($new_admin_password == null)) {
            $this->setMessage("Unable to create passwords");
            return false;
        }
        if ($new_dj_password == $new_admin_password) {
            $this->setMessage("DJ and Admin passwords are not allowed to match");
            return false;
        }
        $this->setMessage("started api_reset_passwords");
        $this->stream->setAdminPassword($new_admin_password);
        $this->stream->setDjPassword($new_dj_password);
        $this->stream->setNeedWork(false);
        $update_status = $this->stream->updateEntry();
        if ($update_status["status"] == false) {
            $sql->flagError();
            $this->setMessage("Unable to update password in db");
            return false;
        }
        $this->setMessage("calling api");
        $status = $this->serverApi->optPasswordReset();
        $this->setMessage($this->serverApi->getLastApiMessage());
        if ($status == false) {
            $sql->flagError();
        }
        return $status;
    }
    public function apiResetPasswords(): bool
    {
        $this->setMessage("Starting function:" . __FUNCTION__);
        return $this->apiSetPasswords($this->randString(5 + rand(1, 3)), $this->randString(7 + rand(1, 6)));
    }
    public function apiStart(): bool
    {
        $this->setMessage("Starting function:" . __FUNCTION__);
        if ($this->callableAction(__FUNCTION__) == false) {
            return false;
        }
        $status = $this->serverApi->optToggleStatus(true);
        $this->setMessage($this->serverApi->getLastApiMessage());
        return $status;
    }
    public function apiStop(): bool
    {
        $this->setMessage("Starting function:" . __FUNCTION__);
        if ($this->callableAction(__FUNCTION__) == false) {
            return false;
        }
        $status = $this->serverApi->optToggleStatus(false);
        $this->setMessage($this->serverApi->getLastApiMessage());
        return $status;
    }
    public function apiAutodjToggle(): bool
    {
        $this->setMessage("Starting function:" . __FUNCTION__);
        if ($this->callableAction(__FUNCTION__) == false) {
            return false;
        }
        if ($this->package == null) {
            $this->setMessage("No package selected");
            return false;
        }
        if ($this->package->getAutodj() == false) {
            $this->setMessage("This package does not support autoDJ");
            return false;
        }
        $status = $this->serverApi->optToggleAutodj();
        $this->setMessage($this->serverApi->getLastApiMessage());
        return $status;
    }
    public function apiAutodjNext(): bool
    {
        $this->setMessage("Starting function:" . __FUNCTION__);
        if ($this->callableAction(__FUNCTION__) == false) {
            $this->setMessage("apiAutodjNext is not supported");
            return false;
        }
        if ($this->package == null) {
            $this->setMessage("No package selected");
            return false;
        }
        if ($this->package->getAutodj() == false) {
            $this->setMessage("This package does not support autoDJ");
            return false;
        }
        $status = $this->serverApi->optAutodjNext();
        $this->setMessage($this->serverApi->getLastApiMessage());
        return $status;
    }
    /**
     * getAllAccounts
     * @return mixed[] [status => bool, usernames=> array, passwords=> array]
     */
    public function getAllAccounts(bool $include_passwords = false, StreamSet $stream_set = null): array
    {
        $this->setMessage("Starting function:" . __FUNCTION__);
        if ($this->serverApi != null) {
            $status = $this->serverApi->accountNameList($include_passwords, $stream_set);
            $this->setMessage($this->serverApi->getLastApiMessage());
            return $status;
        }
        return ["status" => false,"usernames" => [],"passwords" => []];
    }
    protected function getStreamCustomizedUsername(): string
    {
        $this->setMessage("Starting function:" . __FUNCTION__);
        if ($this->avatar == null) {
            // reset username
            return $this->stream->getOriginalAdminUsername();
        }

        // customize username
        $server_accounts = $this->serverApi->accountNameList();
        $this->setMessage($this->serverApi->getLastApiMessage());
        if ($server_accounts["status"] == false) {
            return "";
        }
        if (in_array($this->stream->getAdminUsername(), $server_accounts["usernames"]) == false) {
            return "";
        }
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
        if (in_array($accepted_name, $acceptable_names) == false) {
            return "";
        }
        return $accepted_name;
    }
    public function apiCustomizeUsername(): bool
    {
        $this->setMessage("Starting function:" . __FUNCTION__);
        global $retry;
        $retry = false;
        $status = false;
        if ($this->callableAction(__FUNCTION__) == false) {
            return false;
        }
        $stream_state_check = $this->serverApi->StreamState();
        $this->setMessage($this->serverApi->getLastApiMessage());
        if ($stream_state_check["status"] == false) {
            return false;
        }
        if ($stream_state_check["state"] == true) {
            $retry = true;
        }
        if ($retry == true) {
            $status = $this->serverApi->optToggleStatus(false);
            $this->setMessage($this->serverApi->getLastApiMessage());
            if ($status == true) {
                $this->setMessage("Unable to update username right now stopping server!");
            }
            return $status;
        }
        $new_username = $this->getStreamCustomizedUsername();
        if ($new_username == "") {
            $this->setMessage("No new username found");
            return false;
        }
        $old_username = $this->stream->getAdminUsername();
        if ($old_username == $new_username) {
            $this->setMessage("No change needed");
            return true;
        }
        $this->stream->setAdminUsername($new_username);
        $update_status = $this->stream->updateEntry();
        if ($update_status["status"] == false) {
            $this->sql->flagError();
            $this->setMessage("failed to save changes to DB");
            return false;
        }
        $status = $this->serverApi->eventStartSyncUsername($old_username);
        $this->setMessage($this->serverApi->getLastApiMessage());
        if ($status == false) {
            $this->sql->flagError();
        }
        return $status;
    }
}
