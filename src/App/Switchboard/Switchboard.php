<?php

namespace App\Switchboard;

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
        $input = new InputFilter();
        $this->method = $input->postFilter("method");
        $this->action = $input->postFilter("action");
        $this->loadPage();
    }

    protected function loadPage(): void
    {
        if ($this->method == "") {
            $this->method = $this->module;
        }
        if ($this->action == "") {
            $this->action = $this->area;
        }
        $this->method = ucfirst($this->method);
        $this->action = ucfirst($this->action);
        if ((install_ok() == false) && ($this->method != "Install")) {
            print json_encode(["status" => "0", "message" => "Error with setup please contact support"]);
            return;
        }
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
            print json_encode(["status" => "0", "message" => "[" . $this->method . " | " . $this->action . "] 
            Unsupported class " . htmlentities($use_class)]);
            return;
        }

        $obj = new $use_class();
        if ($obj->getLoadOk() == true) {
            $obj->process();
        }
        $obj->getoutput();
    }
}
