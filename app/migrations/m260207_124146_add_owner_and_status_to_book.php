<?php

use yii\db\Migration;

class m260207_124146_add_owner_and_status_to_book extends Migration
{
    public function safeUp()
    {
        $table = '{{%book}}';

        // user_id
        try {
            $this->addColumn($table, 'user_id', $this->integer()->notNull());
        } catch (\Throwable $e) {
            // already exists — ok
        }

        // status
        try {
            $this->addColumn(
                $table,
                'status',
                $this->string(20)->notNull()->defaultValue('published')
            );
        } catch (\Throwable $e) {
            // already exists — ok
        }

        // index status
        try {
            $this->dropIndex('idx-book-status', $table);
        } catch (\Throwable $e) {}

        $this->createIndex(
            'idx-book-status',
            $table,
            'status'
        );

        // index user_id
        try {
            $this->dropIndex('idx-book-user_id', $table);
        } catch (\Throwable $e) {}

        $this->createIndex(
            'idx-book-user_id',
            $table,
            'user_id'
        );

        // FK
        try {
            $this->addForeignKey(
                'fk-book-user_id',
                $table,
                'user_id',
                '{{%user}}',
                'id',
                'CASCADE'
            );
        } catch (\Throwable $e) {
            // FK already exists
        }
    }


    public function safeDown()
    {
        $table = '{{%book}}';

        try { $this->dropForeignKey('fk-book-user_id', $table); } catch (\Throwable $e) {}
        try { $this->dropIndex('idx-book-user_id', $table); } catch (\Throwable $e) {}
        try { $this->dropIndex('idx-book-status', $table); } catch (\Throwable $e) {}
        try { $this->dropColumn($table, 'user_id'); } catch (\Throwable $e) {}
        try { $this->dropColumn($table, 'status'); } catch (\Throwable $e) {}
    }



}
