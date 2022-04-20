<?php

namespace App\Switchboard;

use YAPF\Bootstrap\Switchboard\Switchboard;

class Website extends Switchboard
{
    protected $targetEndpoint = "View";
    protected function accessChecks(): bool
    {
        global $_SERVER;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->targetEndpoint = "Control";
        }
        if ($this->siteConfig->getSession()?->getLoggedIn() == true) {
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
