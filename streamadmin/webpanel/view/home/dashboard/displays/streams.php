<?php
$sub_grid_streams = new grid();
$sub_grid_streams->add_content('<strong>Streams</strong>',12);
$sub_grid_streams->add_content('<h5><a href="[[url_base]]stream/ready"><span class="badge badge-success">Ready <span class="badge badge-light">'.$stream_total_ready.'</span></span></a></h5>',4);
$sub_grid_streams->add_content('<h5><a href="[[url_base]]stream/needwork"><span class="badge badge-warning">Needwork <span class="badge badge-light">'.$stream_total_needwork.'</span></span></a></h5>',4);
$sub_grid_streams->add_content('<h5><a href="[[url_base]]stream/sold"><span class="badge badge-info">Sold <span class="badge badge-light">'.$stream_total_sold.'</span></span></a></h5><br/>',4);
?>
