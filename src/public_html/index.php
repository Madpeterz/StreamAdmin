<?php

namespace App;

include "../App/Framework/load.php";

$loadwith = "App\View\Switchboard";
if (install_ok() == true) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $loadwith = "Control";
    }
} else {
    $method = "Install";
}

$obj = new $loadwith();
