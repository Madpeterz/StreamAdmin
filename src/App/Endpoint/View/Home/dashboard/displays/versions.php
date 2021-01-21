<?php

if (file_exists("shared/versions/sql/" . $slconfig->getDbVersion() . ".sql") == true) {
    $main_grid->addContent("<div class=\"alert alert-warning\" role=\"alert\">DB update required "
    . "<br/> please run \"shared/versions/sql/" . $slconfig->getDbVersion() . ".sql\"</div>", 12);
}
if (file_exists("shared/versions/about/" . $slconfig->getDbVersion() . ".txt") == true) {
    $main_grid->close_row();
    $main_grid->addContent("<br/>Version: " . $slconfig->getDbVersion() . "", 12);
    $main_grid->addContent(file_get_contents("shared/versions/about/" . $slconfig->getDbVersion() . ".txt"), 12);
} else {
    $main_grid->addContent("Version: " . $slconfig->getDbVersion() . "", 12);
}
