<?php

$main_grid->addContent("<br/>", 12);
$main_grid->close_row();
$main_grid->addContent($sub_grid_streams->getOutput(), 6);
$main_grid->addContent($sub_grid_clients->getOutput(), 6);
$main_grid->addContent("<br/>", 12);
$main_grid->close_row();
$main_grid->addContent($sub_grid_servers->getOutput(), 6);
$main_grid->addContent($sub_grid_objects->getOutput(), 6);
$main_grid->close_row();
