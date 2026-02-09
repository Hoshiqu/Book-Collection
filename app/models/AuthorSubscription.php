<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $author_id
 * @property int $user_id
 * @property string $phone
 * @property int $created_at
 */
class AuthorSubscription extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%subscription}}';
    }

    public function rules(): array
    {
        return [
            [['author_id', 'user_id', 'phone'], 'required'],
            [['author_id', 'user_id', 'created_at'], 'integer'],
            [['phone'], 'string', 'max' => 20],
            [['author_id', 'user_id'], 'unique', 'targetAttribute' => ['author_id', 'user_id']],
        ];
    }

    public function beforeSave($insert): bool
    {
        if ($insert) {
            $this->created_at = time();
        }

        return parent::beforeSave($insert);
    }

    /** Подписка → автор */
    public function getAuthor()
    {
        return $this->hasOne(Author::class, ['id' => 'author_id']);
    }

    /** Подписка → пользователь */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
