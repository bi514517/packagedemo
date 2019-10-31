<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Comment extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createComment(Request $request)
    {
        $request = json_decode(str_replace(chr(92),"",$request->getContent()));
        DB::table('comment')->insert(
            [
             'comment.userId' => $request->userId,
             'comment.bookId' => $request->bookId,
             'comment.content' => $request->content,
             'comment.timeUpload' => now()
           ]
        );
        return response()->json([
                 'status' => 'true',
                 'data' => self::getCommentByBookId1($request->bookId) ,
                 'messenge' => 'insert success'
             ]);
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
    public function getComment($page,$numberOfPage)
    {
      $skip = $page -1 * $numberOfPage;
      $take = $numberOfPage;
      $comment = DB::table('comment')->select("comment.id as commentId", "comment.content as commentContent",
       "comment.timeUpload as commentTimeUpload" , "user.id as userId" , "user.name as userName" ,
       "user.avatar as userAvatar" , "book.id as bookId", "book.name as bookName","book.avatar as bookAvatar")
      ->leftJoin('user' , 'user.id' , '=' , 'comment.userId')
      ->leftJoin('book' , 'book.id' , '=' , 'comment.bookId')
      ->orderBy('comment.timeUpload','desc')
      ->skip($skip)->take($take)
      ->get();
      return response()->json([
               'status' => 'true',
               'data' => $comment ,
               'messenge' => ''
           ]);
    }
    public function getCommentByBookId($bookId)
    {
      return response()->json([
               'status' => 'true',
               'data' => self::getCommentByBookId1($bookId) ,
               'messenge' => ''
           ]);
    }
    function getCommentByBookId1($bookId)
    {
      $comment = DB::table('comment')->select("comment.id as commentId",
       "comment.content as commentContent","comment.timeUpload as commentTimeUpload" ,
        "user.id as userId" , "user.name as userName" , "user.avatar as userAvatar" )
      ->leftJoin('user' , 'user.id' , '=' , 'comment.userId')
      ->where('comment.bookId',$bookId)
      ->orderBy('comment.timeUpload','desc')
      ->get();
      return $comment;
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
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
