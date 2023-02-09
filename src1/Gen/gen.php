<?php

namespace App;

use YAPF\Framework\Config\SimpleConfig;
use YAPF\Framework\Generator\DbObjectsFactory;

include "../../vendor/autoload.php";
include "gen.config.php";
include "gen_db.php";

$system = new SimpleConfig();
$db_objects_factory = new DbObjectsFactory(false);
$db_objects_factory->enableConsoleErrors();
$db_objects_factory->start();
