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
        $name = $input->postString("name", 100, 4);
        if ($name == null) {
            $this->failed("Name is not vaild: " . $input->getLastError());
            return;
        }
        if ($treevender->loadByField("name", $name) == true) {
            $this->failed("There is already a tree vender assigned to that name");
            return;
        }

        $treevender = new Treevender();
        $treevender->setName($name);
        $treevender->setTextureWaiting("00000000-0000-0000-0000-000000000000");
        $treevender->setTextureInuse("00000000-0000-0000-0000-000000000000");
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
