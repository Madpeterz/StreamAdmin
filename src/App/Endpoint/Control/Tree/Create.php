<?php

namespace App\Endpoint\Control\Tree;

use App\R7\Model\Treevender;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Create extends ViewAjax
{
    public function process(): void
    {
        $treevender = new Treevender();
        $input = new InputFilter();
        $name = $input->postFilter("name");
        if (strlen($name) < 5) {
            $this->setSwapTag("message", "Name length must be 5 or longer");
            return;
        }
        if (strlen($name) > 100) {
            $this->setSwapTag("message", "Name length must be 200 or less");
            return;
        }
        if ($treevender->loadByField("name", $name) == true) {
            $this->setSwapTag("message", "There is already a tree vender assigned to that name");
            return;
        }
        $treevender = new Treevender();
        $treevender->setName($name);
        $create_status = $treevender->createEntry();
        if ($create_status["status"] == false) {
            $this->setSwapTag(
                "message",
                sprintf("Unable to create tree vender: %1\$s", $create_status["message"])
            );
            return;
        }
        $this->setSwapTag("status", true);
        $this->setSwapTag("redirect", "tree");
        $this->setSwapTag("message", "Tree vender created");
    }
}
