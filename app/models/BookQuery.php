<?php

namespace app\models;

use yii\db\ActiveQuery;

class BookQuery extends ActiveQuery
{
    public function published()
    {
        return $this->andWhere(['book.status' => 'published']);
    }

    public function ownedBy($userId)
    {
        return $this->andWhere(['book.user_id' => $userId]);
    }

    public function subscribedBy($userId)
    {
        return $this
            ->joinWith(['authors.authorSubscriptions s'])
            ->andWhere(['s.user_id' => $userId]);
    }

    public function inUserLibrary(int $userId): self
    {
        return $this->innerJoin('{{%user_library}} ul', 'ul.book_id = book.id')
            ->andWhere(['ul.user_id' => $userId]);
    }

    // models/Book.php
    public static function find(): BookQuery
    {
        return new BookQuery(static::class);
    }

}
