<?php

class swapables_helper
{
    function get_swapped_text(string $template, avatar $avatar, rental $rental, package $package, server $server, stream $stream): string
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
              "RENTAL_EXPIRES_DATETIME" => date('l jS \of F Y h:i:s A', $rental->get_expireunixtime()),
              "RENTAL_TIMELEFT" => timeleft_hours_and_days($rental->get_expireunixtime()),
              "STREAM_PORT" => $stream->get_port(),
              "STREAM_ADMINUSERNAME" => $stream->get_adminusername(),
              "STREAM_ADMINPASSWORD" => $stream->get_adminpassword(),
              "STREAM_DJPASSWORD" => $stream->get_djpassword(),
              "STREAM_MOUNTPOINT" => $stream->get_mountpoint(),
              "SERVER_DOMAIN" => $server->get_domain(),
              "SERVER_CONTROLPANEL" => $server->get_controlpanel_url(),
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
