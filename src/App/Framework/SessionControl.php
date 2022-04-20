<?php

namespace App\Framework;

use App\Models\Staff;
use YAPF\Framework\Core\SQLi\SqlConnectedClass;

class SessionControl extends SqlConnectedClass
{
    protected ?Staff $main_class_object = null;
    protected $logged_in = false;
    protected $session_values = ["lhash","autologout","nextcheck","username","ownerLevel"];
    protected $lhash = "";
    protected $autologout = 0;
    protected $nextcheck = 0;
    protected $username = "";
    protected $ownerLevel = 0;
    public function getOwnerLevel(): bool
    {
        if ($this->ownerLevel == 1) {
            return true;
        }
        return false;
    }
    protected function populateSessionDataset(): bool
    {
        $this->lhash = $this->main_class_object->getLhash();
        $this->autologout = time() + 600;
        $this->nextcheck = time() + 45;
        $this->username = $this->main_class_object->getUsername();
        $this->ownerLevel = $this->main_class_object->getOwnerLevel();
        $this->updateSession();
        return true;
    }
    public function endSession(): void
    {
        global $_SESSION;
        $this->why_logged_out = "Session ended";
        $this->ownerLevel = 0;
        $this->username = "";
        $this->logged_in = false;
        $this->autologout = 0;
        $this->lhash = "";
        $this->nextcheck = 0;
        $_SESSION = [];
        session_destroy();
    }
    protected $why_logged_out = "Not logged in at all";
    public function getWhyLoggedOut(): string
    {
        return $this->why_logged_out;
    }
    protected function createlhash(bool $update_session_after = true): bool
    {
        $this->createMainObject();
        if ($this->main_class_object->isLoaded() == false) {
            return false;
        }
        $new_lhash = $this->hashPassword(time(), rand(1000, 4000), microtime(), $this->main_class_object->getLhash());
        $this->main_class_object->setlhash($new_lhash);
        $this->nextcheck = time() + 120;
        $save_status = $this->main_class_object->updateEntry();
        if ($save_status["status"] == true) {
            $this->lhash = $new_lhash;
            if ($update_session_after == true) {
                $this->updateSession();
                global $sql;
                $sql->sqlSave();
            }
            return true;
        }
        $this->why_logged_out = $save_status["message"];
        return false;
    }
    public function hashPassword(
        string $arg1 = "",
        string $arg2 = "",
        ?string $arg3 = "",
        ?string $arg4 = "",
        int $length = 42
    ): string {
        $newhash = hash("sha256", implode("", [$arg1,$arg2,$arg3,$arg4]));
        if (strlen($newhash) > $length) {
            $newhash = substr($newhash, 0, $length);
        }
        return $newhash;
    }
    protected function vaildatelhash(): bool
    {
        $this->createMainObject();
        if ($this->main_class_object->isLoaded() == false) {
            return false;
        }
        if ($this->lhash == $this->main_class_object->getLhash()) {
            return $this->createlhash(true);
        }
        $this->why_logged_out = "session lhash does not match db";
        return false;
    }
    protected function createMainObject(bool $also_load_object_from_session_lhash = true): void
    {
        if ($this->main_class_object == null) {
            $this->main_class_object = new Staff();
        }
        if ($also_load_object_from_session_lhash == false) {
            return;
        }
        $this->main_class_object->loadByLhash($this->lhash);
    }
    protected function updateSession(): void
    {
        foreach ($this->session_values as $value) {
            $_SESSION[$value] = $this->$value;
        }
    }
    public function getLoggedIn(): bool
    {
        return $this->logged_in;
    }
    public function loadFromSession(): bool
    {
        global $unixtime_hour;
        if (isset($_SESSION) == false) {
            $this->why_logged_out = "Waiting for login";
            return false;
        }

        if (count($_SESSION) == 0) {
            $this->why_logged_out = "Waiting for login";
            return false;
        }

        $required_values_set = true;
        foreach ($this->session_values as $value) {
            if (isset($_SESSION[$value]) == false) {
                $required_values_set = false;
                break;
            }
        }
        if ($required_values_set == false) {
            $this->why_logged_out = "-";
            return false;
        }
        foreach ($this->session_values as $value) {
            $this->$value = $_SESSION[$value];
        }
        if ($this->autologout <= time()) {
            $this->endSession();
            $this->why_logged_out = "Inactive auto logout";
            return false;
        }
        $this->autologout = time() + ($unixtime_hour * 2);
        $this->updateSession();
        $this->logged_in = true;
        if ($this->nextcheck < time()) {
            $this->logged_in = $this->vaildatelhash();
            return $this->logged_in;
        }
        if ($this->logged_in == false) {
            $this->endSession();
            $this->why_logged_out = "Session state error: Not logged in but session active";
        }
        return $this->logged_in;
    }
    /**
     * updatePassword
     * @return mixed[] [status => bool, message=>string,]
     */
    public function updatePassword(string $new_password): array
    {
        if ($this->main_class_object != null) {
            $psalt = $this->hashPassword(
                time(),
                $this->main_class_object->getId(),
                $this->main_class_object->getPsalt(),
                $this->main_class_object->getOwnerLevel()
            );
            $phash = $this->hashPassword(
                $new_password,
                $this->main_class_object->getId(),
                $psalt,
                $this->main_class_object->getOwnerLevel()
            );
            $this->main_class_object->setPsalt($psalt);
            $this->main_class_object->setPhash($phash);
            return $this->main_class_object->updateEntry();
        } else {
            return ["status" => false,"message" => "update_password requires the user object to be loaded!"];
        }
    }
    public function userpasswordCheck(string $input_password): bool
    {
        $this->createMainObject();
        if ($this->main_class_object->isLoaded() == false) {
            return false;
        }
        $check_hash = $this->hashUserPassword($input_password);
        if ($check_hash["status"] == false) {
            return false;
        }
        if ($check_hash["phash"] != $this->main_class_object->getPhash()) {
            return false;
        }
        return true;
    }
    /**
     * hashUserPassword
     * @return mixed[] [status => bool, message=>string, new_salt=>bool, salt_value=> string, phash=> string]
     */
    public function hashUserPassword(string $input_password, bool $create_new_psalt = false): array
    {
        if ($this->main_class_object != null) {
            $p_salt = $this->main_class_object->getPsalt();
            if ($create_new_psalt == true) {
                $p_salt = $this->hashPassword(
                    time(),
                    $this->main_class_object->getId(),
                    $this->main_class_object->getPsalt(),
                    $this->main_class_object->getOwnerLevel()
                );
            }
            return [
            "status" => true,
            "message" => "hashed",
            "new_salt" => $create_new_psalt,
            "salt_value" => $p_salt,
            "phash" => $this->hashPassword(
                $input_password,
                $this->main_class_object->getId(),
                $p_salt,
                $this->main_class_object->getOwnerLevel()
            ),
            ];
        }
        return ["status" => false,"message" => "hash_userpassword requires the user object to be loaded!"];
    }
    public function attachStaffMember(Staff $staff): void
    {
        $this->main_class_object = $staff;
    }
    public function loginWithUsernamePassword(string $username, string $password): bool
    {
        $this->createMainObject(false);
        if ($this->main_class_object->loadByUsername($username) == false) {
            return false;
        }
        if ($this->userPasswordCheck($password) == false) {
            $this->main_class_object = null; // remove link to that account
            return false;
        }
        // login ok build session.
        return $this->populateSessionDataset();
    }
}
