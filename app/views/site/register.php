<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Регистрация';
?>

<div class="d-flex justify-content-center">
    <div class="card shadow-sm" style="max-width:480px; width:100%">
        <div class="card-body">
            <h3 class="card-title mb-3 text-center">Регистрация</h3>

            <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($model, 'Имя') ?>
            <?= $form->field($model, 'Пароль')->passwordInput() ?>
            <?= $form->field($model, 'Повтор пароля')->passwordInput() ?>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <?= Html::a('Уже есть аккаунт? Войти', ['site/login']) ?>
                <?= Html::submitButton('Зарегистрироваться', ['class' => 'btn btn-primary']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
