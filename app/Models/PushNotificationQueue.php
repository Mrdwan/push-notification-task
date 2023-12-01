<?php

namespace App\Models;

use Exception;

class PushNotificationQueue extends Model
{
    const STATUS_PENDING = 0;
    const STATUS_SENT = 1;
    const STATUS_FAILED = 2;

    protected static function getTableName(): string
    {
        return 'push_notifications_queue';
    }

    /**
     * add the notification to a queue
     * will add it to the database as a simple queue then will consume it later
     *
     * @param integer $notificationId
     * @param array $data
     *
     * @return integer|null
     */
    static public function queue(int $notificationId, array $data): ?int
    {
        // TODO: use DTO object instead of an array
        return self::create([
            'content' => json_encode($data),
            'push_notification_id' => $notificationId,
            'status' => self::STATUS_PENDING
        ]);
    }

    public function markNotificationAsSent()
    {
        $sql = "UPDATE notification_queue SET status = :status WHERE push_notification_id = :push_notification_id";
        $statement = $this->prepare($sql);

        $pushNotificationId = $this->getId();

        $statement->bindParam(':push_notification_id', $pushNotificationId, PDO::PARAM_INT);
        $statement->bindValue(':status', self::STATUS_SENT, PDO::PARAM_INT);

        return $statement->execute();
    }

    public function markNotificationAsFailed()
    {
        $sql = "UPDATE notification_queue SET status = :status WHERE push_notification_id = :push_notification_id";
        $statement = $this->prepare($sql);

        $pushNotificationId = $this->getId();

        $statement->bindParam(':push_notification_id', $pushNotificationId, PDO::PARAM_INT);
        $statement->bindValue(':status', self::STATUS_FAILED, PDO::PARAM_INT);

        return $statement->execute();
    }
}