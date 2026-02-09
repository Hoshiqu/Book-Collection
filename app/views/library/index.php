<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Book[] $books */

$this->title = 'Моя библиотека';
$this->params['breadcrumbs'][] = $this->title;
?>

<h1 class="mb-4">📚 Библиотека</h1>

<?php if (empty($books)): ?>
    <p class="text-muted">Ваша библиотека пуста.</p>
<?php else: ?>

    <?php foreach ($books as $book): ?>
        <div class="card mb-3 shadow-sm library-book-card" data-book-id="<?= $book->id ?>">
            <div class="card-body">
                <div class="row">
                    <!-- COVER -->
                    <?php if ($book->cover_path): ?>
                        <div class="col-md-2 mb-3 mb-md-0">
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
                    <div class="<?= $book->cover_path ? 'col-md-8' : 'col-md-10' ?>">
                        <!-- TITLE -->
                        <h5 class="card-title mb-1">
                            <?= Html::encode($book->title) ?>
                            <?php if ($book->published_year): ?>
                                <small class="text-muted">
                                    (<?= Html::encode($book->published_year) ?>)
                                </small>
                            <?php endif; ?>
                        </h5>

                        <!-- AUTHORS -->
                        <?php if (!empty($book->authors)): ?>
                            <div class="text-muted mb-2">
                                <?php foreach ($book->authors as $i => $author): ?>
                                    <?= Html::a(
                                        Html::encode($author->full_name),
                                        ['author/view', 'id' => $author->id],
                                        ['class' => 'text-decoration-none']
                                    ) ?>
                                    <?= $i < count($book->authors) - 1 ? ', ' : '' ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- DESCRIPTION -->
                        <?php if ($book->description): ?>
                            <p class="card-text text-muted mb-2">
                                <?= Html::encode(mb_substr($book->description, 0, 200)) ?>
                                <?= mb_strlen($book->description) > 200 ? '...' : '' ?>
                            </p>
                        <?php endif; ?>

                        <!-- ISBN -->
                        <?php if ($book->isbn): ?>
                            <small class="text-muted d-block mb-2">
                                ISBN: <?= Html::encode($book->isbn) ?>
                            </small>
                        <?php endif; ?>

                        <!-- GENRES -->
                        <?php if (!empty($book->genres)): ?>
                            <div class="mt-2">
                                <?php foreach ($book->genres as $genre): ?>
                                    <span class="badge bg-secondary me-1">
                                        <?= Html::encode($genre->name) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- ACTIONS -->
                    <div class="col-md-2 text-end">

    <?php if ($book->user_id === Yii::$app->user->id): ?>

        <!-- DELETE FROM SITE -->
        <?= Html::beginForm(['/book/delete', 'id' => $book->id], 'post', [
            'class' => 'd-inline book-delete-ajax'
        ]) ?>
        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
        <?= Html::submitButton('🗑️', [
            'class' => 'btn btn-sm btn-danger',
            'title' => 'Удалить книгу с сайта (для всех пользователей)',
        ]) ?>
        <?= Html::endForm() ?>

    <?php else: ?>

        <!-- REMOVE FROM LIBRARY -->
        <?= Html::beginForm(['/library/remove', 'id' => $book->id], 'post', [
            'class' => 'd-inline library-remove-ajax'
        ]) ?>
        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
        <?= Html::submitButton('❌', [
            'class' => 'btn btn-sm btn-outline-danger',
            'title' => 'Удалить из библиотеки',
        ]) ?>
        <?= Html::endForm() ?>

    <?php endif; ?>

    <br>

    <!-- EDIT — ВСЕГДА -->
    <?= Html::a(
        '✏️',
        ['book/update', 'id' => $book->id],
        [
            'class' => 'btn btn-sm btn-outline-secondary mt-1',
            'title' => 'Редактировать книгу',
        ]
    ) ?>

</div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

<?php endif; ?>

<?php
$this->registerJs(<<<JS
document.addEventListener('submit', function(e) {
    var form = e.target;
    if (!form || !form.classList.contains('library-remove-ajax') && !form.classList.contains('book-delete-ajax')) return;
    e.preventDefault();
    var isDelete = form.classList.contains('book-delete-ajax');
    var msg = isDelete ? 'Это удалит книгу для всех пользователей. Продолжить?' : 'Удалить книгу из вашей библиотеки?';
    if (!confirm(msg)) return;
    var btn = form.querySelector('[type=submit]');
    var origText = btn.textContent;
    btn.disabled = true;
    btn.textContent = '…';
    var body = new FormData(form);
    fetch(form.action, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: body })
        .then(function(r) { return r.json().then(function(d) { return r.ok ? d : Promise.reject(d.error || d); }); })
        .then(function(d) {
            if (!d.success) throw new Error(d.error || 'Failed');
            if (typeof showNotification === 'function') showNotification(isDelete ? 'Книга удалена' : 'Книга удалена из библиотеки');
            var card = form.closest('.library-book-card');
            if (card) card.style.opacity = '0.5';
            setTimeout(function() { if (card) card.remove(); }, 300);
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
