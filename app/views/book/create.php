<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Genre;
/**
 * @var yii\web\View $this
 * @var app\models\Book $model
 * @var yii\widgets\ActiveForm $form
 */

$this->title = 'Добавить книгу';
$this->params['breadcrumbs'][] = ['label' => 'Моя библиотека', 'url' => ['book/my']];
$this->params['breadcrumbs'][] = $this->title;

$isNew = $model->isNewRecord;
?>

<div class="book-create">

    <h1 class="mb-4"><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin([
        'action' => $isNew
            ? ['book/create']
            : ['book/update', 'id' => $model->id],
        'options' => ['class' => 'card card-body', 'enctype' => 'multipart/form-data'],
    ]); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'published_year')->input('number') ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 5]) ?>

    <?= $form->field($model, 'coverFile')->fileInput([
        'accept' => 'image/*',
        'id' => 'cover-file-input'
    ]) ?>

    <div class="d-flex align-items-center gap-2 mt-1">
        <button type="button"
                class="btn btn-sm btn-outline-secondary"
                id="paste-cover-btn">
            📋 Вставить из буфера обмена.
        </button>

        <small class="text-muted">
            Вы можете нажать <b>Ctrl+V</b> что бы вставить картинку из буфера обмена.
        </small>
    </div>


    <?php if (!$model->isNewRecord && $model->cover_path): ?>
        <div class="mb-3">
            <label class="form-label">Текущая обложка:</label><br>
            <div style="
                width: 120px;
                aspect-ratio: 2 / 3;
                overflow: hidden;
                border-radius: 6px;
                background: #f1f1f1;
            ">
                <img src="<?= Html::encode($model->cover_path) ?>"
                    alt="<?= Html::encode($model->title) ?>"
                    style="
                        width: 100%;
                        height: 100%;
                        object-fit: cover;
                        object-position: center;
                        display: block;
                    ">
            </div>

        </div>
    <?php endif; ?>



    <!-- АВТОРЫ -->
    <?php
    use app\models\Author;

    $authors = Author::find()
        ->orderBy('full_name')
        ->select(['full_name', 'id'])
        ->indexBy('id')
        ->column();
    ?>
    
    <?= $form->field($model, 'authorIds')->checkboxList($authors) ?>
    <!-- ЖАНРЫ -->
    <?php
    $genres = Genre::find()
        ->orderBy('name')
        ->select(['name', 'id'])
        ->indexBy('id')
        ->column();
    ?>

    <?= $form->field($model, 'genreIds')->checkboxList($genres) ?>

    <?= $form->field($model, 'newGenres')
        ->textInput([
            'placeholder' => 'Добавить новые жанры (через запятую)',
        ])
        ->hint('Пример: SQL, Data Science, Big Data')
    ?>

    <div class="mt-4 d-flex gap-2">
        <?= Html::submitButton(
            $isNew ? 'Создать книгу' : 'Сохранить изменения',
            ['class' => 'btn btn-primary']
        ) ?>

        <?php if (!$isNew && $model->status === 'draft'): ?>
            <?= Html::a(
                'Publish',
                ['book/publish', 'id' => $model->id],
                [
                    'class' => 'btn btn-success',
                    'data-confirm' => 'Опубликовать книгу?',
                    'data-method' => 'post',
                ]
            ) ?>
        <?php endif; ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$this->registerJs(<<<JS
(function () {

    function showNewCoverPreview(file) {
        const input = document.getElementById('cover-file-input');
        if (!input) return;

        // контейнер со старыми/новыми обложками
        let wrapper = document.getElementById('cover-preview-wrapper');

        if (!wrapper) {
            wrapper = document.createElement('div');
            wrapper.id = 'cover-preview-wrapper';
            wrapper.className = 'mb-3 d-flex gap-3 align-items-start';
            input.closest('.form-group')?.appendChild(wrapper);
        }

        // блок новой обложки
        let box = document.getElementById('new-cover-preview');
        if (!box) {
            box = document.createElement('div');
            box.id = 'new-cover-preview';
            box.innerHTML = `
                <label class="form-label">New cover:</label><br>
                <div style="
                    width: 120px;
                    aspect-ratio: 2 / 3;
                    overflow: hidden;
                    border-radius: 6px;
                    background: #f1f1f1;
                ">
                    <img style="
                        width: 100%;
                        height: 100%;
                        object-fit: cover;
                        object-position: center;
                        display: block;
                    ">
                </div>
            `;
            wrapper.appendChild(box);
        }

        const img = box.querySelector('img');
        const reader = new FileReader();
        reader.onload = function (e) {
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }

    // 📋 Paste image from clipboard
    document.addEventListener('paste', function (event) {
        const items = (event.clipboardData || event.originalEvent.clipboardData).items;
        for (const item of items) {
            if (item.type.startsWith('image/')) {
                const file = item.getAsFile();
                const input = document.getElementById('cover-file-input');
                if (!input) return;

                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                input.files = dataTransfer.files;

                showNewCoverPreview(file);
                return;
            }
        }
    });

    // 📁 Select file manually
    document.getElementById('cover-file-input')?.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            showNewCoverPreview(this.files[0]);
        }
    });

})();
JS);
?>


