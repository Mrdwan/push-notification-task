<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class PushNotifications extends AbstractMigration
{
    public function up(): void
    {
        $this->table('push_notifications')
            ->addColumn('title', 'string', ['limit' => 190])
            ->addColumn('message', 'text')
            ->addColumn('country_id', 'integer')
            ->addForeignKey('country_id', 'countries', 'id')
            ->create();
    }

    public function down(): void
    {
        $this->table('push_notifications')
            ->drop();
    }
}
