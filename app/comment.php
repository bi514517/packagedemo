<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class comment extends Model
{
    public static function getCommentByBook($bookId, $page)
    {
        return DB::table('comments')
            ->where('comments.bookId', $bookId)
            ->get();
    }
    
}
