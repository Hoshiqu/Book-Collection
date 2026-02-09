<?php

use yii\helpers\Html;

/** @var app\models\Author[] $authors */

$this->title = 'Авторы';
$this->params['breadcrumbs'][] = $this->title;
?>

<h1 class="mb-4">Авторы</h1>

<?php if (!Yii::$app->user->isGuest): ?>
    <p class="mb-3"><?= Html::a('Добавить автора', ['author/create'], ['class' => 'btn btn-primary']) ?></p>
<?php endif; ?>

<?php
function plural($n, $one, $few, $many) {
    $n = abs($n) % 100;
    $n1 = $n % 10;
    if ($n > 10 && $n < 20) return $many;
    if ($n1 > 1 && $n1 < 5) return $few;
    if ($n1 == 1) return $one;
    return $many;
}
?>

<?php if (empty($authors)): ?>
    <div class="alert alert-info">
        No authors found.
    </div>
<?php else: ?>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php foreach ($authors as $author): ?>
            <?php 
            $bookCount = count($author->books);
            $publishedBooks = array_filter($author->books, fn($book) => $book->status === \app\models\Book::STATUS_PUBLISHED);
            $publishedCount = count($publishedBooks);
            ?>
            <div class="col">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <?= Html::encode($author->full_name) ?>
                        </h5>
                        
                        <div class="d-flex flex-column gap-2">
                            <div class="d-flex align-items-center text-muted">
                                <span>
                                    <strong><?= $publishedCount ?></strong> 
                                    <?= $publishedCount === 1 ? 'Книг' : 'Книги' ?> опубликовано
                                </span>
                            </div>
                            
                            <?php if ($bookCount > $publishedCount): ?>
                                <div class="d-flex align-items-center text-muted">
                                    <span class="me-2">📝</span>
                                    <span>
                                        <strong><?= $bookCount - $publishedCount ?></strong> 
                                        <?= ($bookCount - $publishedCount) === 1 ? 'черновик' : 'черновика' ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card-footer bg-white border-top d-flex gap-1 flex-wrap">
                        <?= Html::a('Посмотреть книги →', ['author/view', 'id' => $author->id], ['class' => 'btn btn-sm btn-primary']) ?>
                        <?php if (!Yii::$app->user->isGuest): ?>
                            <?= Html::a('Изменить', ['author/update', 'id' => $author->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                            <?= Html::a('Удалить', ['author/delete', 'id' => $author->id], [
                                'class' => 'btn btn-sm btn-outline-danger',
                                'data-method' => 'post',
                                'data-confirm' => 'Удалить автора?',
                            ]) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
