<?php
class bot_helper
{
    function send_bot_command(botconfig $botconfig,string $command,array $args) : string
    {
        $raw = "".$command."".implode("~#~",$args)."".$botconfig->get_secret();
        $cooked = sha1($raw);
        global $reply;
        $reply["raw"] = $raw;
        $reply["cooked"] = $cooked;
        return "".$command."|||".implode("~#~",$args)."@@@".$cooked."";
    }
      function send_message(botconfig $botconfig,avatar $botavatar,avatar $avatar,string $message,bool $allow_bot=false)
      {
          $reply_status = true;
          $why_failed = "No idea";
          if($allow_bot == true)
          {
              if($botconfig->get_ims() == true)
              {
                  $bot_send_message = $this->send_bot_command($botconfig,"im",array($avatar->get_avataruuid(),$message));
                  $status = $this->send_message_to_avatar($botavatar,$bot_send_message);
                  if($status["status"] == false)
                  {
                      $reply_status = false;
                      $why_failed = $status["message"];
                  }
              }
          }
          if($reply_status == true)
          {
              return $this->send_message_to_avatar($avatar,$message);
          }
          else
          {
              return array("status"=>false,"message"=>$why_failed);
          }
      }
      function send_message_to_avatar(avatar $avatar,string $sendmessage) : array
      {
          $message = new message();
          $message->set_field("avatarlink",$avatar->get_id());
          $message->set_field("message",$sendmessage);
          return $message->create_entry();
      }
}
?>
