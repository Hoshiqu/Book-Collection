<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_library}}`.
 */
class m260207_150356_create_user_library_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user_library}}', [
        'id' => $this->primaryKey(),
        'user_id' => $this->integer()->notNull(),
        'book_id' => $this->integer()->notNull(),
        'created_at' => $this->integer()->notNull(),
    ]);

    $this->createIndex(
        'ux-user_library-user_book',
        '{{%user_library}}',
        ['user_id', 'book_id'],
        true
    );

    $this->addForeignKey(
        'fk-user_library-user',
        '{{%user_library}}',
        'user_id',
        '{{%user}}',
        'id',
        'CASCADE'
    );

    $this->addForeignKey(
        'fk-user_library-book',
        '{{%user_library}}',
        'book_id',
        '{{%book}}',
        'id',
        'CASCADE'
    );

    }


    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%user_library}}');
    }
}
