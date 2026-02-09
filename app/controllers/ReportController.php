<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\services\ReportService;

class ReportController extends Controller
{
    public function actionTopAuthors()
    {
        $year = (int)Yii::$app->request->get('year', 2025);

        $service = new ReportService();
        $data = $service->topAuthorsByYear($year);

        return $this->render('top-authors', [
            'year' => $year,
            'data' => $data,
        ]);
    }
}
