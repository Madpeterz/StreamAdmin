<?php

use App\Models\Avatar;
use App\Models\Package;
use App\Models\Rental;
use App\Models\Server;
use App\Models\Stream;

class swapables_helper
{
    function get_swapped_text(string $template, Avatar $avatar, Rental $rental, Package $package, Server $server, Stream $stream): string
    {
        global $timezone_name;
          $av_split = explode(" ", $avatar->getAvatarname());
        if (count($av_split) == 1) {
              $av_split[] = "Resident";
        }
          $true_false = [false => "Disabled",true => "Enabled"][$package->getAutodj()];
          $template = str_replace("\n", "", $template);
          $template = str_replace("\r", "", $template); // Normalise the template
          $swaps = [
              "AVATAR_FIRSTNAME" => $av_split[0],
              "AVATAR_LASTNAME" => $av_split[1],
              "AVATAR_FULLNAME" => $avatar->getAvatarname(),
              "RENTAL_EXPIRES_DATETIME" => date('l jS \of F Y h:i:s A', $rental->getExpireunixtime()),
              "RENTAL_TIMELEFT" => timeleft_hours_and_days($rental->getExpireunixtime()),
              "STREAM_PORT" => $stream->getPort(),
              "STREAM_ADMINUSERNAME" => $stream->getAdminusername(),
              "STREAM_ADMINPASSWORD" => $stream->getAdminpassword(),
              "STREAM_DJPASSWORD" => $stream->getDjpassword(),
              "STREAM_MOUNTPOINT" => $stream->getMountpoint(),
              "SERVER_DOMAIN" => $server->getDomain(),
              "SERVER_CONTROLPANEL" => $server->getControlpanel_url(),
              "PACKAGE_NAME" => $package->getName(),
              "PACKAGE_LISTENERS" => $package->getListeners(),
              "PACKAGE_BITRATE" => $package->getBitrate(),
              "PACKAGE_AUTODJ" => $true_false,
              "PACKAGE_AUTODJ_SIZE" => $package->getAutodj_size(),
              "NL" => "\n",
              "PACKAGE_UID" => $package->getPackage_uid(),
              "RENTAL_UID" => $rental->getRental_uid(),
              "TIMEZONE" => $timezone_name,
          ];
          foreach ($swaps as $key => $value) {
                $template = str_replace("[[" . $key . "]]", $value, $template);
          }
          return $template;
    }
}
