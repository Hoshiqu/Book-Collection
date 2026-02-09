<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $author_id
 * @property string $phone
 * @property int $created_at
 */
class Subscription extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%subscription}}';
    }

    public function rules(): array
    {
        return [
            [['author_id', 'phone'], 'required'],
            [['author_id', 'created_at'], 'integer'],
            [['phone'], 'string', 'max' => 20],

            // базовая нормализация номера
            [['phone'], 'match', 'pattern' => '/^\+?[0-9]{10,15}$/'],
        ];
    }

    public function beforeSave($insert): bool
    {
        if ($insert) {
            $this->created_at = time();
        }

        return parent::beforeSave($insert);
    }

    public function getAuthor()
    {
        return $this->hasOne(Author::class, ['id' => 'author_id']);
    }
}
