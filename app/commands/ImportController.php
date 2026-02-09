<?php

namespace app\commands;

use yii\console\Controller;
use app\models\Author;
use app\models\Book;
use app\models\Genre;
use Yii;

class ImportController extends Controller
{
    public function actionBooks(): void
    {
        $data = $this->getData();

        foreach ($data as $item) {
            $book = Book::findOne(['title' => $item['title']]);

            if (!$book) {
                $book = new Book([
                    'title' => $item['title'],
                    'published_year' => $item['year'],
                    'isbn' => $item['isbn'],
                ]);
                $book->save(false);
            }

            foreach ($item['authors'] as $authorName) {
                $author = Author::findOne(['full_name' => $authorName])
                    ?? new Author(['full_name' => $authorName]);

                $author->save(false);

                Yii::$app->db->createCommand()->insert(
                    'book_author',
                    ['book_id' => $book->id, 'author_id' => $author->id]
                )->execute();
            }

            foreach ($item['genres'] as $genreName) {
                $genre = Genre::findOne(['name' => $genreName])
                    ?? new Genre(['name' => $genreName]);

                $genre->save(false);

                Yii::$app->db->createCommand()->insert(
                    'book_genre',
                    ['book_id' => $book->id, 'genre_id' => $genre->id]
                )->execute();
            }
        }

        echo "Import completed\n";
    }

    private function getData(): array
    {
        return [
            [
                'title' => 'Путеводитель по базам данных',
                'year' => 2025,
                'isbn' => 'DB-001',
                'authors' => ['Комаров Владимир'],
                'genres' => ['Database Architecture', 'Database Fundamentals'],
            ],
            [
                'title' => 'Введение в системы баз данных',
                'year' => 2025,
                'isbn' => 'DB-002',
                'authors' => ['Дейт К. Дж.'],
                'genres' => ['Database Fundamentals', 'Database Architecture'],
            ],
            [
                'title' => 'MySQL по максимуму',
                'year' => 2025,
                'isbn' => 'DB-003',
                'authors' => ['Ботрос Сильвия', 'Тинли Джереми'],
                'genres' => ['MySQL', 'Performance & Optimization', 'SQL'],
            ],
            [
                'title' => 'SQL Server. Наладка и оптимизация для профессионалов',
                'year' => 2025,
                'isbn' => 'DB-004',
                'authors' => ['Короткевич Дмитрий'],
                'genres' => ['SQL Server', 'Performance & Optimization'],
            ],
            [
                'title' => 'Нечеткое сопоставление данных в SQL',
                'year' => 2025,
                'isbn' => 'DB-005',
                'authors' => ['Лемер Джим'],
                'genres' => ['SQL', 'Data Engineering'],
            ],
            [
                'title' => 'Антипаттерны SQL',
                'year' => 2025,
                'isbn' => 'DB-006',
                'authors' => ['Карвин Билл'],
                'genres' => ['SQL', 'Database Design'],
            ],
            [
                'title' => 'PostgreSQL 16 изнутри',
                'year' => 2025,
                'isbn' => 'DB-007',
                'authors' => ['Рогов Егор Валерьевич'],
                'genres' => ['PostgreSQL', 'Database Architecture'],
            ],
            [
                'title' => 'Kafka Streams и ksqlDB: данные в реальном времени',
                'year' => 2025,
                'isbn' => 'DB-008',
                'authors' => ['Митч Сеймур'],
                'genres' => ['Data Engineering', 'Streaming & Real-time'],
            ],
        ];
    }
}
