<?php

namespace App\Endpoint\View\Export;

use App\R7\Set\AvatarSet;
use App\R7\Set\PackageSet;
use App\R7\Set\RentalSet;
use App\R7\Set\ServerSet;
use App\R7\Set\StreamSet;
use App\Template\ExcelSheet;

class Flow1 extends ExcelSheet
{
    protected $deleleted_entrys = 0;
    public function process(): void
    {
        if ($this->session->getOwnerLevel() != 1) {
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
        $avatars = new AvatarSet();
        $avatars->loadByValues($clients->getAllByField("avatarLink"));

        $worksheetData = $this->spreadsheet->createSheet();
        $worksheetData->setTitle("Data");
        $worksheetData->getTabColor()->setRGB('0000FF');

        $dataBlob = [
            ["ID","Customer Name","Port Number","Stream Password","Admin Username","Admin Password",
            "URL","Package Name","Comments","Expires at"],
        ];

        foreach ($streams as $stream) {
            $entry = [];

            $server = $servers->getObjectByID($stream->getServerLink());
            $clientName = "Available";
            $comments = "None";
            $packageName = "????";
            $expiresAt = "";
            if ($stream->getNeedWork() == true) {
                $clientName = "- Needs work -";
            }
            $rental = $clients->getObjectByField("streamLink", $stream->getId());
            if ($rental != null) {
                if (strlen($rental->getMessage()) > 0) {
                    $comments = $rental->getMessage();
                }
                $clientName = "-Taken-";
                $avatar = $avatars->getObjectByID($rental->getAvatarLink());
                if ($avatar != null) {
                    $clientName = $avatar->getAvatarName();
                }
                $expiresAt = "Expired";
                if ($rental->getExpireUnixtime() > time()) {
                    $expiresAt = date('d/m/Y @ G:i:s', $rental->getExpireUnixtime());
                }
            }
            $package = $packages->getObjectByID($stream->getPackageLink());
            if ($package != null) {
                $packageName = $package->getName();
            }


            $entry[] = $stream->getId();
            $entry[] = $clientName;
            $entry[] = $stream->getPort();
            $entry[] = $stream->getDjPassword();
            $entry[] = $stream->getAdminUsername();
            $entry[] = $stream->getAdminPassword();
            $entry[] = $server->getDomain() . ":" . $stream->getPort();
            $entry[] = $packageName;
            $entry[] = $comments;
            $entry[] = $expiresAt;
            $dataBlob[] = $entry;
        }

        $worksheetData->fromArray($dataBlob, "", "A1");
        unset($dataBlob);
    }
}
