<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use App\utils\utilsFunction;

class chapter extends Model
{
    public static function insertChapter($bookId, $chapterStt, $chapterName)
    {
        try {
            $updateTime = now();
            DB::table('chapter')->insert(
                [
                    'chapter.bookId' => $bookId,
                    'chapter.stt' => $chapterStt,
                    'chapter.name' => $chapterName,
                    'chapter.timeUpload' => $updateTime,
                ]
            );
            // update info book
            DB::table('book')
                ->where('book.id', $bookId)
                ->update(['book.lastestUpdate' => $updateTime]);
            return true;
        } catch (QueryException $ex) {
            return false;
        }
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

    public static function checkChapterExist($stt, $bookId)
    {
        return DB::table('chapter')
            ->where('chapter.stt', $stt)
            ->where('chapter.bookId', $bookId)
            ->exists();
    }
    
    public static function saveChapter($bookId, $chapterStt, $content)
    {
        return utilsFunction::saveChapter($bookId, $chapterStt, $content);
    }

    public static function getChapter($bookId, $stt)
    {
        $info = DB::table('book')
        ->select(
            "book.id as bookId",
            "book.name as bookName",
            "book.avatar",
            "chapter.name as chapterName",
            "chapter.stt"
        )
        ->leftJoin('chapter', 'chapter.bookId', '=', 'book.id')
        ->where('chapter.stt', $stt)
        ->where('chapter.bookId', $bookId)
        ->first();
        $result = [];
        $result["data"] = utilsFunction::getChapterContent($bookId, $stt);
        $result["info"] = $info;
        return json_decode(json_encode($result));
    }
}
