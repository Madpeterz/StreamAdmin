<?php

namespace App\Helpers\ServerApi;

use App\R7\Set\StreamSet;
use YAPF\InputFilter\InputFilter;

class ServerApiHelper extends FunctionsServerApiHelper
{
    public function apiRecreateAccount(): bool
    {
        global $sql;
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
            $sql->flagError();
            $this->message = "Unable to update password in db: " . $update_status["message"];
            return false;
        }
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

    public function apiEnableAccount(): bool
    {
        if ($this->callableAction(__FUNCTION__) == false) {
            return false;
        }
        $status = $this->updateAccountState(true);
        $this->message = $this->serverApi->getLastApiMessage();
        return $status;
    }

    public function apiListDjs(): bool
    {
        $this->dj_list = [];
        if ($this->callableAction(__FUNCTION__) == false) {
            return false;
        }
        $reply = $this->serverApi->getAccountState();
        if ($reply["status"] == false) {
            $this->message = $this->serverApi->getLastApiMessage();
            return false;
        }
        if ($reply["state"] == false) {
            $this->message = "Account is disabled";
            return false;
        }
        $reply = $this->serverApi->djList();
        $this->dj_list = $reply["list"];
        $this->message = $this->serverApi->getLastApiMessage();
        if (count($this->loadedDjs()) > 0) {
            $this->message = implode(",", $this->loadedDjs());
        }
        return $reply["status"];
    }
    public function apiChangeTitle(): bool
    {
        if ($this->callableAction(__FUNCTION__) == false) {
            return false;
        }
        if ($this->avatar == null) {
            return false;
        }
        $reply = $this->serverApi->changeTitle($this->avatar->getAvatarName() . " stream");
        return $reply;
    }
    public function apiPurgeDjs(): bool
    {
        if ($this->callableAction(__FUNCTION__) == false) {
            return false;
        }
        $reply = $this->serverApi->getAccountState();
        if ($reply["status"] == false) {
            $this->message = $this->serverApi->getLastApiMessage();
            return false;
        }
        if ($reply["state"] == false) {
            $this->message = "Account is disabled";
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
        $this->message = $this->serverApi->getLastApiMessage();
        if ($all_ok == true) {
            $this->message = "Removed " . $this->getRemovedDjCounter() . " dj accounts";
        }
        return $all_ok;
    }
    public function apiDisableAccount(): bool
    {
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
        if ($this->callableAction(__FUNCTION__) == false) {
            return [
                "status" => false,
                "loads" => ["1" => 0,"5" => 0,"15" => 0],
                "ram" => ["free" => 0,"max" => 0],
                "streams" => ["total" => 0,"active" => 0],
                "message" => "No api",
            ];
        }
        return $this->serverApi->serverStatus();
    }


    public function apiSetPasswords(string $new_dj_password = null, string $new_admin_password = null): bool
    {
        global $sql;
        $this->message = "started";
        if ($this->serverApi == null) {
            $this->message = "Server API is not loaded";
            return false;
        }
        if ($this->checkFlags(["optPasswordReset","eventResetPasswordRevoke"]) == false) {
            $this->message = "Password reset / Password revoke not allowed";
            return false;
        }

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
            if (($set_dj_password == null) || ($set_admin_password == null)) {
                $this->message = "input failed because:" . $input->getWhyFailed();
                return false;
            }
            $new_dj_password = $set_dj_password;
            $new_admin_password = $set_admin_password;
            $this->message = "got passwords from input";
        }
        if (($new_dj_password == null) || ($new_admin_password == null)) {
            $this->message = "Unable to create passwords";
            return false;
        }
        if ($new_dj_password == $new_admin_password) {
            $this->message = "DJ and Admin passwords are not allowed to match";
            return false;
        }
        $this->message = "started api_reset_passwords";
        $this->message = "passed flag check";
        $this->stream->setAdminPassword($new_admin_password);
        $this->stream->setDjPassword($new_dj_password);
        $this->stream->setNeedWork(false);
        $update_status = $this->stream->updateEntry();
        if ($update_status["status"] == false) {
            $sql->flagError();
            $this->message = "Unable to update password in db";
            return false;
        }
        $this->message = "calling api";
        $status = $this->serverApi->optPasswordReset();
        $this->message = $this->serverApi->getLastApiMessage();
        if ($status == false) {
            $sql->flagError();
        }
        return $status;
    }
    public function apiResetPasswords(): bool
    {
        return $this->apiSetPasswords($this->randString(5 + rand(1, 3)), $this->randString(7 + rand(1, 6)));
    }
    public function apiStart(): bool
    {
        if ($this->callableAction(__FUNCTION__) == false) {
            return false;
        }
        $status = $this->serverApi->optToggleStatus(true);
        $this->message = $this->serverApi->getLastApiMessage();
        return $status;
    }
    public function apiStop(): bool
    {
        if ($this->callableAction(__FUNCTION__) == false) {
            return false;
        }
        $status = $this->serverApi->optToggleStatus(false);
        $this->message = $this->serverApi->getLastApiMessage();
        return $status;
    }
    public function apiAutodjToggle(): bool
    {
        if ($this->callableAction(__FUNCTION__) == false) {
            return false;
        }
        if ($this->avatar == null) {
            $this->message = "No avatar setup";
            return false;
        }
        if ($this->package == null) {
            $this->message = "No package selected";
            return false;
        }
        if ($this->package->getAutodj() == false) {
            $this->message = "This package does not support autoDJ";
            return false;
        }
        $status = $this->serverApi->optToggleAutodj();
        $this->message = $this->serverApi->getLastApiMessage();
        return $status;
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
        if ($this->avatar == null) {
            // reset username
            return $this->stream->getOriginalAdminUsername();
        }

        // customize username
        $server_accounts = $this->serverApi->accountNameList();
        $this->message = $this->serverApi->getLastApiMessage();
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
        global $retry;
        $retry = false;
        $status = false;
        if ($this->callableAction(__FUNCTION__) == false) {
            return false;
        }
        $stream_state_check = $this->serverApi->StreamState();
        $this->message = $this->serverApi->getLastApiMessage();
        if ($stream_state_check["status"] == false) {
            return false;
        }
        if ($stream_state_check["state"] == true) {
            $retry = true;
        }
        if ($retry == true) {
            $status = $this->serverApi->optToggleStatus(false);
            $this->message = $this->serverApi->getLastApiMessage();
            if ($status == true) {
                $this->message = "Unable to update username right now stopping server!";
            }
            return $status;
        }
        $new_username = $this->getStreamCustomizedUsername();
        if ($new_username == "") {
            $this->message = "No new username found";
            return false;
        }
        $old_username = $this->stream->getAdminUsername();
        if ($old_username == $new_username) {
            $this->message = "No change needed";
            return true;
        }
        $this->stream->setAdminUsername($new_username);
        $update_status = $this->stream->updateEntry();
        if ($update_status["status"] == false) {
            $this->sql->flagError();
            $this->message = "failed to save changes to DB";
            return false;
        }
        $status = $this->serverApi->eventStartSyncUsername($old_username);
        $this->message = $this->serverApi->getLastApiMessage();
        if ($status == false) {
            $this->sql->flagError();
        }
        return $status;
    }
}
