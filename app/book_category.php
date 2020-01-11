<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use App\utils\utilsFunction;

class book_category extends Model
{
    public static function insertBook_Category($bookName, $categories)
    {
        $query = "insert into `book_category`(`bookId`, `categoryId`)
            values (?,?) ON DUPLICATE KEY UPDATE book_category.bookId = VALUES(book_category.bookId)";
        foreach ($categories as $categoryName) {
            DB::insert(
                $query,
                [utilsFunction::createSlugId($bookName), utilsFunction::createSlugId($categoryName)]
            );
        }
    }
}
