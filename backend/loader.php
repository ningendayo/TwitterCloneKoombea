<?php

use api\Response;
use utils\Functions;

require_once 'vendor/autoload.php';
require_once 'configs.php';
require_once './core/Roles.php';
require_once './core/DataBaseTypes.php';
require_once './core/DataTypes.php';
require_once './core/Response.php';
require_once './core/Request.php';
require_once './core/JWTConfig.php';
$response = new Response();
require_once './utils/Functions.php';
require_once './utils/ServerData.php';
require_once './utils/Mailing.php';
require_once './utils/FileManager.php';
require_once './database/Connection.php';
require_once './database/AbsDatabase.php';
require_once './database/sql/SQLDatabase.php';
require_once './database/mongo/MongoDatabase.php';
require_once './endpoints/Crud.php';
require_once './endpoints/EndPoint.php';
(new utils\Functions)->includeDir("./endpoints/src/");
