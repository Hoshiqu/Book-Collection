<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var $author app\models\Author */

$this->title = $author->full_name;
$this->params['breadcrumbs'][] = ['label' => 'Авторы', 'url' => ['author/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-md-8">

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="card-title mb-3">
                    <?= Html::encode($author->full_name) ?>
                </h2>

                <p class="text-muted mb-0">
                    Книги автора.
                </p>

                <?php if (!Yii::$app->user->isGuest): ?>
                    <div class="mt-3">
                        <?= Html::a('Редактировать автора', ['author/update', 'id' => $author->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                        <?= Html::a('Удалить автора', ['author/delete', 'id' => $author->id], [
                            'class' => 'btn btn-sm btn-outline-danger',
                            'data-method' => 'post',
                            'data-confirm' => 'Delete this author?',
                        ]) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (empty($books)): ?>
            <div class="alert alert-secondary">
                Книг автора пока нет.
            </div>
        <?php else: ?>
            <?php foreach ($books as $book): ?>
                <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <div class="row">

                            <!-- COVER -->
                            <?php if (!empty($book->cover_path)): ?>
                                <div class="col-md-3 mb-3 mb-md-0">
                                    <div style="
                                        width: 120px;
                                        aspect-ratio: 2 / 3;
                                        overflow: hidden;
                                        border-radius: 6px;
                                        background: #f1f1f1;
                                    ">
                                        <img src="<?= Html::encode($book->cover_path) ?>"
                                            alt="<?= Html::encode($book->title) ?>"
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

                            <!-- CONTENT -->
                            <div class="<?= !empty($book->cover_path) ? 'col-md-9' : 'col-md-12' ?>">

                                <!-- TITLE -->
                                <h5 class="card-title mb-1">
                                    <?= Html::encode($book->title) ?>
                                    <?php if ($book->published_year): ?>
                                        <small class="text-muted">
                                            (<?= Html::encode($book->published_year) ?>)
                                        </small>
                                    <?php endif; ?>
                                </h5>

                                <!-- DESCRIPTION (FULL) -->
                                <?php if (!empty($book->description)): ?>
                                    <p class="card-text mt-2">
                                        <?= nl2br(Html::encode($book->description)) ?>
                                    </p>
                                <?php else: ?>
                                    <p class="text-muted mt-2 mb-0">
                                        Нет описания.
                                    </p>
                                <?php endif; ?>

                                <!-- GENRES -->
                                <?php if (!empty($book->genres)): ?>
                                    <div class="mt-3">
                                        <?php foreach ($book->genres as $genre): ?>
                                            <span class="badge bg-secondary me-1">
                                                <?= Html::encode($genre->name) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                            </div>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>


        <?php endif; ?>

    </div>


    <div class="col-md-4">

        <div class="card border-primary shadow-sm subscribe-sms-card">
            <div class="card-body subscribe-sms-card-body">
                <h5 class="card-title">
                    Подпишиться на SMS-уведомления о новых книгах автора
                </h5>

                <p class="text-muted small">
                    Получайте SMS-уведомления о новых книгах автора.
                </p>

                <?php $form = ActiveForm::begin([
                    'action' => ['author/subscribe', 'id' => $author->id],
                    'method' => 'post',
                    'options' => ['class' => 'subscribe-sms-ajax'],
                ]); ?>

                <div class="mb-3">
                    <?= Html::label('Номер телефона', 'phone', ['class' => 'form-label']) ?>
                    <?= Html::input('text', 'phone', '', [
                        'class' => 'form-control',
                        'id' => 'subscribe-phone',
                        'placeholder' => '+7 999 000-11-22',
                        'required' => true,
                    ]) ?>
                </div>

                <?= Html::submitButton(
                    'Подписаться',
                    ['class' => 'btn btn-primary w-100']
                ) ?>

                <?php ActiveForm::end(); ?>
            </div>
        </div>

    </div>
</div>

<?php
$this->registerJs(<<<JS
document.addEventListener('submit', function(e) {
    if (!e.target || !e.target.classList.contains('subscribe-sms-ajax')) return;
    e.preventDefault();
    var form = e.target;
    var btn = form.querySelector('[type=submit]');
    var origText = btn.textContent;
    btn.disabled = true;
    btn.textContent = '…';
    var body = new FormData(form);
    fetch(form.action, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: body })
        .then(function(r) { return r.json().then(function(d) { return r.ok ? d : Promise.reject(d.error || d); }); })
        .then(function(d) {
            if (!d.success) throw new Error(d.error || 'Failed');
            if (typeof showNotification === 'function') showNotification('You have subscribed successfully.');
            var cardBody = form.closest('.subscribe-sms-card-body');
            if (cardBody) {
                cardBody.style.opacity = '0.7';
                cardBody.innerHTML = '<p class="text-success mb-0">You are subscribed to SMS updates for this author.</p>';
            }
        })
        .catch(function(err) {
            btn.disabled = false;
            btn.textContent = origText;
            if (typeof showNotification === 'function') showNotification(typeof err === 'string' ? err : (err.message || 'Error'), 'error');
        });
    return false;
});
JS
, \yii\web\View::POS_READY);
?>
