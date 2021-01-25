<?php

namespace App\Framework;

use App\Models\Staff;
use YAPF\Core\SqlConnectedClass as SqlConnectedClass;

class SessionControl extends SqlConnectedClass
{
    protected ?Staff $main_class_object = null;
    protected $logged_in = false;
    protected $session_values = ["lhash","autologout","nextcheck","username","ownerLevel"];
    protected $lhash = 0;
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
        session_regenerate_id(true);
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
        $this->why_logged_out = "Session ended";
        session_destroy();
    }
    protected $why_logged_out = "Not logged in at all";
    public function getWhyLoggedOut(): string
    {
        return $this->why_logged_out;
    }
    protected function createlhash(bool $update_session_after = true): bool
    {
        if ($this->createMainObject() == false) {
            $this->why_logged_out = "Unable to create root session object";
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
        if ($this->createMainObject() == false) {
            $this->why_logged_out = "Unable to create root session object";
            return false;
        }
        if ($this->lhash == $this->main_class_object->getLhash()) {
            return $this->createlhash(true);
        }
        $this->why_logged_out = "session lhash does not match db";
        return false;
    }
    protected function createMainObject(bool $also_load_object_from_session_lhash = true): bool
    {
        if ($this->main_class_object == null) {
            $this->main_class_object = new staff();
        }
        $load_ok = true;
        if ($also_load_object_from_session_lhash == true) {
            if ($this->main_class_object->getId() == null) {
                $load_ok = $this->main_class_object->loadByField("lhash", $this->lhash);
            }
        }
        return $load_ok;
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
        $this->autologout = time() + 3600;
        $this->updateSession();
        $this->logged_in = true;
        if ($this->nextcheck < time()) {
            $this->logged_in = $this->vaildatelhash();
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
        if ($this->createMainObject(true) == true) {
            $expected_hash = null;
            if ($expected_hash == null) {
                $expected_hash = $this->main_class_object->getPhash();
            }
            $check_hash = $this->hashUserPassword($input_password);
            if ($check_hash["status"] == true) {
                if ($check_hash["phash"] == $expected_hash) {
                    return true;
                }
            }
        }
        return false;
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
        if ($this->createMainObject(false) == true) {
            if ($this->main_class_object->loadByField("username", $username) == true) {
                if ($this->userPasswordCheck($password) == true) {
                    // login ok build session.
                    return $this->populateSessionDataset();
                } else {
                    $this->main_class_object = null; // remove link to that account
                }
            }
        }
        return false;
    }
}
