<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\chapter;
use App\category;
use App\utils\utilsFunction;

class book extends Model
{
    public static function insertBook($bookName, $bookAvatar, $bookDesciption, $authorName, $bookDatePublication, $bookStatus)
    {
        $query = "insert INTO book(book.id, book.avatar, book.name, book.description, book.authorId , book.datePublication , book.lastestUpdate , book.status)
                VALUES (?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE book.status = VALUES(book.status)";
        DB::insert(
            $query,
            [utilsFunction::createSlugId($bookName), $bookAvatar, $bookName, $bookDesciption, utilsFunction::createSlugId($authorName), $bookDatePublication, $bookDatePublication, $bookStatus]
        );
    }

    public static function getJustUpdatedBook($limit = 30)
    {
        $books = DB::table("book")
            ->select(
                "book.id as bookId",
                "book.name as bookName",
                "book.avatar",
                "book.lastestUpdate"
            )
            ->orderBy("book.lastestUpdate", "desc")
            ->limit($limit)
            ->get();
        $data = [];
        foreach ($books as $book) {
            $book->lastchapter = chapter::getLastChapterByBook($book->bookId);
            $book->categories = category::getCategoryByBookId($book->bookId);
            array_push($data, $book);
        }
        return $data;
    }

    public static function getNewestBooks($limit = 8)
    {
        $data = DB::table('book')
            ->select(
                "book.id as bookId",
                "book.name as bookName",
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
                "book.id as bookId",
                "book.name as bookName",
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
                DB::raw(' IFNULL(ROUND(SUM(book_user.vote)/Count(book_user.vote),1),0) as vote ')
            )
            ->leftJoin('book_user', 'book_user.bookId', '=', 'book.id')
            ->leftJoin('author', 'author.id', '=', 'book.authorId')
            ->groupBy("book.id")
            ->orderBy('vote', 'desc')
            ->limit($limit)
            ->get();
        return $data;
    }
    public static function getBookById($bookId, $chapterPage = 1, $amountOfChapterPage = 99999)
    {
        $data = DB::table('book')
            ->select(
                "book.id as bookId",
                "book.avatar as bookAvatar",
                "user.avatar as userAvatar",
                "book.name as bookName",
                "description",
                "author.name as authorName",
                "view",
                "datePublication",
                "lastestUpdate",
                "submitUserId",
                "user.name as submitUserName",
                "status",
                DB::raw(' IFNULL(ROUND(SUM(book_user.vote)/Count(book_user.vote),1),0) as vote ')
            )
            ->leftJoin('book_user', 'book_user.bookId', '=', 'book.id')
            ->leftJoin('author', 'author.id', '=', 'book.authorId')
            ->leftJoin('user', 'user.id', 'book.submitUserId')
            ->where("book.id", $bookId)
            ->first();
        $data->chapters = chapter::getChaptersByBook($bookId, $amountOfChapterPage, $chapterPage);
        $data->categories = category::getCategoryByBookId($bookId);
        $data->tags = tag::getTagsByBookId($bookId);
        $data->page = $chapterPage;
        $count = chapter::countChaptersByBook($bookId);
        $data->pagination = utilsFunction::pagination($chapterPage, $amountOfChapterPage, $count);
        return $data;
    }
    public static function delBook($bookId)
    {
        DB::table("book_category")->where("bookId", $bookId)->delete();
        DB::table("book_tag")->where("bookId", $bookId)->delete();
        DB::table("book_user")->where("bookId", $bookId)->delete();
        DB::table("book")->where("book.Id", $bookId)->delete();
        echo "đã xóa truyện " . $bookId;
    }

    public static function delBookIfNoChapt($bookId)
    {
        if (count(chapter::getChaptersByBook($bookId)) <= 0) {
            book::delBook($bookId);
        }
    }

    public static function updateLastChapt($bookId, $chaptCount, $lastChaptname, $lastChapStt)
    {
        DB::table("book")->where("book.Id", $bookId)->update([
            "chaptCount" => $chaptCount,
            "lastChaptname" => $lastChaptname,
            "lastChapStt" => $lastChapStt
        ]);
    }
}
