<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use app\models\Book;
use app\models\UserLibrary;

class LibraryController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Моя библиотека: книги из user_library (добавленные кнопкой ➕) + книги, где пользователь автор (book.user_id).
     */
    public function actionIndex()
    {
        $userId = Yii::$app->user->id;

        // Книги из таблицы user_library (добавленные в библиотеку)
        $libraryBookIds = UserLibrary::find()
            ->select('book_id')
            ->where(['user_id' => $userId])
            ->column();

        // Книги, где пользователь — автор/владелец (book.user_id)
        $ownedBookIds = Book::find()
            ->select('id')
            ->where(['user_id' => $userId])
            ->column();

        $allBookIds = array_unique(array_merge($libraryBookIds, $ownedBookIds));

        if (empty($allBookIds)) {
            $books = [];
        } else {
            $books = Book::find()
                ->andWhere(['id' => $allBookIds])
                ->with(['authors', 'genres'])
                ->orderBy(['title' => SORT_ASC])
                ->all();
        }

        return $this->render('index', [
            'books' => $books,
        ]);
    }

    /**
     * Добавить книгу в библиотеку (AJAX)
     */
    public function actionAdd(int $id)
    {
        $book = Book::findOne($id);
        if (!$book) {
            return $this->asJson(['success' => false, 'error' => 'Book not found'], 404);
        }

        // only allow POST for adding to library
        if (!Yii::$app->request->isPost) {
            return $this->asJson(['success' => false, 'error' => 'Method not allowed'], 405);
        }

        $exists = UserLibrary::find()
            ->where([
                'user_id' => Yii::$app->user->id,
                'book_id' => $book->id,
            ])
            ->exists();

        if (!$exists) {
            $library = new UserLibrary();
            $library->user_id = Yii::$app->user->id;
            $library->book_id = $book->id;
            // created_at выставит TimestampBehavior

            if (!$library->save(false)) {
                $errors = $library->getFirstErrors();
                Yii::error('Failed to save UserLibrary: ' . json_encode($errors), __METHOD__);
                if (Yii::$app->request->isAjax) {
                    return $this->asJson([
                        'success' => false,
                        'error' => 'Save failed: ' . (!empty($errors) ? implode(', ', $errors) : 'Неизвестная ошибка'),
                    ]);
                }
                Yii::$app->session->setFlash('error', 'Failed to add book to library.');
                return $this->redirect(Yii::$app->request->referrer ?: ['book/index']);
            }
        }

        if (Yii::$app->request->isAjax) {
            return $this->asJson(['success' => true]);
        }
        Yii::$app->session->setFlash('success', 'Книга добавлена в библиотеку.');
        return $this->redirect(['book/index']);
    }

    /**
     * Убрать книгу из библиотеки (AJAX)
     */
    public function actionRemove(int $id)
    {
        $book = Book::findOne($id);
        if (!$book) {
            throw new NotFoundHttpException();
        }

        // only allow POST for removing from library
        if (!Yii::$app->request->isPost) {
            return $this->asJson(['success' => false, 'error' => 'Method not allowed'], 405);
        }

        UserLibrary::deleteAll([
            'user_id' => Yii::$app->user->id,
            'book_id' => $id,
        ]);

        if (Yii::$app->request->isAjax) {
            return $this->asJson(['success' => true]);
        }
        Yii::$app->session->setFlash('success', 'Книга удалена из библиотеки.');
        return $this->redirect(Yii::$app->request->referrer ?: ['book/index']);
    }
}
