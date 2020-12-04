<?php

namespace App\View;

use App\Framework\SessionControl;

class Switchboard
{
    protected SessionControl $session;
    public function __construct()
    {
        global $page, $module, $option, $session, $sql;
        $this->page = &$page;
        $this->module = &$module;
        $this->option = &$option;
        $this->session = &$session;
        $this->module = ucfirst($this->module);
        $this->loadPage();
    }

    protected function loadPage(): void
    {
        if ($this->session->getLoggedIn() == false) {
            $this->module = "Login";
        }
        $fallback = "Error";
        if (install_ok() == false) {
            $this->module = "Install";
        } else {
            $fallback = "Home";
        }
        $TargetView = '\\App\\View\\' . $this->module . '\\' . $this->option;
        $DefaultView = "\\App\\View\\" . $this->module . "\\DefaultView";
        $use_class = "\\App\\View\\" . $fallback . "\\DefaultView";
        if (class_exists($DefaultView) == true) {
            $use_class = $DefaultView;
        }
        if (class_exists($TargetView) == true) {
            $use_class = $TargetView;
        }
        $obj = new $use_class();
        $obj->process();
        $obj->getoutput();
    }
}
