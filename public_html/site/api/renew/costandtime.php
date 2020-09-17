<?php
$input = new inputFilter();
$rental_uid = $input->postFilter("rental_uid");
$rental = new rental();
$status = false;
if($rental->load_by_field("rental_uid",$rental_uid) == true)
{
    $stream = new stream();
    if($stream->load($rental->get_streamlink()) == true)
    {
        $package = new package();
        if($package->load($stream->get_packagelink()) == true)
        {
            $status = true;
            $reply["cost"] = $package->get_cost();
            print timeleft_hours_and_days($rental->get_expireunixtime());
        }
        else
        {
            print $lang["renew.cat.error.3"];
        }
    }
    else
    {
        print $lang["renew.cat.error.2"];
    }
}
else
{
    print $lang["renew.cat.error.1"];
}
?>
