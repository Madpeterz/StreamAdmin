<?php
$input = new inputFilter();
$accept = $input->postFilter("accept");
$redirect ="objects";
$status = false;
if($accept == "Accept")
{
    $objects_set = new objects_set();
    $objects_set->loadAll();
    $purge_status = $objects_set->purge_collection_set();
    if($purge_status["status"] == true)
    {
        $status = true;
        echo $lang["objects.cl.info.1"];
    }
    else
    {
        echo sprintf($lang["objects.cl.error.2"],$purge_status["message"]);
    }
}
else
{
    echo $lang["objects.cl.error.1"];
}
?>
