<?php

namespace app\commands;

use app\services\NotificationService;
use yii\console\Controller;

class NotifyController extends Controller
{
    public function actionSend(): void
    {
        $service = new NotificationService();
        $count = $service->notifyNewBooks();

        echo "Sent {$count} notifications\n";
    }
}
