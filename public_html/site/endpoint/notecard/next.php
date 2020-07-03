<?php
$load_ok = false;
$notecard = new notecard();
$rental = new rental();
$package = new package();
$avatar = new avatar();
$template = new template();
$stream = new stream();
$server = new server();
$notice = new notice();
$load_by = array(
    "rental" => array("notecard"=>"rentallink"),
    "avatar" => array("rental"=>"avatarlink"),
    "stream" => array("rental"=>"streamlink"),
    "server" => array("stream"=>"serverlink"),
    "package" => array("stream"=>"packagelink"),
);
$notecard_title = "";
$notecard_content = "";
if($notecard->get_as_notice() == false)
{
    $load_by["template"] = array("package"=>"templatelink");
}
else
{
    $load_by["notice"] = array("rental"=>"noticelink");
}
$notecard_set = new notecard_set();
$notecard_set->load_newest(1,array(),array(),"id","ASC"); // lol loading oldest with newest command ^+^ hax
if($notecard_set->get_count() > 0)
{
    $notecard = $notecard_set->get_first();
    $load_ok = true;
    foreach($load_by as $objectname => $value)
    {
        foreach($value as $source => $linkon)
        {
            $object = $$objectname;
            $loadfromobject = $$source;
            $loadfromfunction = "get_".$linkon."";
            if($object->load($loadfromobject->$loadfromfunction()) == false)
            {
                $load_ok = false;
                break;
            }
        }
    }
}
if($load_ok == true)
{
    if($notecard->get_as_notice() == false)
    {
        $notecard_title = "Streamdetails for ".$avatar->get_avatarname()." port: ".$stream->get_port()."";
        $notecard_content = $template->get_notecarddetail();
    }
    else
    {
        $notecard_title = "Reminder for ".$avatar->get_avatarname()." port: ".$stream->get_port()."";
        $notecard_content = $notice->get_notecarddetail();
    }
    $swap_helper = new swapables_helper();
    $notecard_content = $swap_helper->get_swapped_text($notecard_content,$avatar,$rental,$package,$server,$stream);
    $remove_status = $notecard->remove_me();
    if($remove_status["status"] == true)
    {
        $reply = array(
            "status"=>true,
            "message"=>"ok",
            "AvatarUUID"=>$avatar->get_avataruuid(),
            "NotecardTitle"=>$notecard_title,
            "NotecardContent"=>$notecard_content
        );
    }
    else
    {
        $reply = array("status"=>false,"message"=>"Unable to load notecard right now");
    }
}
else
{
    $reply = array("status"=>false,"message"=>"Unable to load notecard right now");
}
?>
