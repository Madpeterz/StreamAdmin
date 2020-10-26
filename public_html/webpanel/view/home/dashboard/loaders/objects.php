<?php
$objects = new objects();
$one_hour_ago = (time()-$unixtime_hour);
$countdata_objects = $sql->group_count($objects->get_table(),"objectmode",array(array("lastseen"=>">=")),array(array($one_hour_ago=>"i")));
?>
