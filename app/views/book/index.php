<?php

use yii\helpers\Html;

/**
 * @var app\models\Book[] $books
 * @var app\models\Genre[] $genres
 * @var int|null $activeGenre
 */

$this->title = 'Books';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="book-index">

    <h1 class="mb-4"><?= Html::encode($this->title) ?></h1>

    <div class="row">

        <!-- LEFT SIDEBAR: GENRES -->
        <div class="col-md-3">
            <div class="list-group mb-4">

                <?= Html::a(
                    'All genres',
                    ['book/index'],
                    [
                        'class' =>
                            'list-group-item list-group-item-action ' .
                            ($activeGenre === null ? 'active' : '')
                    ]
                ) ?>

                <?php foreach ($genres as $genre): ?>
                    <?= Html::a(
                        Html::encode($genre->name),
                        ['book/index', 'genre_id' => $genre->id],
                        [
                            'class' =>
                                'list-group-item list-group-item-action ' .
                                ((int)$activeGenre === (int)$genre->id ? 'active' : '')
                        ]
                    ) ?>
                <?php endforeach; ?>

            </div>
        </div>

        <!-- MAIN CONTENT: BOOKS -->
        <div class="col-md-9">

            <?php if (empty($books)): ?>
                <div class="alert alert-info">
                    No books found.
                </div>
            <?php else: ?>

                <?php foreach ($books as $book): ?>
                    <div class="card mb-3 shadow-sm">
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

                                <!-- ADD TO LIBRARY (только для чужих книг; AJAX без редиректа) -->
                                <div class="col-md-2 text-end library-actions" data-book-id="<?= $book->id ?>">
    <?php if (!Yii::$app->user->isGuest && $book->user_id != Yii::$app->user->id): ?>

        <?php if (!$book->isInLibrary(Yii::$app->user->id)): ?>
            <?= Html::beginForm(['library/add', 'id' => $book->id], 'post', ['class' => 'd-inline']) ?>
            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
            <?= Html::submitButton('➕', [
                'class' => 'btn btn-sm btn-outline-success',
                'title' => 'Add to library',
            ]) ?>
            <?= Html::endForm() ?>
        <?php else: ?>
            <?= Html::beginForm(['library/remove', 'id' => $book->id], 'post', [
                'class' => 'd-inline library-ajax-form',
                'data-action' => 'remove'
            ]) ?>
            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
            <?= Html::submitButton('❌', [
                'class' => 'btn btn-sm btn-outline-danger',
                'title' => 'Remove from library',
            ]) ?>
            <?= Html::endForm() ?>
        <?php endif; ?>

        <br>

        <?= Html::a(
            '✏️',
            ['book/update', 'id' => $book->id],
            [
                'class' => 'btn btn-sm btn-outline-secondary mt-1',
                'title' => 'Edit book',
            ]
        ) ?>

    <?php endif; ?>
</div>


                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

            <?php endif; ?>

        </div>
    </div>
</div>

<?php
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
$this->registerJs(<<<JS
(function() {
    document.addEventListener('submit', function(e) {
        if (!e.target || !e.target.classList.contains('library-ajax-form')) return;
        e.preventDefault();
        var form = e.target;
        if (!confirm('Remove this book from your library?')) return;
        var btn = form.querySelector('[type=submit]');
        var origText = btn.textContent;
        btn.disabled = true;
        btn.textContent = '…';
        var body = new FormData(form);
        fetch(form.action, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: body })
            .then(function(r) { return r.json().then(function(d) { return r.ok ? d : Promise.reject(d.error || d); }); })
            .then(function(d) {
                if (!d.success) throw new Error(d.error || 'Failed');
                if (typeof showNotification === 'function') showNotification('Book removed from library');
                var wrap = form.closest('.library-actions');
                var cp = document.querySelector('meta[name="csrf-param"]');
                var ct = document.querySelector('meta[name="csrf-token"]');
                cp = cp ? cp.getAttribute('content') : '<?= addslashes($csrfParam) ?>';
                ct = ct ? ct.getAttribute('content') : '<?= addslashes($csrfToken) ?>';
                var addUrl = form.action.replace(/\/remove/, '/add');
                wrap.innerHTML = '<form class="d-inline" method="post" action="'+addUrl+'">' +
                    '<input type="hidden" name="'+cp+'" value="'+ct.replace(/"/g, '&quot;')+'">' +
                    '<button type="submit" class="btn btn-sm btn-outline-success" title="Add to library">➕</button></form>';
            })
            .catch(function(err) {
                btn.disabled = false;
                btn.textContent = origText;
                if (typeof showNotification === 'function') showNotification(typeof err === 'string' ? err : (err.message || 'Error'), 'error');
            });
        return false;
    });
})();
JS
, \yii\web\View::POS_READY);
?>
