<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\chapter;
use App\category;


class book extends Model
{
    public static function getJustUpdatedBook($limit = 30)
    {
        $books = DB::table("book")
            ->orderBy("book.lastestUpdate", "desc")
            ->limit($limit)
            ->get();
        $data = [];
        foreach ($books as $book) {
            $book->lastchapter = chapter::getLastChapterByBook($book->id);
            $book->categories = category::getCategoryByBookId($book->id);
            array_push($data, $book);
        }
        return $data;
    }

    public static function getNewestBooks($limit = 8)
    {
        $data = DB::table('book')
            ->select(
                "book.id",
                "book.name",
                "book.avatar"
            )
            ->orderBy('book.datePublication', 'desc')
            ->limit($limit)
            ->get();
        return $data;
    }

    public static function getTopViewBooks($limit = 5)
    {
        $data = DB::table('book')
            ->select(
                "book.id",
                "book.name",
                "book.avatar",
                "book.authorId",
                "author.name as authorName",
                "book.description"
            )
            ->leftJoin('author', 'author.id', '=', 'book.authorId')
            ->orderBy('book.view', 'desc')
            ->limit($limit)
            ->get();
        return $data;
    }

    public static function getRecommendBooks($limit = 10)
    {
        $data = DB::table('book')
            ->select(
                "book.id as bookId",
                "avatar",
                "book.name as bookName",
                "description",
                "author.name as authorName",
                "view",
                "datePublication",
                "lastestUpdate",
                "submitUserId",
                "status",
                DB::raw(' IFNULL(ROUND(SUM(user_book.vote)/Count(user_book.vote),1),0) as vote ')
            )
            ->leftJoin('user_book', 'user_book.bookId', '=', 'book.id')
            ->leftJoin('author', 'author.id', '=', 'book.authorId')
            ->groupBy("book.id")
            ->orderBy('vote', 'desc')
            ->limit($limit)
            ->get();
        return $data;
    }
}
