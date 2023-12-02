<?php

namespace App\Models;

use App\Models\PushNotificationQueue;
use Exception;
use PDO;

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
     * @param integer $countryId
     * @param integer $notificationId
     *
     * @return void
     */
    public static function dispatch(string $title, string $message, int $countryId, int $notificationId): void
    {
        $chunkSize = 5000;

        // TODO: can be improved to (join method + where method) ORM
        $statement = parent::connection()->prepare("
            SELECT d.`token`
            FROM `devices` d
            INNER JOIN `users` u ON d.`user_id` = u.`id`
            WHERE u.`country_id` = :countryId
            AND d.`expired` = 0
            LIMIT :offset, :limit
        ");

        $statement->bindValue(':countryId', $countryId, PDO::PARAM_INT);

        $page = 0;
        do {
            $offset = $page * $chunkSize;
            $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
            $statement->bindValue(':limit', $chunkSize, PDO::PARAM_INT);

            $statement->execute();
            $devices = $statement->fetchAll(PDO::FETCH_ASSOC);

            if ($devices) {
                $tokens = array_column($devices, 'token');
                PushNotificationQueue::queue($notificationId, $tokens, [
                        'title' => $title,
                        'message' => $message,
                ]);
            }

            $page++;
        } while (!empty($devices));
    }

    /**
     * get notification statistics
     *
     * @param integer $notificationID
     *
     * @return array|null
     */
    static function statistics(int $notificationID): ?array
    {
        // TODO: can be improved to (join method + where method) ORM
        $statement = parent::connection()->prepare("
            SELECT
                p.`id`,
                p.`title`,
                p.`message`,
                p.`sent`,
                p.`failed`,
                COUNT(q.`id`) AS pending_count
            FROM push_notifications p
            LEFT JOIN push_notifications_queue q
                ON p.`id` = q.`push_notification_id`
            WHERE p.`id` = ?
        ");

        $statement->bindValue(1, $notificationID);
        $statement->execute();

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * update stats for a notification
     *
     * @param array $stats
     *
     * @return void
     */
    static function updateStats(array $stats): void
    {
        $table = self::getTableName();

        foreach ($stats as $notificationStats) {
            $statement = parent::connection()->prepare("
                UPDATE $table
                SET
                    sent = sent + :sent,
                    failed = failed + :failed
                WHERE
                    id = :id
            ");

            $statement->bindParam(':sent', $notificationStats['sent']);
            $statement->bindParam(':failed', $notificationStats['failed']);
            $statement->bindParam(':id', $notificationStats['notification_id']);
            $statement->execute();
        }
    }

    /**
     * send the notification
     *
     * @throws Exception
     *
     * @return bool
     */
    public static function send(string $title, string $message, string $token): bool
    {
        return random_int(1, 10) > 1;
    }
}