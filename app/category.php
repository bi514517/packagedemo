<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class category extends Model
{
    public static function getALl()
    {
        $data = DB::table('category')->select('category.id', 'category.name', 'category.accept')->get();
        return $data;
    }
    public static function getAccepted()
    {
        $data = DB::table('category')->select('category.id', 'category.name', 'category.accept')->where('category.accept', 1)->get();
        return $data;
    }
    public static function getCategoryByBookId($bookId)
    {
        $data = DB::table('book')
            ->select('category.id', 'category.name', 'category.accept')
            ->leftJoin('book_category', 'book.id', '=', 'book_category.bookId')
            ->leftJoin('category', 'category.id', '=', 'book_category.categoryId')
            ->where('book.id', $bookId)->get();
        return $data;
    }
}
