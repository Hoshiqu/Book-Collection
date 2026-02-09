<?php

namespace app\components\sms;

interface SmsClientInterface
{
    public function send(string $phone, string $message): void;
}
