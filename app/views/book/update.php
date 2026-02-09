<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Genre;
use app\models\Author;

/**
 * @var yii\web\View $this
 * @var app\models\Book $model
 */

$isNew = false;

$this->title = 'Edit book';
$this->params['breadcrumbs'][] = ['label' => 'My books', 'url' => ['book/my']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="book-update">

    <h1 class="mb-4"><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin([
        'action' => ['book/update', 'id' => $model->id],
        'options' => [
            'class' => 'card card-body',
            'enctype' => 'multipart/form-data',
        ],
    ]); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'published_year')->input('number') ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 5]) ?>

    <?= $form->field($model, 'coverFile')->fileInput([
        'accept' => 'image/*',
        'id' => 'cover-file-input',
    ]) ?>
    <?= Html::hiddenInput('removeCover', '0', ['id' => 'remove-cover-flag']) ?>

    <div class="d-flex align-items-center gap-2 mt-1">
        <button type="button"
                class="btn btn-sm btn-outline-secondary"
                id="paste-cover-btn">
            📋 Paste from clipboard
        </button>

        <small class="text-muted">
            You can also press <b>Ctrl+V</b> to paste an image
        </small>
    </div>

    <?php if (!$model->isNewRecord && $model->cover_path): ?>
        <div class="mb-3 d-flex gap-3 align-items-start" id="cover-preview-wrapper">
            <div>
                <label class="form-label">Current cover:</label><br>

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

                <div class="mt-2">
                    <button type="button"
                            class="btn btn-sm btn-outline-danger"
                            id="remove-cover-btn">
                        🗑 Remove cover
                    </button>
                </div>
            </div>

            <!-- сюда JS добавит новую -->
        </div>
    <?php endif; ?>



     <?php
    $authors = Author::find()
        ->orderBy('full_name')
        ->select(['full_name', 'id'])
        ->indexBy('id')
        ->column();
    ?>

    <?= $form->field($model, 'authorIds')->checkboxList($authors) ?>

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
            'placeholder' => 'Add new genres (comma separated)',
        ])
        ->hint('Example: SQL, Data Science, Big Data')
    ?>


    <div class="mt-4 d-flex gap-2">
        <?= Html::submitButton('Save changes', ['class' => 'btn btn-primary']) ?>

        <?= Html::a(
            'Cancel',
            ['book/index'],
            ['class' => 'btn btn-secondary']
        ) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$this->registerJs(<<<JS
(function () {

    const input = document.getElementById('cover-file-input');

    function ensureWrapper() {
        let wrapper = document.getElementById('cover-preview-wrapper');
        if (!wrapper && input) {
            wrapper = document.createElement('div');
            wrapper.id = 'cover-preview-wrapper';
            wrapper.className = 'mb-3 d-flex gap-3 align-items-start';
            input.closest('.form-group')?.appendChild(wrapper);
        }
        return wrapper;
    }

    function showNewCoverPreview(file) {
        if (!input) return;

        const wrapper = ensureWrapper();
        if (!wrapper) return;

        // сбрасываем флаг удаления, если он был
        const removeFlag = document.getElementById('remove-cover-flag');
        if (removeFlag) removeFlag.value = '0';

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
        reader.onload = e => img.src = e.target.result;
        reader.readAsDataURL(file);
    }

    // 📁 Выбор файла вручную
    input?.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            showNewCoverPreview(this.files[0]);
        }
    });

    // 📋 Вставка из буфера
    document.addEventListener('paste', function (event) {
        const items = (event.clipboardData || event.originalEvent.clipboardData)?.items || [];
        for (const item of items) {
            if (item.type.startsWith('image/')) {
                const file = item.getAsFile();
                if (!file || !input) return;

                const dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;

                showNewCoverPreview(file);
                event.preventDefault();
                return;
            }
        }
    });

    // 🗑 Удаление текущей обложки
    document.getElementById('remove-cover-btn')?.addEventListener('click', function () {
        if (!confirm('Remove cover image?')) return;

        const flag = document.getElementById('remove-cover-flag');
        if (flag) flag.value = '1';

        // визуально приглушаем старую обложку
        const wrapper = document.getElementById('cover-preview-wrapper');
        if (wrapper) wrapper.style.opacity = '0.4';

        // сбрасываем input
        if (input) input.value = '';

        // убираем превью новой
        const newPreview = document.getElementById('new-cover-preview');
        if (newPreview) newPreview.remove();
    });

})();
JS);
?>


