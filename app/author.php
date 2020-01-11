<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use App\utils\utilsFunction;

class author extends Model
{
    public static function insertAuthor($author)
    {
        DB::insert(
            'insert INTO author (author.id,author.name) values (?, ?) ON DUPLICATE KEY UPDATE author.name = VALUES(author.name) ',
            [utilsFunction::createSlugId($author), $author]
        );
    }
}
