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
        $name = $input->postString("name", 100, 5);
        if ($name == null) {
            $this->failed("Name is not vaild: " . $input->getLastError());
            return;
        }
        if ($treevender->loadByField("name", $name) == true) {
            $this->failed("There is already a tree vender assigned to that name");
            return;
        }
        $textureWaiting = $input->postUUID("textureWaiting");
        if ($textureWaiting == null) {
            $this->failed("texture waiting is not vaild: " . $input->getLastError());
            return;
        }
        $textureInuse = $input->postUUID("textureInuse");
        if ($textureInuse == null) {
            $this->failed("texture inuse is not vaild: " . $input->getLastError());
            return;
        }

        $treevender = new Treevender();
        $treevender->setName($name);
        $treevender->setTextureWaiting($textureWaiting);
        $treevender->setTextureInuse($textureInuse);
        $create_status = $treevender->createEntry();
        if ($create_status["status"] == false) {
            $this->setSwapTag(
                "message",
                sprintf("Unable to create tree vender: %1\$s", $create_status["message"])
            );
            return;
        }
        $this->ok("Tree vender created");
        $this->setSwapTag("redirect", "tree");
    }
}
