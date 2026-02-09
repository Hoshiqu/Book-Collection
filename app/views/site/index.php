<?php

use yii\helpers\Html;

/**
 * @var int $booksCount
 * @var int $authorsCount
 * @var int $genresCount
 */

$this->title = 'Книжная полка';
?>

<div class="site-index">

    <!-- HERO -->
    <div class="p-5 mb-5 bg-light rounded-3">
        <div class="container-fluid py-4">
            <h1 class="display-5 fw-bold">
                Книжная полка
            </h1>

            <p class="col-md-8 fs-5 text-muted">
                Онлайн-каталог книг с авторами и подписками на новые издания.
            </p>

            <div class="mt-4">
                <?= Html::a(
                    'Посмотреть книги',
                    ['book/index'],
                    ['class' => 'btn btn-primary btn-lg me-2']
                ) ?>

                <?= Html::a(
                    'Авторы',
                    ['author/index'],
                    ['class' => 'btn btn-outline-secondary btn-lg']
                ) ?>
            </div>
        </div>
    </div>

    <!-- STATS -->
    <div class="row text-center mb-5">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="fw-bold"><?= $booksCount ?></h2>
                    <p class="text-muted mb-0">Книг</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="fw-bold"><?= $authorsCount ?></h2>
                    <p class="text-muted mb-0">Авторов</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="fw-bold"><?= $genresCount ?></h2>
                    <p class="text-muted mb-0">Жанров</p>
                </div>
            </div>
        </div>
    </div>

    <!-- QUICK LINKS -->
    <div class="row">

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">📚 Книги</h5>
                    <p class="card-text text-muted">
                        Посмотреть все книги и отфильтровать по жанрам.
                    </p>
                    <?= Html::a('Открыть книги', ['book/index'], ['class' => 'btn btn-sm btn-primary']) ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">✍️ Авторы</h5>
                    <p class="card-text text-muted">
                        Посмотреть авторов и их опубликованные книги.
                    </p>
                    <?= Html::a('Открыть авторов', ['author/index'], ['class' => 'btn btn-sm btn-primary']) ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">📊 Топ авторы</h5>
                    <p class="card-text text-muted">
                        Top 10 авторов по количеству книг в году.
                    </p>
                    <?= Html::a('Посмотреть отчет', ['report/top-authors'], ['class' => 'btn btn-sm btn-primary']) ?>
                </div>
            </div>
        </div>

    </div>

</div>
