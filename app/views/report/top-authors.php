<?php

use yii\helpers\Html;

/** @var int $year */
/** @var array $data */

$this->title = "Топ авторов для {$year}";
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="card shadow-sm mb-4">
    <div class="card-body">

        <h2 class="card-title mb-4">
            <?= Html::encode($this->title) ?>
        </h2>

        <form method="get" class="row g-3 align-items-end mb-4">
            <div class="col-auto">
                <label for="year" class="form-label">Год</label>
                <input
                    type="number"
                    class="form-control"
                    id="year"
                    name="year"
                    value="<?= Html::encode($year) ?>"
                    min="1900"
                    max="<?= date('Y') ?>"
                >
            </div>

            <div class="col-auto">
                <button type="submit" class="btn btn-primary">
                    Показать отчет
                </button>
            </div>
        </form>

        <?php if (empty($data)): ?>
            <div class="alert alert-info mb-0">
                Нет данных для выбранного года.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>Автор</th>
                            <th style="width: 180px;">Книг опубликовано</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $i => $row): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= Html::encode($row['full_name']) ?></td>
                                <td>
                                    <span class="badge bg-primary">
                                        <?= Html::encode($row['books_count']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>
</div>
