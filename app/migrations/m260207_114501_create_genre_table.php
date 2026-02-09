<?php

use yii\db\Migration;

class m260207_114501_create_genre_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%genre}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(100)->notNull()->unique(),
        ]);
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%genre}}');
    }
}
