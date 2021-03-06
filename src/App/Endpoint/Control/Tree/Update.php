<?php

namespace App\Endpoint\Control\Tree;

use App\R7\Model\Treevender;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Update extends ViewAjax
{
    public function process(): void
    {
        $input = new InputFilter();
        $name = $input->postFilter("name");
        $failed_on = "";
        $this->setSwapTag("redirect", "");
        if (strlen($name) < 5) {
            $this->setSwapTag("message", "Name length must be 5 or longer");
            return;
        }
        if (strlen($name) > 100) {
            $this->setSwapTag("message", "Name length must be 100 or less");
            return;
        }
        $treevender = new Treevender();
        if ($treevender->loadID($this->page) == false) {
            $this->setSwapTag("redirect", "tree");
            $this->setSwapTag("message", "Unable to find treevender");
            return;
        }
        $whereConfig = [
            "fields" => ["name"],
            "values" => [$name],
            "types" => ["s"],
            "matches" => ["="],
        ];
        $count_check = $this->sql->basicCountV2($treevender->getTable(), $whereConfig);
        $expected_count = 0;
        if ($treevender->getName() == $name) {
            $expected_count = 1;
        }
        if ($count_check["status"] == false) {
            $this->setSwapTag("message", "Unable to check if there is a tree vender assigned already");
            return;
        }
        if ($count_check["count"] != $expected_count) {
            $this->setSwapTag("message", "There is already a tree vender with that name already");
            return;
        }
        $treevender->setName($name);
        $update_status = $treevender->updateEntry();
        if ($update_status["status"] == false) {
            $this->setSwapTag(
                "message",
                sprintf("Unable to update tree vender: %", $update_status["message"])
            );
            return;
        }
        $this->setSwapTag("status", true);
        $this->setSwapTag("redirect", "tree");
        $this->setSwapTag("message", "Treevender updated");
    }
}
