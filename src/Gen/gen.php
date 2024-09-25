<?php

namespace App;

use YAPF\Framework\Generator\DbObjectsFactory;

include "../../vendor/autoload.php";
include "gen.config.php";
include "gen_db.php";

$system = new Config();
new DbObjectsFactory(rebuildOutputFolders: true);
