<?php

namespace App\Models;

use PDO;
use Exception;

class PushNotification extends Model
{
    protected static function getTableName(): string
    {
        return 'push_notifications';
    }

    /**
     * dispatch the notification to the queue for all devices
     *
     * @param string $title
     * @param string $message
     * @param integer $deviceId
     * @param integer $notificationId
     *
     * @return void
     */
    public static function dispatch(string $title, string $message, int $deviceId, int $notificationId): void
    {
        // TODO: can be improve to (join method + where method) ORM
        $statement = parent::connection()->prepare("
            SELECT d.`token`
            FROM `devices` d
            INNER JOIN `users` u ON d.`user_id` = u.`id`
            WHERE u.`country_id` = ?
            AND d.`expired` = 0
        ");

        $statement->bindValue(1, $deviceId);
        $statement->execute();
        $devices = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($devices as $device) {
            PushNotificationQueue::queue($notificationId, [
                'title' => $title,
                'message' => $message,
                'token' => $device['token'],
            ]);
        }
    }

    static function statistics(int $notificationID): ?array
    {
        // TODO: can be improve to (join method + where method) ORM
        $statement = parent::connection()->prepare("
            SELECT
                p.`id`,
                p.`title`,
                p.`message`,
                COUNT(q.`status`) AS total_count,
                COUNT(CASE WHEN q.`status` = 0 THEN 1 END) AS pending_count,
                COUNT(CASE WHEN q.`status` = 1 THEN 1 END) AS sent_count,
                COUNT(CASE WHEN q.`status` = 2 THEN 1 END) AS failed_count
            FROM push_notifications p
            JOIN push_notifications_queue q
                ON p.`id` = q.`push_notification_id`
            WHERE p.`id` = ?
        ");

        $statement->bindValue(1, $notificationID);
        $statement->execute();

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @throws Exception
     */
    public static function send(string $title, string $message, string $token): bool
    {
        return random_int(1, 10) > 1;
    }
}