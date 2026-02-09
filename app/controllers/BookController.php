<?php

namespace app\controllers;

use app\models\Book;
use app\models\Genre;
use app\models\Author;
use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class BookController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    // публичный каталог
                    [
                        'allow' => true,
                        'actions' => ['index', 'view'],
                        'roles' => ['?', '@'],
                    ],
                    // всё остальное — только для авторизованных
                    [
                        'allow' => true,
                        'actions' => ['create', 'update', 'delete'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Публичный каталог (ТОЛЬКО published)
     */
    public function actionIndex()
    {
        $genreId = Yii::$app->request->get('genre_id');

        $query = Book::find()
            ->published()
            ->with(['authors', 'genres']);

        if ($genreId) {
            $query->joinWith('genres')
                  ->andWhere(['genre.id' => $genreId]);
        }

        return $this->render('index', [
            'books' => $query->all(),
            'genres' => Genre::find()->orderBy('name')->all(),
            'activeGenre' => $genreId,
        ]);
    }

    /**
     * Личная библиотека пользователя
     */
    public function actionMy()
    {
        $userId = Yii::$app->user->id;

        // мои созданные книги
        $myBooks = Book::find()
            ->ownedBy($userId)
            ->with(['authors', 'genres'])
            ->all();

        // книги, добавленные в библиотеку
        $libraryBooks = Book::find()
            ->inUserLibrary($userId)
            ->with(['authors', 'genres'])
            ->all();

        return $this->render('my', [
            'myBooks' => $myBooks,
            'libraryBooks' => $libraryBooks,
        ]);
    }

    /**
     * Создание книги
     * ❗ КНИГА ПУБЛИКУЕТСЯ СРАЗУ
     */
    public function actionCreate()
    {
        $model = new Book();
        $model->user_id = Yii::$app->user->id;
        $model->status = Book::STATUS_PUBLISHED;

        if ($model->load(Yii::$app->request->post())) {
            // Обработка загрузки обложки
            $file = UploadedFile::getInstance($model, 'coverFile');
            if ($file && !$file->hasError) {
                $uploadPath = Yii::getAlias('@app/assets/covers');
                if (!is_dir($uploadPath)) {
                    @mkdir($uploadPath, 0755, true);
                }
                // Проверяем что директория существует и доступна для записи
                if (is_dir($uploadPath) && is_writable($uploadPath)) {
                    $fileName = uniqid() . '.' . $file->extension;
                    $filePath = $uploadPath . '/' . $fileName;
                    if ($file->saveAs($filePath)) {
                        $model->cover_path = '/asset/covers?filename=' . $fileName;
                    }
                } else {
                    Yii::$app->session->setFlash('error', 'Cannot create upload directory. Please check permissions.');
                }
            }
            
            if ($model->save()) {

            // GENRES
            // NEW GENRES (from text input)
                if (!empty($model->newGenres)) {
                    $names = array_filter(array_map('trim', explode(',', $model->newGenres)));

                    foreach ($names as $name) {
                        $genre = Genre::findOne(['name' => $name]);
                        if (!$genre) {
                            $genre = new Genre(['name' => $name]);
                            $genre->save(false);
                        }

                        // привязываем жанр, если ещё не привязан
                        if (!$model->getGenres()->where(['genre.id' => $genre->id])->exists()) {
                            $model->link('genres', $genre);
                        }
                    }
                }


                // AUTHORS
                $model->unlinkAll('authors', true);

                // если выбраны авторы в форме
                if (!empty($model->authorIds)) {
                    foreach ($model->authorIds as $authorId) {
                        $author = Author::findOne($authorId);
                        if ($author) {
                            $model->link('authors', $author);
                        }
                    }
                } else {
                    // если авторы не выбраны — автор = текущий пользователь
                    $user = Yii::$app->user->identity;
                    $authorName = $user->username;

                    $author = Author::findOne(['full_name' => $authorName]);
                    if (!$author) {
                        $author = new Author();
                        $author->full_name = $authorName;
                        $author->save(false);
                    }

                    $model->link('authors', $author);
                }


                // Уведомление подписчиков автора о новой книге (SMS)
                try {
                    $notification = new \app\services\NotificationService();
                    $notification->notifyAboutBook($model);
                } catch (\Throwable $e) {
                    Yii::error('Subscription notifications: ' . $e->getMessage(), __METHOD__);
                }

                Yii::$app->session->setFlash('success', 'Book published');

                return $this->redirect(['index']);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Редактирование книги
     */
    public function actionUpdate(int $id)
    {
        $model = Book::find()
            ->with(['authors', 'genres'])
            ->where(['id' => $id])
            ->one();

        if (!$model) {
            throw new NotFoundHttpException('Book not found');
        }

        /**
         * =====================================================
         * ПРЕДЗАПОЛНЕНИЕ ЧЕКБОКСОВ ПРИ ОТКРЫТИИ ФОРМЫ (GET)
         * =====================================================
         */
        if (empty($model->authorIds)) {
            $model->authorIds = array_map(
                fn($author) => $author->id,
                $model->authors
            );
        }

        if (empty($model->genreIds)) {
            $model->genreIds = array_map(
                fn($genre) => $genre->id,
                $model->genres
            );
        }

        /**
         * =====================================================
         * POST
         * =====================================================
         */
        if ($model->load(Yii::$app->request->post())) {

            // не даём Yii потерять массивы
            $model->authorIds = $model->authorIds ?? [];
            $model->genreIds  = $model->genreIds ?? [];

            // флаг удаления обложки
            $removeCover = Yii::$app->request->post('removeCover') === '1';

            /**
             * =====================================================
             * УДАЛЕНИЕ ТЕКУЩЕЙ ОБЛОЖКИ (если нажали 🗑)
             * =====================================================
             */
            if ($removeCover && $model->cover_path) {
                $oldPath = str_replace('/asset/covers?filename=', '', $model->cover_path);
                $oldFilePath = Yii::getAlias('@app/assets/covers') . '/' . $oldPath;

                if (is_file($oldFilePath)) {
                    @unlink($oldFilePath);
                }

                $model->cover_path = null;
            }

            /**
             * =====================================================
             * ЗАГРУЗКА НОВОЙ ОБЛОЖКИ
             * =====================================================
             */
            $file = UploadedFile::getInstance($model, 'coverFile');
            if ($file && !$file->hasError) {

                // если была старая — удаляем
                if ($model->cover_path) {
                    $oldPath = str_replace('/asset/covers?filename=', '', $model->cover_path);
                    $oldFilePath = Yii::getAlias('@app/assets/covers') . '/' . $oldPath;
                    if (is_file($oldFilePath)) {
                        @unlink($oldFilePath);
                    }
                }

                $uploadPath = Yii::getAlias('@app/assets/covers');
                if (!is_dir($uploadPath)) {
                    @mkdir($uploadPath, 0755, true);
                }

                if (is_dir($uploadPath) && is_writable($uploadPath)) {
                    $fileName = uniqid('cover_', true) . '.' . $file->extension;
                    $filePath = $uploadPath . '/' . $fileName;

                    if ($file->saveAs($filePath)) {
                        $model->cover_path = '/asset/covers?filename=' . $fileName;
                    }
                } else {
                    Yii::$app->session->setFlash(
                        'error',
                        'Cannot create upload directory. Please check permissions.'
                    );
                }
            }

            /**
             * =====================================================
             * SAVE BOOK
             * =====================================================
             */
            if ($model->save()) {

                /**
                 * =====================================================
                 * GENRES (НОВЫЕ ИЗ ТЕКСТОВОГО ПОЛЯ)
                 * =====================================================
                 */
                if (!empty($model->newGenres)) {
                    $names = array_filter(array_map('trim', explode(',', $model->newGenres)));

                    foreach ($names as $name) {
                        $genre = Genre::findOne(['name' => $name]);
                        if (!$genre) {
                            $genre = new Genre(['name' => $name]);
                            $genre->save(false);
                        }

                        if (!$model->getGenres()->where(['genre.id' => $genre->id])->exists()) {
                            $model->link('genres', $genre);
                        }
                    }
                }

                /**
                 * =====================================================
                 * AUTHORS (M:N)
                 * =====================================================
                 */
                $model->unlinkAll('authors', true);

                if (!empty($model->authorIds)) {
                    foreach ($model->authorIds as $authorId) {
                        if ($author = Author::findOne($authorId)) {
                            $model->link('authors', $author);
                        }
                    }
                } else {
                    // fallback — текущий пользователь
                    $user = Yii::$app->user->identity;
                    $authorName = $user->username;

                    $author = Author::findOne(['full_name' => $authorName]);
                    if (!$author) {
                        $author = new Author();
                        $author->full_name = $authorName;
                        $author->save(false);
                    }

                    $model->link('authors', $author);
                }

                Yii::$app->session->setFlash('success', 'Book saved');
                return $this->redirect(['index']);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }



    /**
     * Удаление книги
     */
    public function actionDelete(int $id)
    {
        $book = Book::findOne($id);

        if (!$book || !$book->canEdit()) {
            throw new NotFoundHttpException();
        }

        $book->delete();

        if (Yii::$app->request->isAjax) {
            return $this->asJson(['success' => true]);
        }
        Yii::$app->session->setFlash('success', 'Book deleted');
        return $this->redirect(['library/index']);
    }
}
