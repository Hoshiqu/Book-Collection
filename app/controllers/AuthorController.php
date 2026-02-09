<?php

namespace app\controllers;

use app\models\Author;
use app\services\SubscriptionService;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class AuthorController extends Controller
{
    public function actionView(int $id)
    {
        $author = Author::findOne($id);
        if ($author === null) {
            throw new NotFoundHttpException('Author not found');
        }

        return $this->render('view', [
            'author' => $author,
            'books' => $author->books,
        ]);
    }
    
    public function actionSubscribe(int $id): Response
    {
        $service = new SubscriptionService();

        try {
            $phone = Yii::$app->request->post('phone');
            if (empty(trim((string) $phone))) {
                throw new \DomainException('Пожалуйста, введите ваш номер телефона.');
            }
            $service->subscribe($id, $phone);

            if (Yii::$app->request->isAjax) {
                return $this->asJson(['success' => true]);
            }
            Yii::$app->session->setFlash('success', 'Вы успешно подписались на автора.');
        } catch (\Exception $e) {
            if (Yii::$app->request->isAjax) {
                return $this->asJson(['success' => false, 'error' => $e->getMessage()]);
            }
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->redirect(['author/view', 'id' => $id]);
    }

    public function actionIndex()
    {
        $authors = Author::find()
            ->with('books')
            ->orderBy(['full_name' => SORT_ASC])
            ->all();
    
        return $this->render('index', [
            'authors' => $authors,
        ]);
    }

    public function actionUpdate(int $id)
    {
        $model = Author::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('Автор не найден');
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Автор успешно обновлен.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete(int $id)
    {
        $model = Author::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('Автор не найден');
        }

        if ($model->delete()) {
            Yii::$app->session->setFlash('success', 'Автор удален.');
        } else {
            Yii::$app->session->setFlash('error', 'Не удалось удалить автора.');
        }

        return $this->redirect(['index']);
    }

    public function actionCreate()
    {
        $model = new Author();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Автор успешно создан.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

}
