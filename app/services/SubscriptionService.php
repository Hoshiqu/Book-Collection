<?php

namespace app\services;

use app\models\Author;
use app\models\Subscription;
use yii\db\Exception;

class SubscriptionService
{
    /**
     * Подписка гостя на автора
     *
     * @throws Exception
     */
    public function subscribe(int $authorId, string $phone): Subscription
    {
        $author = Author::findOne($authorId);
        if ($author === null) {
            throw new Exception('Автор не найден');
        }

        $phone = $this->normalizePhone($phone);

        $exists = Subscription::find()
            ->where([
                'author_id' => $authorId,
                'phone' => $phone,
            ])
            ->exists();

        if ($exists) {
            throw new Exception('Subscription already exists');
        }

        $subscription = new Subscription();
        $subscription->author_id = $authorId;
        $subscription->phone = $phone;

        if (!$subscription->save()) {
            throw new Exception('Failed to save subscription');
        }

        return $subscription;
    }

    private function normalizePhone(string $phone): string
    {
        // оставляем только цифры и +
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // если номер без +, считаем что это РФ
        if ($phone !== '' && $phone[0] !== '+') {
            $phone = '+7' . $phone;
        }

        return $phone;
    }
}
