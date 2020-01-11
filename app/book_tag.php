<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\utils\utilsFunction;

class book_tag extends Model
{

    public static function insertBook_Tag($bookName, $tags)
    {
        $query = "insert INTO book_tag (book_tag.bookId, book_tag.tagId)
    VALUES  (?,(SELECT tag.id FROM tag WHERE tag.name = ? LIMIT 1)) ON DUPLICATE KEY UPDATE book_tag.bookId = VALUES(book_tag.bookId)";
        foreach ($tags as $tagName) {
            DB::insert(
                $query,
                [utilsFunction::createSlugId($bookName), $tagName]
            );
        }
    }
}
