<?php

namespace App\Switchboard;

class Website extends Switchboard
{
    public function __construct()
    {
        global $page, $module, $area, $session, $_SERVER;
        $this->page = $page;
        $this->module = $module;
        $this->area = $area;
        $this->session = &$session;
        $this->targetEndpoint = "View";
        $this->module = ucfirst($this->module);
        $this->area = ucfirst($this->area);
        if (install_ok() == true) {
            if ($this->session->getLoggedIn() == false) {
                $this->module = "Login";
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->targetEndpoint = "Control";
            }
        } else {
            $this->module = "Install";
            include "" . ROOTFOLDER . "/App/Flags/InstallMode.php";
        }
        $this->loadPage();
    }
}
