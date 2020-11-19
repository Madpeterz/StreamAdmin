<?php

class reseller_helper
{
    protected $reseller = null;
    function get_reseller(): reseller
    {
        return $this->reseller;
    }
    function load_or_create(int $avatarlinkid, bool $auto_accept = false, int $auto_accept_rate = 0, bool $show_errors = false): bool
    {
        $this->reseller = new reseller();
        if ($avatarlinkid > 0) {
            if ($this->reseller->loadByField("avatarlink", $avatarlinkid) == true) {
                return true;
            } else {
                $this->reseller = new reseller();
                $this->reseller->set_avatarlink($avatarlinkid);
                $this->reseller->set_allowed($auto_accept);
                $this->reseller->set_rate($auto_accept_rate);
                $save_status = $this->reseller->create_entry();
                if ($save_status["status"] == false) {
                    if ($show_errors == true) {
                        echo "[Reseller_helper] - Unable to create reseller entry because: " . $save_status["message"] . "";
                    }
                }
                return $save_status["status"];
            }
        } else {
            if ($show_errors == true) {
                echo "[Reseller_helper] - Reseller avatar link id needs to be 1 or more! - Given value: " . $avatarlinkid . "";
            }
        }
        return false;
    }
}
