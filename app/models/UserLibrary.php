<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * Запись «книга добавлена в библиотеку пользователя».
 * Таблица user_library: id, user_id, book_id, created_at (без updated_at — см. миграцию).
 *
 * @property int $id
 * @property int $user_id
 * @property int $book_id
 * @property int $created_at
 */
class UserLibrary extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%user_library}}';
    }

    public function getBook()
    {
        return $this->hasOne(Book::class, ['id' => 'book_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
                'value' => function () {
                    return time();
                },
            ],
        ];
    }
}
