<?php

namespace App\Models;

use PDO;
use Exception;

class PushNotificationQueue extends Model
{
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
            'push_notification_id' => $notificationId
        ]);
    }

    /**
     * Get push notifications in chunks.
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    static public function getInChunks(int $limit, int $offset): array
    {
        // TODO: better ORM
        $statement = parent::connection()->prepare("
            SELECT *
            FROM push_notifications_queue
            ORDER BY id
            LIMIT :limit OFFSET :offset
        ");

        $statement->bindParam(':limit', $limit, PDO::PARAM_INT);
        $statement->bindParam(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    static function bulkDelete(array $ids): bool
    {
        $tableName = self::getTableName();

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        // TODO: better ORM
        $statement = parent::connection()->prepare("
            DELETE FROM $tableName
            WHERE id IN ($placeholders)
        ");

        // Bind each ID separately
        foreach ($ids as $index => $id) {
            $statement->bindValue($index + 1, $id, PDO::PARAM_INT);
        }

        return $statement->execute();
    }
}