<?php

namespace App\Endpoint\Control\Datatables;

use App\Models\Datatable;
use App\Template\ControlAjax;

class Update extends ControlAjax
{
    public function process(): void
    {

        $col = $this->input->post("col")->checkGrtThanEq(1)->asInt();
        if ($col === null) {
            $this->failed("Col is missing");
            return;
        }
        $acceptedDirs = ["desc","asc"];
        $dir = $this->input->post("dir")->asString();
        if (in_array($dir, $acceptedDirs) == false) {
            $this->failed("Dir is not accepted");
            return;
        }

        $datatable = new Datatable();
        if ($datatable->loadID($this->siteConfig->getPage())->status == false) {
            $this->failed("Unable to find the datatable config");
            return;
        }
        $oldvalues = $datatable->objectToValueArray();
        $datatable->setCol($col);
        $datatable->setDir($dir);
        $update_status = $datatable->updateEntry();
        if ($update_status->status == false) {
            $this->failed(sprintf("Unable to update datatable config: %1\$s", $update_status->message));
            return;
        }
        $this->redirectWithMessage("Datatable config updated");
        $this->createMultiAudit(
            $datatable->getId(),
            $datatable->getFields(),
            $oldvalues,
            $datatable->objectToValueArray()
        );
    }
}
