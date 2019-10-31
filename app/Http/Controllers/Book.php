<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class Book extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request = json_decode(str_replace(chr(92),"",$request->getContent()));
        define ("SORTBYDATE","0");
        define ("SORTBYVIEW","1");
        define ("SORTBYLIKE","2");
        $select = "select book.id as bookId, book.avatar as bookAvatar, book.name as bookName , author.name as authorName ";
        $join = "LEFT JOIN author on author.id = book.authorId ";
        $where = "Where ";
        $sort = "";
        $limit = "LIMIT ".($request->page-1)*$request->numberOfPage.",".$request->numberOfPage;
        switch ($request->sortBy) {
          case SORTBYDATE:
              $sort =  "ORDER BY book.datePublication ". $request->sortType;
              break;
          case SORTBYVIEW:
              $sort =  "ORDER BY book.view ". $request->sortType;
              break;
          case SORTBYLIKE:
              $select = "select book.id as bookId, book.avatar as bookAvatar, book.name as bookName
              , author.name authorName , COUNT(user_book.reactionId) as likes";
              $join = $join. "LEFT JOIN user_book on user_book.bookId = book.id ";
              $sort =  "ORDER BY likes ". $request->sortType;
              break;
          default:
            // code...
            break;
        }
        if(isset( $request->category )) {
            $join = $join. "LEFT JOIN book_category on book_category.bookId = book.id ";
            $where = $where. " book_category.categoryId = '".$request->category."' ";
            if(isset( $request->searchKeywords )) {
                $join = $join. "LEFT JOIN book_tag on book_tag.bookId = book.id
              LEFT JOIN tag on tag.id = book_tag.tagId ";
                $where = $where. "and MATCH (book.name) AGAINST ('".$request->searchKeywords."' IN NATURAL LANGUAGE MODE) OR
              MATCH (tag.name) AGAINST ('".$request->searchKeywords."' IN NATURAL LANGUAGE MODE) OR
              MATCH (author.name) AGAINST ('".$request->searchKeywords."' IN NATURAL LANGUAGE MODE) ";
            }
        }
        else{
          if(isset( $request->searchKeywords )) {
              $join = $join. "LEFT JOIN book_tag on book_tag.bookId = book.id
            LEFT JOIN tag on tag.id = book_tag.tagId ";
              $where = $where. "MATCH (book.name) AGAINST ('".$request->searchKeywords."' IN NATURAL LANGUAGE MODE) OR
            MATCH (tag.name) AGAINST ('".$request->searchKeywords."' IN NATURAL LANGUAGE MODE) OR
            MATCH (author.name) AGAINST ('".$request->searchKeywords."' IN NATURAL LANGUAGE MODE) ";
          }
          else {
              $where ="";
          }
        }
        $query =  $select ."
        FROM book
        ". $join ."
        ". $where ."
         GROUP BY book.id
        ". $sort ."
        ". $limit;
        // add chapter
        $query ="
        SELECT x.bookId, x.bookAvatar, x.bookName , x.authorName,
          chapter.stt as chapterStt , chapter.name as chapterName
        FROM ( " . $query . " ) as x left JOIN chapter ON chapter.bookId = x.bookId
        WHERE chapter.name is not null";
        // get last
        $query = "
        SELECT m1.*
          FROM ( " . $query . " ) m1 LEFT JOIN ( " . $query . " ) m2
           ON (m1.bookId = m2.bookId AND m1.chapterStt < m2.chapterStt)
          WHERE m2.chapterStt IS NULL;
                 ";

        $data = DB::select( DB::raw($query));
        return response()->json([
                 'status' => 'true',
                 'data' => $data ,
                 'messenge' => 'success'
             ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function detail($bookId)
    {
        $tags = self::getTags($bookId);
        $categories = self::getCategories($bookId);
        $detail = self::getBookDetail($bookId);
        $comment = self::getComment($bookId);
        if( count($detail) > 0 ){
          return response()->json([
                   'status' => 'true',
                   'data' => [
                     'bookDetail' => $detail[0],
                     'categories' => $categories,
                     'tags' => $tags,
                     'comments' => $comment
                     ] ,
                   'messenge' => 'success'
               ]);
        }
        else {
          return response()->json([
                   'status' => 'false',
                   'data' => [] ,
                   'messenge' => 'fail'
               ]);
        }

    }
    public function detailWithUserId($bookId,$userId)
    {
        $tags = self::getTags($bookId);
        $categories = self::getCategories($bookId);
        $detail = self::getBookDetail($bookId);
        $comment = self::getComment($bookId);
        $bookWithCurrentUser = self::getBookWithCurrentUser($bookId,$userId);
        if( count($detail) > 0 ){
          return response()->json([
                   'status' => 'true',
                   'data' => [
                     'bookDetail' => $detail[0],
                     'categories' => $categories,
                     'tags' => $tags,
                     'comments' => $comment,
                     'currentUser'=> $bookWithCurrentUser
                     ] ,
                   'messenge' => 'success'
               ]);
        }
        else {
          return response()->json([
                   'status' => 'false',
                   'data' => [] ,
                   'messenge' => 'fail'
               ]);
        }

    }


    public function getTags($bookId){
        $tags = DB::table('book_tag')->select('tag.name','tag.id')
        ->leftJoin('tag' , 'tag.id' , '=' , 'book_tag.tagId')
        ->where('book_tag.bookId', $bookId)->get();
        return $tags;
    }

    public function getCategories($bookId){
        $categories = DB::table('book_category')
        ->select('category.name','category.id')
        ->leftJoin('category' , 'category.id' , '=' , 'book_category.categoryId')
        ->where('book_category.bookId', $bookId)->get();
        return $categories;
    }
    public function getComment($bookId){
        $comment = DB::table('comment')
        ->select( DB::raw('COUNT(comment.id) as countComment'))
        ->leftJoin('user' , 'user.id' , '=' , 'comment.userId')
        ->where('comment.bookId', $bookId)->get();
        if(count($comment) > 0){
          return $comment[0]->countComment;
        }
        return 0;
    }
    public function getBookWithCurrentUser($bookId,$userId){
        $detail = DB::table('user_book')
        ->select("user_book.chapt", "reaction.name as reactionName",
        "reaction.icon as reactionIcon")
        ->leftJoin('reaction', 'reaction.id', '=', 'user_book.reactionId')
        ->where('user_book.bookId', $bookId)
        ->where('user_book.userId', $userId)
        ->get();
        if(count($detail) > 0){
          return $detail[0];
        }
        return "";
    }
    public function getBookDetail($bookId){
        $detail = DB::table('book')->select("book.id as bookId",
        "book.avatar as bookAvatar", "book.name as bookName",
        "author.id as authorId" ,"author.name as authorName",
        "book.view as bookView" , "book.description as bookDescription" ,
        "book.datePublication as bookDatePublication","book.status as bookStatus",
        DB::raw('max(chapter.stt) as chapterStt'),
        "user.name as submitUserName", "book.submitUserId" ,
        "user.email as submitUserEmail" , "user.avatar as submitUserAvatar" ,
         DB::raw('COUNT(user_book.reactionId) as likes'))
        ->leftJoin('chapter','chapter.bookId','=','book.id')
        ->leftJoin('author', 'author.id', '=', 'book.authorId')
        ->leftJoin('user_book', 'user_book.bookId', '=', 'book.id')
        ->leftJoin('user', 'user.id', '=', 'book.submitUserId')
        ->groupBy('book.id')
        ->where('book.id', $bookId)
        ->get();
        if( count($detail) > 0 ){
          $detail[0]->likes = $detail[0]->likes/$detail[0]->chapterStt;
        }
        return $detail;
    }

    function getReadingBookList($userId){
      $data = DB::table('book')->select('book.id as bookId',
      'book.avatar as bookAvatar' , 'book.name as bookName' ,
      'author.name as authorName','chapter.stt as chapterStt','chapter.name as chapterName')
      ->leftJoin('user_book', 'user_book.bookId', '=', 'book.id')
      ->leftJoin('author', 'author.id', '=', 'book.authorId')
      ->leftjoin('chapter','chapter.stt','=','user_book.chapt')
      ->where('user_book.userId', $userId)
      ->groupBy('book.id')
      ->orderBy('user_book.createDate', 'desc')
      ->get();
      return response()->json([
               'status' => 'true',
               'data' => $data ,
               'messenge' => 'success'
           ]);
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function giveReaction(Request $request)
    {
       $request = json_decode(str_replace(chr(92),"",$request->getContent()));
       $bookId = $request->bookId;
       $userId = $request->userId;
       $reaction = isset($request->reaction) ? $request->reaction : 0;
       $isExist = DB::table('user_book')
               ->where('user_book.userId', $userId)
               ->where('user_book.bookId', $bookId)
               ->exists();
       if($isExist){
         if($reaction == 0){
           DB::table('user_book')
            ->where('user_book.userId', $userId)
            ->where('user_book.bookId', $bookId)
            ->delete();
            return response()->json([
                     'status' => 'true',
                     'data' => null ,
                     'messenge' => 'success'
                 ]);
         }
         else {
           DB::table('user_book')
            ->where('user_book.userId', $userId)
            ->where('user_book.bookId', $bookId)
            ->update(['user_book.reactionId' => $reaction]);
         }
       }
       else {
         DB::table('user_book') -> insert(
            ['user_book.userId' => $userId,
             'user_book.bookId' => $bookId,
             'user_book.reactionId' => $reaction]
         );
       }
       $like = count(DB::table('user_book')->select('user_book.reactionId')
                   ->where('user_book.bookId', $bookId)
                   ->get());
       return response()->json([
                'status' => 'true',
                'data' => $like,
                'messenge' => 'success'
            ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateCurrentChap(Request $request)
    {
        $request = json_decode(str_replace(chr(92),"",$request->getContent()));
        $check = DB::table('user_book')->select('user_book.bookId','user_book.userId')
        ->where('user_book.bookId', $request->bookId)
        ->where('user_book.userId', $request->userId)
        ->exists();
        if($check){
            DB::table('user_book')->select('user_book.bookId','user_book.userId')
            ->where('user_book.bookId', $request->bookId)
            ->where('user_book.userId', $request->userId)
            ->update(['user_book.chapt' => $request->chapt,
                      'user_book.createDate' => now()
                    ]);
            return response()->json([
                     'status' => true,
                     'data' => $request->chapt ,
                     'messenge' => 'update success'
                 ]);

        }else{
            DB::table('user_book')->insert(
                ['user_book.bookId' => $request->bookId,
                 'user_book.userId' => $request->userId,
                 'user_book.chapt' => $request->chapt,
                 'user_book.createDate' => now(),
               ]
            );
            return response()->json([
                     'status' => 'true',
                     'data' => $request->chapt ,
                     'messenge' => 'insert success'
                 ]);

        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
