<?php


namespace App\Models;


use Exception;

class PushNotificationsModel
{
    /**
     * @throws Exception
     */
    public static function send(string $title, string $message, string $token): bool
    {
        return random_int(1, 10) > 1;
    }
}