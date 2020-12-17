<?php

use App\Template\Grid;

$sub_grid_streams = new Grid();
$sub_grid_streams->addContent('<strong>Streams</strong>', 12);
$sub_grid_streams->addContent('<h5><a href="[[url_base]]stream/ready"><span class="badge badge-success">'
. 'Ready <span class="badge badge-light">' . $stream_total_ready . '</span></span></a></h5>', 4);
$sub_grid_streams->addContent('<h5><a href="[[url_base]]stream/needwork"><span class="badge badge-warning">'
. 'Needwork <span class="badge badge-light">' . $stream_total_needwork . '</span></span></a></h5>', 4);
$sub_grid_streams->addContent('<h5><a href="[[url_base]]stream/sold"><span class="badge badge-info">'
. 'Sold <span class="badge badge-light">' . $stream_total_sold . '</span></span></a></h5><br/>', 4);
