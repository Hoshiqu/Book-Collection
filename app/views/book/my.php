<?php

use yii\helpers\Html;

/** @var app\models\Book[] $books */

$this->title = 'My books';
?>

<h1><?= Html::encode($this->title) ?></h1>

<?php if (empty($books)): ?>
    <div class="alert alert-info">
        You have no books yet.
    </div>
<?php else: ?>
    <table class="table table-bordered align-middle">
        <thead>
        <tr>
            <th>Название</th>
            <th>Авторы</th>
            <th>Статус</th>
            <th style="width: 180px;">Действия</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($books as $book): ?>
            <tr>
                <td><?= Html::encode($book->title) ?></td>
                <td>
                    <?= implode(', ', array_map(
                        fn($a) => Html::encode($a->full_name),
                        $book->authors
                    )) ?>
                </td>
                <td>
                    <span class="badge bg-<?= $book->isPublished() ? 'success' : 'secondary' ?>">
                        <?= Html::encode($book->status) ?>
                    </span>
                </td>
                <td>
                    <?php if (!$book->isPublished()): ?>
                        <?= Html::a(
                            'Publish',
                            ['publish', 'id' => $book->id],
                            ['class' => 'btn btn-sm btn-success']
                        ) ?>
                    <?php endif; ?>

                    <?= Html::a(
                        'Delete',
                        ['delete', 'id' => $book->id],
                        [
                            'class' => 'btn btn-sm btn-danger',
                            'data-method' => 'post',
                            'data-confirm' => 'Удалить книгу?',
                        ]
                    ) ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
