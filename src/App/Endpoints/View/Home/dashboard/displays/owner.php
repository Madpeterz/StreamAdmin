<?php

if ($session->getOwnerLevel() == 1) {
    if ($server_set->getCount() == 0) {
        $main_grid->addContent("<hr/>", 12);
        $main_grid->addContent("<a href=\"[[url_base]]import\"><button class=\"btn btn-info btn-block\" type=\"button\">Import from R4</button></a>", 12);
    }
}
