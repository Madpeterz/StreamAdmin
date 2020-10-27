<?php
$rental = new rental();
if($rental->load_by_field("rental_uid",$page) == true)
{
    $avatar = new avatar();
    $avatar->load($rental->get_avatarlink());

    $stream = new stream();
    $stream->load($rental->get_streamlink());

    $package = new package();
    $package->load($stream->get_packagelink());

    $server = new server();
    $server->load($stream->get_serverlink());

    $status = true;
    $viewnotecard = "
    Assigned to: [[AVATAR_FULLNAME]][[NL]]
    ===========================[[NL]][[NL]]
    Package: [[PACKAGE_NAME]][[NL]]
    Listeners: [[PACKAGE_LISTENERS]][[NL]]
    Bitrate: [[PACKAGE_BITRATE]]kbps[[NL]]
    ===========================[[NL]][[NL]]
    Control panel: [[SERVER_CONTROLPANEL]][[NL]]
    ip: [[SERVER_DOMAIN]][[NL]]
    port: [[STREAM_PORT]][[NL]]
    ===========================[[NL]][[NL]]
    Admin user: [[STREAM_ADMINUSERNAME]][[NL]]
    Admin pass: [[STREAM_ADMINPASSWORD]][[NL]]
    Encoder/Stream password: [[STREAM_DJPASSWORD]][[NL]]
    ===========================[[NL]][[NL]]
    Expires: [[RENTAL_EXPIRES_DATETIME]]
    ";
    $swapables_helper = new swapables_helper();
    $ajax_reply->set_swap_tag_string("message",$swapables_helper->get_swapped_text($viewnotecard,$avatar,$rental,$package,$server,$stream));
}
else
{
    $status = false;
    $ajax_reply->set_swap_tag_string("message","Unable to load rental");
}

?>
