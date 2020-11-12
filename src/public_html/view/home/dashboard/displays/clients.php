<?php

$sub_grid_clients = new grid();
$sub_grid_clients->add_content('<strong>Clients</strong>', 12);
$sub_grid_clients->add_content('<h5><a href="[[url_base]]client/expired"><span class="badge badge-danger">Expired <span class="badge badge-light">' . $client_expired . '</span></span></a></h5>', 4);
$sub_grid_clients->add_content('<h5><a href="[[url_base]]client/soon"><span class="badge badge-warning">Expires in 24 hours <span class="badge badge-light">' . $client_expires_soon . '</span></span></a></h5>', 4);
$sub_grid_clients->add_content('<h5><a href="[[url_base]]client/ok"><span class="badge badge-success">Ok <span class="badge badge-light">' . $client_ok . '</span></span></a></h5><br/>', 4);
