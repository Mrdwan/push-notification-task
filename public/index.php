<?php

require_once('./../vendor/autoload.php');

use app\Request;
use app\controllers\PushNotificationsController;

$action = $_POST[Request::PARAM_ACTION] ?? null;
$controller = new PushNotificationsController();

switch ($action) {
    case Request::PARAM_ACTION_SEND:
        $title = $_POST[Request::PARAM_ACTION_SEND_TITLE];
        $message = $_POST[Request::PARAM_ACTION_SEND_MESSAGE];
        $countryId = $_POST[Request::PARAM_ACTION_SEND_COUNTRY_ID];

        if (!$title || !$message || !$countryId) {
            Request::forbidden();
        }

        $result = $controller->sendByCountryId($title, $message, (int)$countryId);
        break;

    case Request::PARAM_ACTION_DETAILS:
        $notificationId = $_POST[Request::PARAM_ACTION_DETAILS_NOTIFICATION_ID];

        if (!$notificationId) {
            Request::forbidden();
        }

        $result = $controller->details((int)$notificationId);
        break;

    case Request::PARAM_ACTION_CRON:
        $result = $controller->cron();
        break;
    default:
        Request::forbidden();
}

$response = [
    'success' => $result !== null,
    'result' => $result,
];

Request::response($response);