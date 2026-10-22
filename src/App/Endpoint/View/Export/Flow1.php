<?php

namespace App\Endpoint\View\Export;

use App\Models\Set\PackageSet;
use App\Models\Set\RentalSet;
use App\Models\Set\ServerSet;
use App\Models\Set\StreamSet;
use App\Template\ExcelSheet;

class Flow1 extends ExcelSheet
{
    protected $deleleted_entrys = 0;
    public function process(): void
    {
        if ($this->siteConfig->getSession()->getOwnerLevel() != 1) {
            $this->asAjax = true;
            $this->failed("Only the system owner can access this area");
            $this->setSwapTag("redirect", "");
            return;
        }
        $this->makeSheet();
    }

    public function makeSheet(): void
    {
        $this->filename = date("YmdHis", time()) . "-streamadmin.xlsx";
        $this->spreadsheet->getProperties()
        ->setCreator("Streamadmin R7")
        ->setLastModifiedBy("Endpoint/Control/Excel/Flow1")
        ->setTitle("Streamadmin data")
        ->setSubject("A snapshot of non SQL data")
        ->setDescription(
            "Created at " . date("D M j G:i:s T Y")
        )
        ->setKeywords("streamadmin streams clients ports")
        ->setCategory("Data blob");
        $streams = new StreamSet();
        $streams->loadAll();
        $clients = new RentalSet();
        $clients->loadAll();
        $servers = new ServerSet();
        $servers->loadAll();
        $packages = new PackageSet();
        $packages->loadAll();
        $avatars = $clients->relatedAvatar();

        $worksheetData = $this->spreadsheet->createSheet();
        $worksheetData->setTitle("Data");
        $worksheetData->getTabColor()->setRGB('0000FF');

        $dataBlob = [
            ["ID","Customer Name","Port Number","Stream Password","Admin Username","Admin Password",
            "URL","Package Name","Comments","Expires at"],
        ];

        foreach ($streams as $stream) {
            $entry = [];

            $server = $servers->getObjectByID($stream->_ServerLink);
            $clientName = "Available";
            $comments = "None";
            $packageName = "????";
            $expiresAt = "";
            if ($stream->_NeedWork == true) {
                $clientName = "- Needs work -";
            }
            $rental = $clients->getObjectByField("streamLink", $stream->_Id);
            if ($rental != null) {
                if (nullSafeStrLen($rental->_Message) > 0) {
                    $comments = $rental->_Message;
                }
                $clientName = "-Taken-";
                $avatar = $avatars->getObjectByID($rental->_AvatarLink);
                if ($avatar != null) {
                    $clientName = $avatar->_AvatarName;
                }
                $expiresAt = "Expired";
                if ($rental->_ExpireUnixtime > time()) {
                    $expiresAt = date('d/m/Y @ G:i:s', $rental->_ExpireUnixtime);
                }
            }
            $package = $packages->getObjectByID($stream->_PackageLink);
            if ($package != null) {
                $packageName = $package->_Name;
            }


            $entry[] = $stream->_Id;
            $entry[] = $clientName;
            $entry[] = $stream->_Port;
            $entry[] = $stream->_DjPassword;
            $entry[] = $stream->_AdminUsername;
            $entry[] = $stream->_AdminPassword;
            $entry[] = $server->_Domain . ":" . $stream->_Port;
            $entry[] = $packageName;
            $entry[] = $comments;
            $entry[] = $expiresAt;
            $dataBlob[] = $entry;
        }

        $worksheetData->fromArray($dataBlob, "", "A1");
        unset($dataBlob);
    }
}
