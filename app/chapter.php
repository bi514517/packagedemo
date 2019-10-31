<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class chapter extends Model
{
    public static function getLastChapterByBook($bookId)
    {
        $count = DB::table("chapter")->where("bookId", $bookId)->count();
        if ($count > 0)
            return DB::table("chapter")->where("bookId", $bookId)->orderBy("stt", "desc")->first()->stt;
        return 0;
    }
}
