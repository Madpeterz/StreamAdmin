<?php

namespace App;

use YAPF\MySQLi\MysqliEnabled as MysqliConnector;
use YAPF\Generator\DbObjectsFactory as DbObjectsFactory;

include "../shared/gen/gen.config.php"; // set config flags
include "../../vendor/autoload.php"; // enable auto load
include "../shared/gen/gen_models_db.php"; // set DB object

// connect to SQL
$sql = new MysqliConnector();

// lets rock
$db_objects_factory = new DbObjectsFactory();
