<?php

namespace app\models;

use yii\db\ActiveRecord;
use Yii;

/**
 * @property int $id
 * @property string $full_name
 * @property int $created_at
 */
class Author extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%author}}';
    }

    public function rules(): array
    {
        return [
            [['full_name'], 'required'],
            [['full_name'], 'string', 'max' => 255],
            [['created_at'], 'integer'],
        ];
    }

    public function beforeSave($insert): bool
    {
        if ($insert) {
            $this->created_at = time();
        }

        return parent::beforeSave($insert);
    }

    /* ===================== RELATIONS ===================== */

    public function getBookAuthors()
    {
        return $this->hasMany(BookAuthor::class, ['author_id' => 'id']);
    }

    public function getBooks()
    {
        return $this->hasMany(Book::class, ['id' => 'book_id'])
            ->via('bookAuthors');
    }

    public function getAuthorSubscriptions()
    {
        return $this->hasMany(AuthorSubscription::class, ['author_id' => 'id']);
    }

    /* ===================== CASCADE DELETE ===================== */

    public function beforeDelete(): bool
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            foreach ($this->bookAuthors as $bookAuthor) {
                $bookId = $bookAuthor->book_id;

                // удаляем связь автор ↔ книга
                $bookAuthor->delete();

                // проверяем, остались ли у книги авторы
                $authorsLeft = (int) (new \yii\db\Query())
                    ->from('book_author')
                    ->where(['book_id' => $bookId])
                    ->count();

                // если авторов больше нет — удаляем книгу
                if ($authorsLeft === 0) {
                    if ($book = Book::findOne($bookId)) {
                        $book->delete();
                    }
                }
            }

            // удаляем подписки автора
            foreach ($this->authorSubscriptions as $subscription) {
                $subscription->delete();
            }

            $transaction->commit();
            return true;

        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}
