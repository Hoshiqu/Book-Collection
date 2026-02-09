<?php

use yii\db\Migration;

class m260207_105259_create_book_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%book}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255)->notNull(),
            'published_year' => $this->smallInteger()->notNull(),
            'description' => $this->text(),
            'isbn' => $this->string(20)->notNull(),
            'cover_path' => $this->string(255),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex(
            'idx-book-published_year',
            '{{%book}}',
            'published_year'
        );

        $this->createIndex(
            'idx-book-title',
            '{{%book}}',
            'title'
        );

        $this->createIndex(
            'ux-book-isbn',
            '{{%book}}',
            'isbn',
            true
        );
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%book}}');
    }
}
