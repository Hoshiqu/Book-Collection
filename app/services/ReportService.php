<?php

namespace app\services;

use app\models\Author;
use yii\db\Query;

class ReportService
{
    public function topAuthorsByYear(int $year): array
    {
        return (new Query())
            ->select([
                'author_id' => 'a.id',
                'full_name' => 'a.full_name',
                'books_count' => 'COUNT(b.id)',
            ])
            ->from(['a' => Author::tableName()])
            ->innerJoin('book_author ba', 'ba.author_id = a.id')
            ->innerJoin('book b', 'b.id = ba.book_id')
            ->where(['b.published_year' => $year])
            ->groupBy(['a.id', 'a.full_name'])
            ->orderBy(['books_count' => SORT_DESC])
            ->limit(10)
            ->all();
    }
}
