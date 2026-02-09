<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */

/** @var app\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Вход';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login d-flex justify-content-center">
    <div class="card shadow-sm" style="max-width: 420px; width:100%;">
        <div class="card-body">
            <h3 class="card-title mb-3 text-center"><?= Html::encode($this->title) ?></h3>

            <?php $form = ActiveForm::begin([
                'id' => 'login-form',
            ]); ?>

            <?= $form->field($model, 'Имя')->textInput(['autofocus' => true])->label('Имя пользователя') ?>

            <?= $form->field($model, 'пароль')->passwordInput()->label('Пароль') ?>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <?= $form->field($model, 'rememberMe')->checkbox()->label('Запомнить меня') ?>
                </div>
                <div>
                    <?= Html::submitButton('Войти', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
                </div>
            </div>

            <?php ActiveForm::end(); ?>

            <div class="text-center text-muted small">
                Новый пользователь? <?= Html::a('Зарегистрироваться', ['site/register']) ?>
            </div>
        </div>
    </div>
</div>
