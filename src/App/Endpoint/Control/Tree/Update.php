<?php

namespace App\Endpoint\Control\Tree;

use App\Models\Sets\TreevenderSet;
use App\Models\Treevender;
use App\Template\ControlAjax;

class Update extends ControlAjax
{
    public function process(): void
    {
        $name = $this->input->post("name")->checkStringLength(4, 100)->asString();
        if ($name == null) {
            $this->failed("Name failed:" . $this->input->getWhyFailed());
            return;
        }
        $textureWaiting = $this->input->post("textureWaiting")->isUuid()->asString();
        if ($textureWaiting == null) {
            $this->failed("texture waiting is not vaild: " . $this->input->getLastError());
            return;
        }
        $textureInuse = $this->input->post("textureInuse")->isUuid()->asString();
        if ($textureInuse == null) {
            $this->failed("texture inuse is not vaild: " . $this->input->getLastError());
            return;
        }
        $hideSoldout = $this->input->post("hideSoldout")->asBool();
        if ($hideSoldout == null) {
            $hideSoldout = false;
        }
        $this->setSwapTag("redirect", "");
        $treevender = new Treevender();
        if ($treevender->loadID($this->siteConfig->getPage())->status == false) {
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
        $trevenderSet = new TreevenderSet();
        $count_check = $trevenderSet->countInDB($whereConfig);
        $expected_count = 0;
        if ($treevender->getName() == $name) {
            $expected_count = 1;
        }
        if ($count_check->status == false) {
            $this->failed("Unable to check if there is a tree vender assigned already");
            return;
        }
        if ($count_check->items > $expected_count) {
            $this->failed("There is already a tree vender with that name already");
            return;
        }
        $treevender->setName($name);
        $treevender->setHideSoldout($hideSoldout);
        $treevender->setTextureWaiting($textureWaiting);
        $treevender->setTextureInuse($textureInuse);
        $update_status = $treevender->updateEntry();
        if ($update_status->status == false) {
            $this->failed(
                sprintf("Unable to update tree vender:  %1\$s", $update_status->message)
            );
            return;
        }
        $this->redirectWithMessage("Treevender updated");
    }
}
