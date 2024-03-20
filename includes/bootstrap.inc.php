<?php

define("PROJECT_ROOT_PATH", __DIR__ . "/../");

//Domain informationen Bsp:  PROTOCOL: https://   DOMAIN: crm.my-drive.id   API_URL: https://crm.my-drive.id/api/
define('PROTOCOL',(!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS'] == 'on')) ? 'https://' : 'http://',true);
define('DOMAIN',$_SERVER['HTTP_HOST']);
$filename = basename($_SERVER["SCRIPT_FILENAME"]);
define('API_URL', preg_replace("/\/$/",'',PROTOCOL.DOMAIN.str_replace(array('\\', $filename), '', dirname(htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES))),1).'/',true);


require_once PROJECT_ROOT_PATH . "/includes/config.inc.php";
require_once PROJECT_ROOT_PATH . "/includes/database.inc.php";
require_once PROJECT_ROOT_PATH . "/includes/output.inc.php";
require_once PROJECT_ROOT_PATH . "/includes/baseController.inc.php";
require_once PROJECT_ROOT_PATH . "/includes/usersController.inc.php";
require_once PROJECT_ROOT_PATH . "/includes/authentication.inc.php";
require_once PROJECT_ROOT_PATH . "/includes/enum.php";


//require_once PROJECT_ROOT_PATH . "/Controller/Api/BaseController.php";
//require_once PROJECT_ROOT_PATH . "/Model/UserModel.php";