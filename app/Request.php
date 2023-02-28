<?php


namespace app;


class Request
{
    public const PARAM_ACTION = 'action';

    public const PARAM_ACTION_SEND = "send";
    public const PARAM_ACTION_CRON = "cron";
    public const PARAM_ACTION_DETAILS = "details";

    public const PARAM_ACTION_SEND_TITLE = 'title';
    public const PARAM_ACTION_SEND_MESSAGE = 'message';
    public const PARAM_ACTION_SEND_COUNTRY_ID = 'country_id';
    public const PARAM_ACTION_DETAILS_NOTIFICATION_ID = "notification_id";

    public static function forbidden(): void
    {
        header('HTTP/1.0 403 Forbidden');
        die('You are forbidden!');
    }

    public static function response(array $response): void
    {
        header("Content-type: application/json");
        echo json_encode($response, JSON_THROW_ON_ERROR);
        exit;
    }
}