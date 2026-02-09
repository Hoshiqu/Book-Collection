<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\Application as WebApplication;
/**
 * @property int $id
 * @property string $title
 * @property int $published_year
 * @property string|null $description
 * @property string $isbn
 * @property string|null $cover_path
 * @property int $user_id
 * @property string $status
 * @property int $created_at
 * @property int $updated_at
 *
 * @property User $user
 * @property Author[] $authors
 * @property Genre[] $genres
 */
class Book extends ActiveRecord
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';

    public $genreIds = [];
    public $authorIds = [];
    public $coverFile;
    public $newGenres;


    
    public static function tableName(): string
    {
        return '{{%book}}';
    }

    /**
     * Подключаем кастомный query
     */
    public static function find()
    {
        return new BookQuery(get_called_class());
    }

    public function rules(): array
    {
        return [
            [['title', 'published_year'], 'required'],
            [['isbn'], 'safe'], // ISBN генерируется автоматически
            [['description'], 'string'],
            [['published_year', 'created_at', 'updated_at', 'user_id'], 'integer'],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_PUBLISHED]],
            [['title', 'cover_path'], 'string', 'max' => 255],
            [['isbn'], 'string', 'max' => 20],
            [['isbn'], 'unique'],
            [['genreIds', 'authorIds'], 'each', 'rule' => ['integer']],
            [['genreIds', 'authorIds'], 'safe'],
            [['coverFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, gif', 'maxSize' => 5 * 1024 * 1024],
            [['newGenres'], 'string'],
        ];
    }

    public function beforeSave($insert): bool
{
    if ($insert) {
        $this->created_at = time();

        // user_id — только если web-приложение
        if (Yii::$app instanceof WebApplication && !Yii::$app->user->isGuest) {
            $this->user_id = Yii::$app->user->id;
        }

        if (empty($this->status)) {
            $this->status = self::STATUS_DRAFT;
        }

        if (empty($this->isbn)) {
            $this->isbn = $this->generateIsbn();
        }
    }

    $this->updated_at = time();

    return parent::beforeSave($insert);
}

    /**
     * Генерация уникального ISBN
     */
    private function generateIsbn(): string
    {
        do {
            // Генерируем ISBN-13 формат: 978-XXXX-XXXX-X
            $isbn = '978-' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT) . '-' . 
                    str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT) . '-' . 
                    rand(0, 9);
        } while (self::find()->where(['isbn' => $isbn])->exists());
        
        return $isbn;
    }

    /* =======================
     * Relations
     * ======================= */

    /**
     * Владелец книги
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * book → book_author
     */
    public function getBookAuthors()
    {
        return $this->hasMany(BookAuthor::class, ['book_id' => 'id']);
    }

    /**
     * book → authors (M:N)
     */
    public function getAuthors()
    {
        return $this->hasMany(Author::class, ['id' => 'author_id'])
            ->viaTable('book_author', ['book_id' => 'id']);
    }
    /**
     * book → genres (M:N)
     */
    public function getGenres()
    {
        return $this->hasMany(Genre::class, ['id' => 'genre_id'])
            ->viaTable('book_genre', ['book_id' => 'id']);
    }

    public function isInLibrary(int $userId): bool
    {
        return UserLibrary::find()
            ->where([
                'user_id' => $userId,
                'book_id' => $this->id,
            ])
            ->exists();
    }

    /**
 * Записи библиотеки пользователей, где есть эта книга
 */
    public function getLibraryEntries()
    {
        return $this->hasMany(UserLibrary::class, ['book_id' => 'id']);
    }


    /* =======================
     * Helpers
     * ======================= */

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function canEdit(): bool
    {
        return Yii::$app->user->id === $this->user_id;
    }

    public function actionUpdate(int $id)
{
    $model = Book::find()
        ->with(['authors', 'genres'])
        ->where(['id' => $id])
        ->one();

    if ($model === null) {
        throw new NotFoundHttpException('Book not found');
    }

    $model->authorIds = array_map(
        fn($author) => $author->id,
        $model->authors
    );

    $model->genreIds = array_map(
        fn($genre) => $genre->id,
        $model->genres
    );



    if ($model->load(Yii::$app->request->post()) && $model->save()) {
        // тут твоя логика сохранения связей
        return $this->redirect(['book/index']);
    }

    return $this->render('update', [
        'model' => $model,
    ]);
}


}
