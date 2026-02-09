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
        $this->sender = Yii::$app->params['sms']['sender'] ?? null;
    }

    public function send(string $phone, string $message): void
    {
        // Нормализация номера под требования SMSPilot (без "+")
        $phone = preg_replace('/\D+/', '', $phone);

        if (strlen($phone) < 10) {
            Yii::warning([
                'reason' => 'invalid_phone',
                'phone' => $phone,
            ], 'sms');
            return;
        }

        $response = $this->client->post('', [
            'apikey' => $this->apiKey,
            'to'     => $phone,
            'send'   => $message,
            'from'   => $this->sender,
            'format' => 'json',
            'test'   => 1,
        ])->send();

        // HTTP-уровень
        if (!$response->isOk) {
            Yii::error([
                'error' => 'SMS HTTP error',
                'status' => $response->statusCode,
            ], 'sms');
            return;
        }

        $data = $response->data;

        // Ошибка API (в test-режиме возможно)
        if (isset($data['error'])) {
            Yii::warning([
                'reason'  => $data['error']['description'] ?? 'unknown',
                'phone'   => $phone,
                'message' => $message,
                'mode'    => 'test',
            ], 'sms');

            return;
        }

        Yii::info([
            'phone'    => $phone,
            'message'  => $message,
            'response' => $data,
            'mode'     => 'test',
        ], 'sms');
    }
}
