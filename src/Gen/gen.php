<?php

namespace App;

use YAPF\Framework\Generator\DbObjectsFactory;

include "../../vendor/autoload.php";
include "gen_db.php";

$system = new Config();

$GEN_DATABASE_HOST = "localhost";
$GEN_DATABASE_USERNAME = "root";
$GEN_DATABASE_PASSWORD = "";
$GEN_PREFIX_TABLE = false;
$GEN_SOLO_PATH = "../../src/App/Models/";
$GEN_SET_PATH = "../../src/App/Models/Set/";
$GEN_TABLES_ARRAY = null;

$GEN_DATABASES = ["test"];
$GEN_SOLO_NS = "App\Models";
$GEN_SET_NS = "App\Models\Set";

new DbObjectsFactory(autoStart:true, rebuildOutputFolders:true);
