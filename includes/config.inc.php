<?php

$ini_array = parse_ini_file(PROJECT_ROOT_PATH . "config/config.ini");

define("DB_SERVER", $ini_array['SERVERNAME']);
define("DB_NAME", $ini_array['DATABASENAME']);
define("DB_USER", $ini_array['DATABASEUSERNAME']);
define("DB_USERPASSWORD", $ini_array['DATABASEUSERPASSWORD']);
