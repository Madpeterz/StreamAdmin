<?php

namespace App\Endpoint\Control\Tree;

use App\Models\Treevender;
use App\Template\ControlAjax;

class Create extends ControlAjax
{
    public function process(): void
    {
        $treevender = new Treevender();

        $name = $this->input->post("name")->checkStringLength(4, 100)->asString();
        if ($name == null) {
            $this->failed("Name is not vaild: " . $this->input->getLastError());
            return;
        }
        if ($treevender->loadByName($name)->status == true) {
            $this->failed("There is already a tree vender assigned to that name");
            return;
        }

        $treevender = new Treevender();
        $treevender->setName($name);
        $treevender->setTextureWaiting("00000000-0000-0000-0000-000000000000");
        $treevender->setTextureInuse("00000000-0000-0000-0000-000000000000");
        $create_status = $treevender->createEntry();
        if ($create_status->status == false) {
            $this->setSwapTag(
                "message",
                sprintf("Unable to create tree vender: %1\$s", $create_status->message)
            );
            return;
        }
        $this->redirectWithMessage("Tree vender created");
        $this->createAuditLog($treevender->getId(), "+++", null, $treevender->getName());
    }
}
