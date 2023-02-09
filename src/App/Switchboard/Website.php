<?php

namespace App\Switchboard;

class Website extends ConfigEnabled
{
    protected string $targetEndpoint = "View";
    protected function accessChecks(): bool
    {
        global $_SERVER;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->targetEndpoint = "Control";
        }
        if ($this->siteConfig->getSession()?->getLoggedIn() === true) {
            return true;
        }
        $this->loadingModule = "Login";
        $this->siteConfig->setModule("Login");
        $allowed_login_areas = ["DefaultView","Logout","Reset","Resetwithtoken","Resetnow","Start"];
        if (in_array($this->siteConfig->getArea(), $allowed_login_areas) == false) {
            $this->loadingArea = "DefaultView";
            $this->siteConfig->setArea("DefaultView");
        }
        return true;
    }
}
