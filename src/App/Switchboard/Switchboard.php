<?php

namespace App\Switchboard;

use App\Endpoint\SecondLifeApi\Apirequests\Next;
use App\Framework\SessionControl;
use YAPF\InputFilter\InputFilter;

abstract class Switchboard
{
    protected $module = "";
    protected $area = "";

    protected $targetEndpoint = "";
    protected SessionControl $session;
    public function __construct()
    {
        global $module, $area;
        $this->module = $module;
        $this->area = $area;
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
        if (($input === "") || ($input === null)) {
            return true;
        }
        return false;
    }

    protected function loadPage(): void
    {
        $input = new InputFilter();
        $usedPostSources = "No";

        $postLoad = $input->postFilter("method");
        if ($this->notSet($postLoad) == false) {
            $this->module = $postLoad;
            $usedPostSources = "Yes";
        }
        $postLoad = $input->postFilter("action");
        if ($this->notSet($postLoad) == false) {
            $this->area = $postLoad;
            $usedPostSources = "Yes";
        }
        $this->module = ucfirst($this->module);
        $this->area = ucfirst($this->area);
        if ($this->notSet($this->module) == true) {
            $this->module = "Home";
        }
        if ($this->accessChecks() == false) {
            return;
        }
        $use_class = "\\App\\Endpoint\\" . $this->targetEndpoint . "\\" . $this->module . "";
        if ($this->module == "") {
            print json_encode(["status" => "0", "message" => "No control module selected!"]);
            return;
        }
        if ($this->area != "") {
            $use_class .= "\\" . $this->area;
        } else {
            $this->area = "DefaultView";
            $use_class .= "\\DefaultView";
        }
        if (class_exists($use_class) == false) {
            $old_use_class = $use_class;
            $use_class = "\\App\\Endpoint\\" . $this->targetEndpoint . "\\DefaultView";
            if (class_exists($use_class) == false) {
                print json_encode(["status" => "0", "message" => "[" . $this->module . " | " . $this->area . "] 
                Unsupported class " . htmlentities($old_use_class)]);
                error_log("Warning unable to load: " . $old_use_class . " and DefaultView is not supported");
                return;
            } else {
                error_log("Warning unable to load: " . $old_use_class . " redirected to DefaultView");
            }
        }

        $obj = new $use_class();
        if ($obj->getLoadOk() == true) {
            $obj->getOutputObject()->setSwapTag("module", $this->module);
            $obj->getOutputObject()->setSwapTag("area", $this->area);
            $obj->getOutputObject()->setSwapTag("UsedPostSources", $usedPostSources);
            $obj->process();
        }
        $obj->getoutput();
    }
}
