<?php

if (file_exists("shared/versions/sql/" . $slconfig->getDb_version() . ".sql") == true) {
    $main_grid->addContent("<div class=\"alert alert-warning\" role=\"alert\">DB update required "
    . "<br/> please run \"shared/versions/sql/" . $slconfig->getDb_version() . ".sql\"</div>", 12);
}
if (file_exists("shared/versions/about/" . $slconfig->getDb_version() . ".txt") == true) {
    $main_grid->close_row();
    $main_grid->addContent("<br/>Version: " . $slconfig->getDb_version() . "", 12);
    $main_grid->addContent(file_get_contents("shared/versions/about/" . $slconfig->getDb_version() . ".txt"), 12);
} else {
    $main_grid->addContent("Version: " . $slconfig->getDb_version() . "", 12);
}
