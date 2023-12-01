<?php

namespace App\Models;

use Exception;

class PushNotification extends Model
{
    protected static function getTableName(): string
    {
        return 'push_notifications';
    }

    /**
     * dispatch the notification to the users devices in a queue
     *
     * @param integer $notificationId
     *
     * @return void
     */
    public static function dispatch(int $notificationId): void
    {
        // TODO: get the users then send it in a queue
        // self::send($title, $message, $token);
    }

    /**
     * @throws Exception
     */
    public static function send(string $title, string $message, string $token): bool
    {
        return random_int(1, 10) > 1;
    }
}