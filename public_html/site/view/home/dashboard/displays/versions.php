<?php
if(file_exists("versions/sql/".$slconfig->get_db_version().".sql") == true)
{
    $main_grid->add_content("<div class=\"alert alert-warning\" role=\"alert\">DB update required <br/> please run \"versions/sql/".$slconfig->get_db_version().".sql\"</div>",12);
}
if(file_exists("versions/about/".$slconfig->get_db_version().".txt") == true)
{
    $main_grid->close_row();
    $main_grid->add_content("<br/>Version: ".$slconfig->get_db_version()."",12);
    $main_grid->add_content(file_get_contents("versions/about/".$slconfig->get_db_version().".txt"),12);
}
else
{
    $main_grid->add_content("Version: ".$slconfig->get_db_version()."",12);
}
?>
