<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class User extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $request = json_decode(str_replace(chr(92),"",$request->getContent()));
        $data = DB::table('user')->select('user.id')
        ->where('user.email', $request->email)->get();
        if( count($data) == 0 ){
          $email = $request->email;
          $name = isset($request->name) ? $request->name : $request->email;
          $avatar = isset($request->avatar) ? $request->avatar : env("DEFAULT_USER_AVATAR", "default");
          DB::table('user')->insert(
              ['email' => $email,
               'name' => $name,
               'avatar' => $avatar]
          );
          $data = DB::table('user')->select('user.id')
          ->where('user.email', $request->email)->get();
          return response()->json([
                   'status' => 'true',
                   'data' => $data[0]->id ,
                   'messenge' => 'insert success'
               ]);
        }
        else {
          return response()->json([
                   'status' => 'true',
                   'data' => $data[0]->id ,
                   'messenge' => 'get success'
               ]);
        }
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
    public function show($id)
    {
        //
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
