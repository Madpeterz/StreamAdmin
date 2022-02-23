<?php

namespace App\Endpoint\Control\Tree;

use App\Models\Treevender;
use App\Framework\ViewAjax;

class Update extends ViewAjax
{
    public function process(): void
    {

        $name = $input->postString("name", 100, 4);
        if ($name == null) {
            $this->failed("Name failed:" . $input->getWhyFailed());
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
        $hideSoldout = $input->postBool("hideSoldout");
        if ($hideSoldout == null) {
            $hideSoldout = false;
        }
        $this->setSwapTag("redirect", "");
        $treevender = new Treevender();
        if ($treevender->loadID($this->siteConfig->getPage()) == false) {
            $this->setSwapTag("redirect", "tree");
            $this->failed("Unable to find treevender");
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
            $this->failed("Unable to check if there is a tree vender assigned already");
            return;
        }
        if ($count_check["count"] != $expected_count) {
            $this->failed("There is already a tree vender with that name already");
            return;
        }
        $treevender->setName($name);
        $treevender->setHideSoldout($hideSoldout);
        $treevender->setTextureWaiting($textureWaiting);
        $treevender->setTextureInuse($textureInuse);
        $update_status = $treevender->updateEntry();
        if ($update_status["status"] == false) {
            $this->failed(
                sprintf("Unable to update tree vender:  %1\$s", $update_status["message"])
            );
            return;
        }
        $this->setSwapTag("redirect", "tree");
        $this->ok("Treevender updated");
    }
}
