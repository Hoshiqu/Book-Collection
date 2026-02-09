<?php

namespace app\models;

use yii\db\ActiveRecord;

class Genre extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%genre}}';
    }

    public function rules(): array
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 100],
            [['name'], 'unique'],
        ];
    }

    public function getBooks()
    {
        return $this->hasMany(Book::class, ['id' => 'book_id'])
            ->viaTable('book_genre', ['genre_id' => 'id']);
    }
}
