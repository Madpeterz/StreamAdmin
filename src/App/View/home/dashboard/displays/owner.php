<?php

if ($session->get_ownerlevel() == 1) {
    if ($server_set->get_count() == 0) {
        $main_grid->add_content("<hr/>", 12);
        $main_grid->add_content("<a href=\"[[url_base]]import\"><button class=\"btn btn-info btn-block\" type=\"button\">Import from R4</button></a>", 12);
    }
}
