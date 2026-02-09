<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $subscription_id
 * @property int $book_id
 * @property int $sent_at
 */
class SubscriptionEvent extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%subscription_event}}';
    }

    public function rules(): array
    {
        return [
            [['subscription_id', 'book_id', 'sent_at'], 'required'],
            [['subscription_id', 'book_id', 'sent_at'], 'integer'],
            [['subscription_id', 'book_id'], 'unique', 'targetAttribute' => ['subscription_id', 'book_id']],
        ];
    }
}
