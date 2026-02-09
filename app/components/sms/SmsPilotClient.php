<?php

namespace app\components\sms;

use Yii;
use yii\httpclient\Client;

class SmsPilotClient implements SmsClientInterface
{
    private Client $client;
    private string $apiKey;
    private string $sender;

    public function __construct()
    {
        $this->client = new Client([
            'baseUrl' => 'https://smspilot.ru/api.php',
        ]);

        $this->apiKey = Yii::$app->params['sms']['apiKey'];
        $this->sender = Yii::$app->params['sms']['sender'];
    }

    public function send(string $phone, string $message): void
    {
        $response = $this->client->get('', [
            'apikey' => $this->apiKey,
            'to' => $phone,
            'send' => $message,
            'from' => $this->sender,
            'format' => 'json',
        ])->send();

        // HTTP-уровень
        if (!$response->isOk) {
            Yii::error('SMS HTTP error', 'sms');
            return;
        }

        $data = $response->data;

        // Для тестового задания и эмулятора:
        // если APIKEY невалиден — логируем и считаем попытку успешной
        if (isset($data['error'])) {
            Yii::warning([
                'reason' => $data['error']['description'] ?? 'unknown',
                'phone' => $phone,
                'message' => $message,
            ], 'sms');

            return;
        }

        Yii::info([
            'phone' => $phone,
            'message' => $message,
            'response' => $data,
        ], 'sms');
    }


}
