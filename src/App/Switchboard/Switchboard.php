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
        if (install_ok() == false) {
            print json_encode(["status" => "0", "message" => "Setup"]);
            return;
        }
        $use_class = "\\App\\Endpoint\\" . $this->targetEndpoint . "\\" . $this->method . "\\" . $this->action . "";
        if (class_exists($use_class) == false) {
            print json_encode(["status" => "0", "message" => "Not supported"]);
            return;
        }
        $obj = new $use_class();
        if ($obj->getLoadOk() == true) {
            $obj->process();
        }
        $obj->getoutput();
    }
}
