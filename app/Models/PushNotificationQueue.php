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
     * @param array $tokens
     * @param array $data
     *
     * @return integer|null
     */
    static public function queue(int $notificationId, array $tokens, array $data): ?int
    {
        $table = self::getTableName();
        // Build the placeholders for the prepared statement
        $placeholders = implode(',', array_fill(0, count($tokens), '(?, ?)'));

        // Prepare the statement with placeholders
        $statement = parent::connection()->prepare("
            INSERT INTO $table (content, push_notification_id) VALUES $placeholders
        ");

        // values should be flattened
        // ['content1', 'push_notification_id_1', 'content2', 'push_notification_id_2', ...]
        $values = [];
        foreach ($tokens as $token) {
            $content = array_merge($data, ['token' => $token]);
            $values[] = json_encode($content);
            $values[] = $notificationId;
        }

        return $statement->execute($values);
    }

    /**
     * Get push notifications in chunks.
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    static public function getInChunks(int $limit, ?int $offset): array
    {
        // TODO: better ORM
        $sql = "
            SELECT *
            FROM push_notifications_queue
        ";

        if ($offset) {
            $sql .= " WHERE id < :offset";
        }

        $sql .= " ORDER BY id DESC LIMIT :limit";

        $statement = parent::connection()->prepare($sql);
        $statement->bindParam(':limit', $limit, PDO::PARAM_INT);

        if ($offset) {
            $statement->bindParam(':offset', $offset, PDO::PARAM_INT);
        }

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