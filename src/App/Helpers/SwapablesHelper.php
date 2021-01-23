<?php

namespace App\Helpers;

use App\Models\Avatar;
use App\Models\Package;
use App\Models\Rental;
use App\Models\Server;
use App\Models\Stream;

class SwapablesHelper
{
    public function getSwappedText(
        string $template,
        Avatar $avatar,
        Rental $rental,
        Package $package,
        Server $server,
        Stream $stream
    ): string {
        global $timezone_name;
          $av_split = explode(" ", $avatar->getAvatarName());
        if (count($av_split) == 1) {
              $av_split[] = "Resident";
        }
          $true_false = [false => "Disabled",true => "Enabled"][$package->getAutodj()];
          $template = str_replace("\n", "", $template);
          $template = str_replace("\r", "", $template); // Normalise the template
          $swaps = [
              "AVATAR_FIRSTNAME" => $av_split[0],
              "AVATAR_LASTNAME" => $av_split[1],
              "AVATAR_FULLNAME" => $avatar->getAvatarName(),
              "RENTAL_EXPIRES_DATETIME" => date('l jS \of F Y h:i:s A', $rental->getExpireUnixtime()),
              "RENTAL_TIMELEFT" => timeleftHoursAndDays($rental->getExpireUnixtime()),
              "STREAM_PORT" => $stream->getPort(),
              "STREAM_ADMINUSERNAME" => $stream->getAdminUsername(),
              "STREAM_ADMINPASSWORD" => $stream->getAdminPassword(),
              "STREAM_DJPASSWORD" => $stream->getDjPassword(),
              "STREAM_MOUNTPOINT" => $stream->getMountpoint(),
              "SERVER_DOMAIN" => $server->getDomain(),
              "SERVER_CONTROLPANEL" => $server->getControlPanelURL(),
              "PACKAGE_NAME" => $package->getName(),
              "PACKAGE_LISTENERS" => $package->getListeners(),
              "PACKAGE_BITRATE" => $package->getBitrate(),
              "PACKAGE_AUTODJ" => $true_false,
              "PACKAGE_AUTODJ_SIZE" => $package->getAutodjSize(),
              "NL" => "\n",
              "PACKAGE_UID" => $package->getPackageUid(),
              "RENTAL_UID" => $rental->getRentalUid(),
              "TIMEZONE" => $timezone_name,
          ];
          foreach ($swaps as $key => $value) {
                $template = str_replace("[[" . $key . "]]", $value, $template);
          }
          return $template;
    }
}
