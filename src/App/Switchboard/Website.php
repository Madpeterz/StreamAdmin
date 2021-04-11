<?php

namespace App\Switchboard;

class Website extends Switchboard
{
    protected function accessChecks(): bool
    {
        global $_SERVER;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->targetEndpoint = "Control";
        }
        if (install_ok() == false) {
            $this->method = "Install";
            $this->module = "Install";
            include "" . ROOTFOLDER . "/App/Flags/InstallMode.php";
            return true;
        }
        if ($this->session->getLoggedIn() == true) {
            return true;
        }
        $this->method = "Login";
        $this->module = "Login";
        $allowed_login_areas = ["DefaultView","Logout","Reset","Resetwithtoken","Resetnow","Start"];
        if (in_array($this->action, $allowed_login_areas) == false) {
            $this->action = "DefaultView";
            $this->area = "DefaultView";
        }
        return true;
    }
    public function __construct()
    {
        global $page, $session;
        $this->page = $page;
        $this->session = &$session;
        $this->targetEndpoint = "View";
        $this->loadPage();
    }
}
