<?php

use yii\db\Migration;

class m260207_111415_create_subscription_event_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%subscription_event}}', [
            'id' => $this->primaryKey(),
            'subscription_id' => $this->integer()->notNull(),
            'book_id' => $this->integer()->notNull(),
            'sent_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex(
            'ux-subscription_event-subscription_book',
            '{{%subscription_event}}',
            ['subscription_id', 'book_id'],
            true
        );

        $this->addForeignKey(
            'fk-subscription_event-subscription',
            '{{%subscription_event}}',
            'subscription_id',
            '{{%subscription}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-subscription_event-book',
            '{{%subscription_event}}',
            'book_id',
            '{{%book}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%subscription_event}}');
    }
}
