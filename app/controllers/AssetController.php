<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class AssetController extends Controller
{
    public function actionCovers($filename)
    {
        $filePath = Yii::getAlias('@app/assets/covers/' . $filename);
        
        if (!file_exists($filePath) || !is_file($filePath)) {
            throw new NotFoundHttpException('Файл не найден');
        }
        $mimeType = mime_content_type($filePath);
        if (!$mimeType) {
            $mimeType = 'image/jpeg'; 
        }
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', $mimeType);
        Yii::$app->response->data = file_get_contents($filePath);
        
        return Yii::$app->response;
    }
}
