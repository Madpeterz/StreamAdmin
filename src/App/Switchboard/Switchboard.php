<?php

namespace App\Switchboard;

use App\Endpoint\SecondLifeApi\Apirequests\Next;
use App\Framework\SessionControl;
use YAPF\InputFilter\InputFilter;

abstract class Switchboard
{
    protected $module = "";
    protected $page = "";
    protected $area = "";
    protected $option = "";

    protected $method = "";
    protected $action = "";
    protected $targetEndpoint = "";
    protected SessionControl $session;
    public function __construct()
    {
        $this->loadPage();
    }

    protected function accessChecks(): bool
    {
        if ((install_ok() == false) && ($this->method != "Install")) {
            print json_encode(["status" => "0", "message" => "Error with setup please contact support"]);
            return false;
        }
        return true;
    }

    protected function notSet(?string $input): bool
    {
        if (($input == "") || ($input == null)) {
            return true;
        }
        return false;
    }

    protected function loadPage(): void
    {
        global $module, $area;
        $this->module = $module;
        $this->area = $area;
        $input = new InputFilter();
        if ($this->notSet($this->method) == true) {
            $this->method = $input->postFilter("method");
        }
        if ($this->notSet($this->action) == true) {
            $this->action = $input->postFilter("action");
        }
        if ($this->notSet($this->method) == true) {
            $this->method = $this->module;
        }
        if ($this->notSet($this->action) == true) {
            $this->action = $this->area;
        }
        if ($this->notSet($this->method) == true) {
            $this->method = "Home";
        }
        $this->method = ucfirst($this->method);
        $this->action = ucfirst($this->action);
        $this->module = $this->method;
        $this->area = $this->action;
        if ($this->accessChecks() == false) {
            return;
        }
        $this->method = ucfirst($this->method);
        $this->action = ucfirst($this->action);

        $use_class = "\\App\\Endpoint\\" . $this->targetEndpoint . "\\" . $this->method . "";
        if ($this->method == "") {
            print json_encode(["status" => "0", "message" => "No control method selected!"]);
            return;
        }
        if ($this->action != "") {
            $use_class .= "\\" . $this->action;
        } else {
            $use_class .= "\\DefaultView";
        }
        if (class_exists($use_class) == false) {
            $old_use_class = $use_class;
            $use_class = "\\App\\Endpoint\\" . $this->targetEndpoint . "\\DefaultView";
            if (class_exists($use_class) == false) {
                print json_encode(["status" => "0", "message" => "[" . $this->method . " | " . $this->action . "] 
                Unsupported class " . htmlentities($old_use_class)]);
                error_log("Warning unable to load: " . $old_use_class . " and DefaultView is not supported");
                return;
            } else {
                error_log("Warning unable to load: " . $old_use_class . " redirected to DefaultView");
            }
        }

        $obj = new $use_class();
        if ($obj->getLoadOk() == true) {
            $obj->getOutputObject()->setSwapTag("method", $this->method);
            $obj->getOutputObject()->setSwapTag("action", $this->action);
            $obj->process();
        }
        $obj->getoutput();
    }
}
