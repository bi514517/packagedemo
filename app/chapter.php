<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class chapter extends Model
{
    public static function insertChapter($bookId, $chapterStt, $chapterName)
    {
        DB::table('chapter')->insert(
            [
                'chapter.bookId' => $bookId,
                'chapter.stt' => $chapterStt,
                'chapter.name' => $chapterName,
                'chapter.timeUpload' => now(),
            ]
        );
    }
    public static function getLastChapterByBook($bookId)
    {
        $count = DB::table("chapter")->where("bookId", $bookId)->count();
        if ($count > 0)
            return DB::table("chapter")->where("bookId", $bookId)->orderBy("stt", "desc")->first()->stt;
        return 0;
    }
    public static function getChaptersByBook($bookId, $amountOfPage = 99999999, $page = 1)
    {
        $skip = $amountOfPage * ($page - 1);
        $limit = $amountOfPage;
        $count = DB::table("chapter")->where("bookId", $bookId)->count();
        if ($count > 0)
            return DB::table("chapter")
                ->select("stt", "name", "timeUpload")
                ->where("bookId", $bookId)
                ->skip($skip)
                ->limit($limit)
                ->get();
        return [];
    }
    public static function countChaptersByBook($bookId)
    {
        $count = DB::table("chapter")->where("bookId", $bookId)->count();
        return $count;
    }
}
