<?php
class swapables_helper
{
      function get_swapped_text(string $template,avatar $avatar,rental $rental,package $package,server $server,stream $stream) : string
      {
            $av_split = explode(" ",$avatar->get_avatarname());
            if(count($av_split) == 1)
            {
                  $av_split[] = "Resident";
            }
            $true_false = array(false=>"Disabled",true=>"Enabled")[$package->get_autodj()];
            $template = str_replace("\n","",$template);
            $template = str_replace("\r","",$template); // Normalise the template
            $swaps = array(
                "AVATAR_FIRSTNAME" => $av_split[0],
                "AVATAR_LASTNAME" => $av_split[1],
                "AVATAR_FULLNAME" => $avatar->get_avatarname(),
                "RENTAL_EXPIRES_DATETIME" => date('l jS \of F Y h:i:s A',$rental->get_expireunixtime()),
                "RENTAL_TIMELEFT" => timeleft_hours_and_days($rental->get_expireunixtime()),
                "STREAM_PORT" => $stream->get_port(),
                "STREAM_ADMINUSERNAME" => $stream->get_adminusername(),
                "STREAM_ADMINPASSWORD" => $stream->get_adminpassword(),
                "STREAM_DJPASSWORD" => $stream->get_djpassword(),
                "STREAM_MOUNTPOINT" => $stream->get_mountpoint(),
                "SERVER_DOMAIN" => $server->get_domain(),
                "SERVER_CONTROLPANEL" => $server->get_controlpanel_url(),
                "PACKAGE_NAME" => $package->get_name(),
                "PACKAGE_LISTENERS" => $package->get_listeners(),
                "PACKAGE_BITRATE" => $package->get_bitrate(),
                "PACKAGE_AUTODJ" => $true_false,
                "PACKAGE_AUTODJ_SIZE" => $package->get_autodj_size(),
                "NL" => "\n",
                "PACKAGE_UID" => $package->get_package_uid(),
                "RENTAL_UID" => $rental->get_rental_uid(),
            );
            foreach($swaps as $key => $value)
            {
                  $template = str_replace("[[".$key."]]",$value,$template);
            }
            return $template;
      }
}
?>
