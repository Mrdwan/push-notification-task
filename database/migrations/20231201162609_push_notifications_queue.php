<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class PushNotificationsQueue extends AbstractMigration
{
    public function up(): void
    {
        $this->table('push_notifications_queue')
            ->addColumn('content', 'json')
            ->addColumn('status', 'integer', ['default' => 0])
            ->addColumn('push_notification_id', 'integer')
            ->addForeignKey('push_notification_id', 'push_notifications', 'id')
            ->create();
    }

    public function down(): void
    {
        $this->table('push_notifications_queue')
            ->drop();
    }
}
