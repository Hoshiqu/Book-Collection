<?php

use yii\db\Migration;

class m260207_114544_create_book_genre_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%book_genre}}', [
            'book_id' => $this->integer()->notNull(),
            'genre_id' => $this->integer()->notNull(),
        ]);

        $this->addPrimaryKey(
            'pk-book_genre',
            '{{%book_genre}}',
            ['book_id', 'genre_id']
        );

        $this->addForeignKey(
            'fk-book_genre-book',
            '{{%book_genre}}',
            'book_id',
            '{{%book}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-book_genre-genre',
            '{{%book_genre}}',
            'genre_id',
            '{{%genre}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%book_genre}}');
    }
}
