<?php namespace knhackt;
die("Hello there");
use base\SuperUtility;

ini_set('display_errors', 1);
error_reporting(E_ALL);

require("base/dependencies.php");
require("dependencies.php");

$data = array_merge($_POST, $_GET);

SuperUtility::enableCustomErrorHandler();
if (!SuperUtility::getExistentAndValue($data, "exception"))
    set_exception_handler(function ($exception) {
        /** @var $exception \Exception */

        $data = array("type" => "unknown", "message" => $exception->getMessage());

        if ($exception instanceof \MySQLException) {
            $data = $exception->getData();
        }

        http_response_code(500);

        $data = array("code" => 500, "message" => "Uncaught exception!", "payload" => $data);
        SuperUtility::$errorless_exit = false;
        die(json_encode($data, JSON_PRETTY_PRINT));

    });


$controller = new Controller($data);
$controller->start("index.html");