<?php

namespace App\Helpers;

use App\Models\Avatar;
use App\Models\Package;
use App\Models\Rental;
use App\Models\Server;
use App\Models\Stream;
use DateTime;
use YAPF\Framework\Helpers\FunctionHelper;

class SwapablesHelper extends FunctionHelper
{
    public function getSwappedText(
        string $template,
        Avatar $avatar,
        Rental $rental,
        Package $package,
        Server $server,
        Stream $stream
    ): string {
        $av_split = explode(" ", $avatar->_AvatarName);
        if (count($av_split) == 1) {
            $av_split[] = "Resident";
        }
        $true_false = [false => "Disabled", true => "Enabled"][$package->_Autodj];
        $template = str_replace("\n", "", $template);
        $template = str_replace("\r", "", $template); // Normalise the template
        $swaps = [
            "AVATAR_FIRSTNAME" => $av_split[0],
            "AVATAR_LASTNAME" => $av_split[1],
            "AVATAR_FULLNAME" => $avatar->_AvatarName,
            "RENTAL_EXPIRES_DATETIME" => date('l jS \of F Y h:i:s A', $rental->_ExpireUnixtime),
            "RENTAL_TIMELEFT" => $this->timeRemainingHumanReadable($rental->_ExpireUnixtime),
            "STREAM_PORT" => $stream->_Port,
            "STREAM_ADMINUSERNAME" => $stream->_AdminUsername,
            "STREAM_ADMINPASSWORD" => $stream->_AdminPassword,
            "STREAM_DJPASSWORD" => $stream->_DjPassword,
            "STREAM_MOUNTPOINT" => $stream->_Mountpoint,
            "SERVER_DOMAIN" => $server->_Domain,
            "SERVER_IP" => $server->_Ipaddress,
            "SERVER_CONTROLPANEL" => $server->_ControlPanelURL,
            "PACKAGE_NAME" => $package->_Name,
            "PACKAGE_LISTENERS" => $package->_Listeners,
            "PACKAGE_BITRATE" => $package->_Bitrate,
            "PACKAGE_AUTODJ" => $true_false,
            "PACKAGE_AUTODJ_SIZE" => $package->_AutodjSize,
            "NL" => "\n",
            "PACKAGE_UID" => $package->_PackageUid,
            "RENTAL_UID" => $rental->_RentalUid,
            "TIMEZONE" => (new DateTime())->getTimezone()->getName(),
            "FirstName" => $av_split[0],
            "LastName" => $av_split[1],
            "ExpiresAt" => date('l jS \of F Y h:i:s A', $rental->_ExpireUnixtime),
            "TimeLeft" => $this->timeRemainingHumanReadable($rental->_ExpireUnixtime),
            "PortNum" => $stream->_Port,
            "AdminUsername" => $stream->_AdminUsername,
            "AdminPassword" => $stream->_AdminPassword,
            "DjPassword" => $stream->_DjPassword,
            "MountPoint" => $stream->_Mountpoint,
            "Package" => $package->_Name,
            "Users" => $package->_Listeners,
            "Kbps" => $package->_Bitrate,
            "AutoDJ" => $true_false,
            "Disk" => $package->_AutodjSize,
            "PackUID" => $package->_PackageUid,
            "RentUID" => $rental->_RentalUid,
        ];
        foreach ($swaps as $key => $value) {
            if ($value == null) {
                $value  = "";
            }
            $template = str_replace("[[" . $key . "]]", $value, $template);
        }
        return $template;
    }
}
