<?php

return [
    'class' => yii\db\Connection::class,
    'dsn' => 'mysql:host=db;dbname=yii2_books',
    'username' => 'yii2',
    'password' => 'yii2',
    'charset' => 'utf8mb4',

    'attributes' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ],
];
