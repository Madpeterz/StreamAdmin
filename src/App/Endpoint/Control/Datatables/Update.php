<?php

namespace App\Endpoint\Control\Datatables;

use App\Models\Datatable;
use App\Framework\ViewAjax;

class Update extends ViewAjax
{
    public function process(): void
    {

        $col = $input->postInteger("col");
        if ($col === null) {
            $this->failed("Col is missing");
            return;
        }
        $acceptedDirs = ["desc","asc"];
        $dir = $this->input->post("dir");
        if (in_array($dir, $acceptedDirs) == false) {
            $this->failed("Dir is not accepted");
            return;
        }

        $datatable = new Datatable();
        if ($datatable->loadID($this->siteConfig->getPage()) == false) {
            $this->failed("Unable to find the datatable config");
            return;
        }
        $datatable->setCol($col);
        $datatable->setDir($dir);
        $update_status = $datatable->updateEntry();
        if ($update_status["status"] == false) {
            $this->failed(sprintf("Unable to update datatable config: %1\$s", $update_status["message"]));
            return;
        }
        $this->ok("Datatable config updated");
    }
}
