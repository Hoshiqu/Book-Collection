<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Register';
?>

<h1>Register</h1>

<?php $form = ActiveForm::begin(); ?>

<?= $form->field($model, 'Имя') ?>
<?= $form->field($model, 'Пароль')->passwordInput() ?>
<?= $form->field($model, 'Повтор пароля')->passwordInput() ?>

<div class="form-group mt-3">
    <?= Html::submitButton('Зарегистрироваться', ['class' => 'btn btn-primary']) ?>
</div>

<?php ActiveForm::end(); ?>
