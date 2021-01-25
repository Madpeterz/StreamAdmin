<?php

namespace App\Framework;

use App\Framework\SessionControl;
use App\Template\BasicView;

class Switchboard
{
    protected SessionControl $session;
    protected $module = "";
    protected $page = "";
    protected $area = "";
    protected $option = "";

    public function __construct()
    {
        global $page, $module, $area, $session, $optional;
        $this->page = $page;
        $this->module = $module;
        $this->area = $area;
        $this->session = &$session;

        $this->module = ucfirst($this->module);
        $this->area = ucfirst($this->area);
        $this->loadPage();
    }
    protected function getTargetModulue(string $loadwith, string $fallback): string
    {
        $TargetView = "\\App\\Endpoint\\" . $loadwith . "\\" . $this->module;
        if ($this->area != "") {
            $TargetView .= "\\" . $this->area;
        }
        $DefaultView = "\\App\\Endpoint\\" . $loadwith . "\\" . $this->module . "\\DefaultView";
        $use_class = "\\App\\Endpoint\\" . $loadwith . "\\" . $fallback . "\\DefaultView";
        if (class_exists($TargetView) == true) {
            return $TargetView;
        }
        if (class_exists($DefaultView) == true) {
            return $DefaultView;
        }
        return $use_class;
    }
    protected function getClassObject(string $requestClass): BasicView
    {
        return new $requestClass();
    }
    protected function loadPage(): void
    {
        global $slconfig;
        $fallback = "Error";
        $loadwith = "View";
        if (install_ok() == true) {
            $fallback = "Home";
            if ($this->session->loadFromSession() == true) {
                if ($this->session->getLoggedIn() == false) {
                    $this->module = "Login";
                }
            } else {
                $this->module = "Login";
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $loadwith = "Control";
            }
        } else {
            $this->module = "Install";
            include "../App/Flags/InstallMode.php";
        }
        $use_class = $this->getTargetModulue($loadwith, $fallback);
        if (class_exists($use_class) == true) {
            $obj = $this->getClassObject($use_class);
            $obj->process();
            $obj->getoutput();
        }
    }
}
