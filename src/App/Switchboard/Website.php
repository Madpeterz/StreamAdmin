<?php

namespace App\Switchboard;

class Website extends Switchboard
{
    public function __construct()
    {
        global $page, $session, $_SERVER;
        $this->page = $page;
        $this->session = &$session;
        $this->targetEndpoint = "View";
        $this->module = ucfirst($this->module);
        $this->area = ucfirst($this->area);
        if (install_ok() == true) {
            if ($this->session->getLoggedIn() == false) {
                $this->module = "Login";
                $allowed_login_areas = ["DefaultView","Logout","Reset","Resetwithtoken","Resetnow","Start"];
                if (in_array($this->area, $allowed_login_areas) == false) {
                    $this->area = "DefaultView";
                }
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->targetEndpoint = "Control";
            }
        } else {
            $this->module = "Install";
            include "" . ROOTFOLDER . "/App/Flags/InstallMode.php";
        }
        if ($this->module == "") {
            $this->module = "Home";
        }
        $this->loadPage();
    }
}
