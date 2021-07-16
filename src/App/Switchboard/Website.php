<?php

namespace App\Switchboard;

class Website extends Switchboard
{
    protected $targetEndpoint = "View";
    protected function accessChecks(): bool
    {
        global $_SERVER;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->targetEndpoint = "Control";
        }
        if (install_ok() == false) {
            $this->targetEndpoint = "View";
            $this->module = "Install";
            include "" . ROOTFOLDER . "/App/Flags/InstallMode.php";
            return true;
        }
        if ($this->session->getLoggedIn() == true) {
            return true;
        }
        $this->module = "Login";
        $allowed_login_areas = ["DefaultView","Logout","Reset","Resetwithtoken","Resetnow","Start"];
        if (in_array($this->area, $allowed_login_areas) == false) {
            $this->area = "DefaultView";
        }
        return true;
    }
    public function __construct()
    {
        global $session;
        $this->session = &$session;
        parent::__construct();
    }
}
