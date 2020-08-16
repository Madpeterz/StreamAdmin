<?php
$banlist = new banlist();
if($banlist->load($page) == true)
{
    $remove_status = $banlist->remove_me();
    if($remove_status["status"] == true)
    {
        redirect("banlist?message=entry removed");
    }
    else
    {
        redirect("banlist?message=Unable to remove entry");
    }
}
else
{
    redirect("banlist?message=unable to find entry");
}
?>
