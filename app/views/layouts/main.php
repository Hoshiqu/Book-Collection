<?php

use yii\helpers\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\bootstrap5\Breadcrumbs;
use app\assets\AppAsset;

AppAsset::register($this);
$this->beginPage();
?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Html::encode($this->title) ?></title>
    <?= Html::csrfMetaTags() ?>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <?php $this->head() ?>
    <style>
        #toast-container { position: fixed; top: 80px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 8px; pointer-events: none; }
        .toast-msg { padding: 12px 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,.15); font-size: 14px; pointer-events: auto; animation: toast-in .25s ease; }
        .toast-msg.success { background: #d1e7dd; color: #0f5132; }
        .toast-msg.error { background: #f8d7da; color: #842029; }
        .toast-msg.hide { animation: toast-out .25s ease forwards; }
        @keyframes toast-in { from { opacity: 0; transform: translateX(100%); } to { opacity: 1; transform: translateX(0); } }
        @keyframes toast-out { from { opacity: 1; transform: translateX(0); } to { opacity: 0; transform: translateX(100%); } }
    </style>
</head>
<body>
<?php $this->beginBody() ?>
<div id="toast-container"></div>
<script>
window.showNotification = function(message, type) {
    type = type || 'success';
    var container = document.getElementById('toast-container');
    var el = document.createElement('div');
    el.className = 'toast-msg ' + type;
    el.textContent = message;
    container.appendChild(el);
    setTimeout(function() {
        el.classList.add('hide');
        setTimeout(function() { el.remove(); }, 280);
    }, 3000);
};
</script>

<header>
<?php
NavBar::begin([
    'brandLabel' => 'Книжная полка',
    'brandUrl' => Yii::$app->homeUrl,
    'options' => [
        'class' => 'navbar navbar-expand-lg navbar-dark bg-dark',
    ],
]);

$menuItems = [
    ['label' => 'Aвторы', 'url' => ['/author/index']],
    ['label' => 'Книги', 'url' => ['/book/index']],
    ['label' => 'Отчет по авторам', 'url' => ['/report/top-authors']],
];

// === AUTH BLOCK ===
if (Yii::$app->user->isGuest) {
    $menuItems[] = ['label' => 'Зарегистрироваться', 'url' => ['/site/register']];
    $menuItems[] = ['label' => 'Войти', 'url' => ['/site/login']];
} else {
    // только для авторизованных
    $menuItems[] = ['label' => 'Библиотека', 'url' => ['/library/index']];
    $menuItems[] = ['label' => 'Добавить книгу', 'url' => ['/book/create']];

    // logout в виде формы POST (важно для безопасности)
    $menuItems[] = '<li class="nav-item d-flex align-items-center ms-2">'
        . Html::beginForm(['/site/logout'], 'post', ['class' => 'd-inline'])
        . Html::submitButton(
            'Выйти (' . Html::encode(Yii::$app->user->identity->username) . ')',
            [
                'class' => 'nav-link btn btn-link text-white text-decoration-none p-0',
                'style' => 'cursor:pointer;',
            ]
        )
        . Html::endForm()
        . '</li>';
}

echo Nav::widget([
    'options' => ['class' => 'navbar-nav ms-auto'],
    'items' => $menuItems,
    'encodeLabels' => false, // важно для HTML logout
]);

NavBar::end();
?>
</header>

<main class="container" style="min-height:1121px">
    <?= Breadcrumbs::widget([
        'links' => $this->params['breadcrumbs'] ?? [],
    ]) ?>

    <?php
    foreach (['success', 'error', 'warning'] as $type):
        if (Yii::$app->session->hasFlash($type)):
            $flashMsg = Yii::$app->session->getFlash($type);
            $toastType = ($type === 'error') ? 'error' : 'success';
    ?>
    <script>(function(){document.addEventListener('DOMContentLoaded',function(){if(typeof showNotification==='function')showNotification(<?= json_encode($flashMsg) ?>,<?= json_encode($toastType) ?>);});})();</script>
    <?php
        endif;
    endforeach;
    ?>

    <?= $content ?>
</main>

<footer class="footer mt-5 py-3 bg-light">
    <div class="container text-center text-muted">
        Yii2 · @groft · 2026
    </div>
</footer>

<?php $this->endBody() ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $this->endPage() ?>