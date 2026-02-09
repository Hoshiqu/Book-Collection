<?php

use yii\db\Migration;

class m260207_105814_create_subscription_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%subscription}}', [
            'id' => $this->primaryKey(),
            'author_id' => $this->integer()->notNull(),
            'phone' => $this->string(20)->notNull(),
            'created_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex(
            'idx-subscription-author_id',
            '{{%subscription}}',
            'author_id'
        );

        $this->createIndex(
            'idx-subscription-phone',
            '{{%subscription}}',
            'phone'
        );

        $this->createIndex(
            'ux-subscription-author_phone',
            '{{%subscription}}',
            ['author_id', 'phone'],
            true
        );

        $this->addForeignKey(
            'fk-subscription-author_id',
            '{{%subscription}}',
            'author_id',
            '{{%author}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%subscription}}');
    }
}
