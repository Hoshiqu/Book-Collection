<?php

/** @var yii\web\View $this */
/** @var string $name */
/** @var string $message */
/** @var Exception $exception */

use yii\helpers\Html;

$this->title = 'Что-то пошло не так';
?>
<div class="site-error d-flex justify-content-center mt-5">

    <div class="card shadow-sm" style="max-width: 600px; width: 100%;">
        <div class="card-body text-center">

            <h1 class="mb-3">😕 Ошибка</h1>

            <p class="text-muted mb-3">
                К сожалению, при обработке запроса произошла ошибка.
            </p>

            <div class="alert alert-danger text-start">
                <?= nl2br(Html::encode($message)) ?>
            </div>

            <p class="text-muted mt-3">
                Вы можете вернуться на главную страницу и продолжить работу.
            </p>

            <div class="d-flex justify-content-center gap-2 mt-4">
                <?= Html::a(
                    '🏠 На главную',
                    Yii::$app->homeUrl,
                    ['class' => 'btn btn-primary']
                ) ?>
            </div>

            <hr class="my-4">

            <p class="text-muted small mb-0">
                Если проблема повторяется, напишите в Telegram:
                <br>
                <a href="https://t.me/groft" target="_blank">@groft</a>
            </p>

        </div>
    </div>

</div>
