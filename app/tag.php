<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class tag extends Model
{
    public static function insertTags($tags)
    {
        foreach ($tags as $tag) {
            $query = "insert into tag (tag.name) VALUES
        (?)
        ON DUPLICATE KEY UPDATE tag.name = VALUES(tag.name)";
            DB::insert($query, [$tag]);
        }
    }
    public static function getALl()
    {
        $data = DB::table('tag')->select('tag.id', 'tag.name')->get();
        return $data;
    }
    public static function getTagsByBookId($bookId)
    {
        $data = DB::table('book')
            ->select('tag.id', 'tag.name')
            ->leftJoin('book_tag', 'book.id', '=', 'book_tag.bookId')
            ->leftJoin('tag', 'tag.id', '=', 'book_tag.tagId')
            ->where('book.id', $bookId)->get();
        return $data;
    }
}
