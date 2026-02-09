# Каталог книг (Yii2 + MySQL)

Веб-приложение на фреймворке **Yii2** для управления каталогом книг и авторов с поддержкой подписок и SMS-уведомлений.  
Проект реализован в рамках тестового задания.

---

## Описание задачи

Необходимо реализовать каталог книг со следующими требованиями:

- Книга может иметь **несколько авторов**
- Реализовать **CRUD** для книг и авторов
- Разграничить права доступа для гостей и авторизованных пользователей
- Реализовать отчёт по авторам
- Дополнительно: SMS-уведомления о новых книгах по подписке

Приложение реализовано как **web-приложение**, не API.

---

## Основной функционал

### Книги

Для книги реализованы следующие поля:

- Название
- Год выпуска
- Описание
- ISBN (уникальный)
- Фото главной страницы (обложка)

Особенности:
- Книга может иметь одного или нескольких авторов (связь M:N)
- Обложка хранится в файловой системе
- Поддерживается загрузка файла, вставка из буфера обмена и удаление обложки

---

### Авторы

Для автора реализовано:

- ФИО
- Связь с книгами (M:N)

Автор книги **не является пользователем системы** — это отдельная сущность.

---

## Права доступа

### Гость (неаутентифицированный пользователь)

- Просмотр каталога книг
- Просмотр страниц авторов
- Подписка на автора с указанием номера телефона
- Получение SMS-уведомлений о новых книгах автора

### Авторизованный пользователь

- Просмотр каталога
- Создание книг
- Редактирование и удаление книг
- Редактирование и удаление авторов
- Управление собственными книгами

Отдельной роли администратора не предусмотрено и не требуется по заданию.

---

## Подписки и SMS-уведомления

- Подписка осуществляется **на конкретного автора**
- Подписка доступна гостям без регистрации
- При публикации новой книги автором отправляется SMS всем подписчикам автора
- Для отправки SMS используется сервис **SMSPilot**

Для тестирования используется **эмуляторный ключ**, реальная отправка SMS не производится:  
https://smspilot.ru/

Отписка от подписки не реализуется, так как не требуется заданием.

---

## Отчёты

Реализован отчёт:

- **ТОП-10 авторов**, выпустивших наибольшее количество книг за выбранный год

Характеристики отчёта:
- Отдельная страница
- Доступен всем пользователям
- Реализован как web-страница (не PDF)

---

## Технические детали

- PHP: **8.0+**
- СУБД: **MySQL / MariaDB**
- Фреймворк: **Yii2**
- Использован шаблон Yii2 (basic)
- Хранение обложек: локальная файловая система
- Для базы данных используются **миграции**
- Дамп БД не требуется
- Директории `vendor` и `runtime` не включаются в репозиторий

RBAC реализован стандартными средствами Yii2, выбор конкретного менеджера не принципиален.

---

## Структура данных

- `book` — книги
- `author` — авторы
- `book_author` — связь книг и авторов (M:N)
- `genre`, `book_genre` — жанры книг
- `author_subscription` — подписки на авторов
- `user` — пользователи системы
- `user_library` — личная библиотека пользователя

---

# Запуск проекта

## Запуск через Docker (Docker Compose)

### 1) Требования
- Docker
- Docker Compose (плагин `docker compose`)

Проверка:

```
docker --version
docker compose version
```

---

### 2) Создание структуры проекта на хосте

```
mkdir yii2-books
cd yii2-books

mkdir docker
mkdir docker/php
mkdir docker/nginx
```

---

### 3) Создать `docker-compose.yml`

Пример (адаптируйте под свои имена контейнеров и порты, если отличаются):

```
version: "3.9"

services:
  app:
    image: php:8.3-fpm
    container_name: yii2-app
    working_dir: /var/www/html
    volumes:
      - ./:/var/www/html
    depends_on:
      - db

  nginx:
    image: nginx:1.25
    container_name: yii2-nginx
    ports:
      - "8080:80"
    volumes:
      - ./:/var/www/html
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - app

  db:
    image: mysql:8.0
    container_name: yii2-db
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: yii2_books
      MYSQL_USER: yii2
      MYSQL_PASSWORD: yii2
    ports:
      - "3306:3306"
    volumes:
      - dbdata:/var/lib/mysql

volumes:
  dbdata:
```

---

### 4) Создать `docker/nginx/default.conf`

Пример:

```
server {
    listen 80;
    server_name localhost;

    root /var/www/html/app/web;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass app:9000;
    }

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 7d;
        access_log off;
    }
}
```

---

### 5) Поднять контейнеры

```
docker compose up -d
docker ps
```

Открыть в браузере:

- http://localhost:8080

---

### 6) Зайти в контейнер PHP

```
docker exec -it yii2-app bash
```

Если всё ок — увидите примерно:

- `root@xxxxxxxx:/var/www/html#`

---

### 7) Установить Composer в контейнере

Проверка PHP:

```
php -v
```

Установка Composer:

```
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
php -r "unlink('composer-setup.php');"
composer -V
```

---

### 8) Создать Yii2 приложение внутри `/var/www/html/app`

```
mkdir app
cd app
pwd
```

Должно быть:

- `/var/www/html/app`

Пакеты для zip и удобства:

```
apt-get update && apt-get install -y git unzip zip libzip-dev
docker-php-ext-install zip
php -m | grep zip
git --version
```

Создать Yii2 basic:

```
composer create-project --prefer-dist yiisoft/yii2-app-basic .
```

---

### 9) Настроить расширение `pdo_mysql` и подключение к БД

Установить `pdo_mysql`:

```
docker-php-ext-install pdo_mysql
php -m | grep -E "pdo|mysql"
```

Проверить Yii:

```
cd /var/www/html/app
php yii
```

Настроить `app/config/db.php` (под docker mysql):

- host: `db`
- dbname: `yii2_books`
- user: `yii2`
- password: `yii2`

При необходимости добавить:

```
'attributes' => [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
],
```
---
### 10) Замена прикладного кода приложения

После создания базового Yii2-проекта необходимо заменить каталог `app/`
на версию с реализацией тестового задания.

Скопируйте каталог `app/` из репозитория с тестовым заданием в корень проекта.

В каталоге `app/` находятся все изменённые файлы проекта:
контроллеры, модели, представления, миграции и конфигурация.
Каталоги `vendor` и `runtime` в репозиторий не включаются.
---

### 11) Применить миграции

```
php yii migrate
```
---

### 12) Получение демонстрационного набора книг 

Выполнив эту команду вы создадите в базе 9 таблиц которые могут продемонстрировать работу приложения.
```
php yii import/books

```

---

## Контакты

В случае возникновения вопросов или проблем с запуском проекта можно обратиться:  
https://t.me/groft
