<?php

namespace App\Framework;

use App\Framework\SessionControl;

class Switchboard
{
    protected SessionControl $session;
    protected $module = "";
    protected $page = "";
    protected $option = "";

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
        $fallback = "Error";
        $loadwith = "View";
        if (install_ok() == true) {
            $fallback = "Home";
            if ($this->session->getLoggedIn() == false) {
                $this->module = "Login";
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $loadwith = "Control";
            }
        } else {
            $this->option = $this->module;
            $this->module = "Install";
            include "../App/Flags/InstallMode.php";
        }
        $TargetView = "\\App\\Endpoints\\" . $loadwith . "\\" . $this->module . "\\" . $this->option;
        $DefaultView = "\\App\\Endpoints" . $loadwith . "\\" . $this->module . "\\DefaultView";
        $use_class = "\\App\\Endpoints\\" . $loadwith . "\\" . $fallback . "\\DefaultView";
        if (class_exists($DefaultView) == true) {
            $use_class = $DefaultView;
        }
        if (class_exists($TargetView) == true) {
            $use_class = $TargetView;
        }
        if (class_exists($use_class) == true) {
            $obj = new $use_class();
            $obj->process();
            $obj->getoutput();
        }
    }
}
