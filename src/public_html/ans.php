<?php

namespace App;

use App\Switchboard\Ans;

include "../App/Framework/Load.php";

@ini_set('display_errors', "off");
@ini_set('log_errors', "on");
@ini_set('error_log', '/proc/self/fd/2');

new Ans();
