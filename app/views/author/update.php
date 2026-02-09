<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var app\models\Author $model */

$this->title = 'Изменить автора';
$this->params['breadcrumbs'][] = ['label' => 'Авторы', 'url' => ['author/index']];
$this->params['breadcrumbs'][] = ['label' => $model->full_name, 'url' => ['author/view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Изменить';
?>

<h1 class="mb-4"><?= Html::encode($this->title) ?></h1>

<div class="card card-body">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'full_name')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Отмена', ['author/view', 'id' => $model->id], ['class' => 'btn btn-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
