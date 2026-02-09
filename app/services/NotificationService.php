<?php

namespace app\services;

use app\components\sms\SmsClientInterface;
use app\models\Author;
use app\models\Book;
use app\models\Subscription;
use app\models\SubscriptionEvent;
use Yii;

class NotificationService
{
    private SmsClientInterface $sms;

    public function __construct()
    {
        $this->sms = Yii::$app->smsClient;
    }

    public function notifyNewBooks(): int
    {
        $sent = 0;

        $subscriptions = Subscription::find()->all();

        foreach ($subscriptions as $subscription) {
            $books = Book::find()
                ->joinWith('authors')
                ->where(['author.id' => $subscription->author_id])
                ->all();

            foreach ($books as $book) {
                $alreadySent = SubscriptionEvent::find()
                    ->where([
                        'subscription_id' => $subscription->id,
                        'book_id' => $book->id,
                    ])
                    ->exists();

                if ($alreadySent) {
                    continue;
                }

                $message = sprintf(
                    'New book by %s: %s (%d)',
                    $book->authors[0]->full_name,
                    $book->title,
                    $book->published_year
                );

                $this->sms->send($subscription->phone, $message);

                $event = new SubscriptionEvent();
                $event->subscription_id = $subscription->id;
                $event->book_id = $book->id;
                $event->sent_at = time();
                $event->save();

                $sent++;
            }
        }

        return $sent;
    }

    /**
     * Отправить SMS подписчикам авторов книги о появлении этой книги (вызывать при публикации).
     */
    public function notifyAboutBook(Book $book): int
    {
        $authorIds = array_map(fn($a) => $a->id, $book->authors);
        if (empty($authorIds)) {
            return 0;
        }

        $sent = 0;
        $subscriptions = Subscription::find()->where(['author_id' => $authorIds])->all();

        foreach ($subscriptions as $subscription) {
            if (SubscriptionEvent::find()->where([
                'subscription_id' => $subscription->id,
                'book_id' => $book->id,
            ])->exists()) {
                continue;
            }

            $author = Author::findOne($subscription->author_id);
            $authorName = $author ? $author->full_name : 'Author';
            $message = sprintf(
                'New book by %s: %s (%d)',
                $authorName,
                $book->title,
                $book->published_year
            );

            try {
                $this->sms->send($subscription->phone, $message);
            } catch (\Throwable $e) {
                Yii::error('SMS send failed: ' . $e->getMessage(), __METHOD__);
                continue;
            }

            $event = new SubscriptionEvent();
            $event->subscription_id = $subscription->id;
            $event->book_id = $book->id;
            $event->sent_at = time();
            $event->save(false);
            $sent++;
        }

        return $sent;
    }
}
