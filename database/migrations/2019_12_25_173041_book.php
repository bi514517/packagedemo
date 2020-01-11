<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Book extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('author', function (Blueprint $table) {
            $table->string('id', 200);
            $table->string('name', 200);
            //
            $table->primary('id');
        });
        Schema::create('book', function (Blueprint $table) {
            $table->string('id', 200);
            $table->string('avatar', 1000);
            $table->string('name', 200);
            $table->longText('description');
            $table->string('authorId', 200);
            $table->timestamp('datePublication')->useCurrent();
            $table->timestamp('lastestUpdate')->useCurrent();
            $table->string('submitUserId', 50);
            $table->string('status', 50);
            $table->integer('view',);
            $table->integer('chaptCount')->default(1);
            $table->string('lastChaptname', 200)->default("");
            $table->integer('lastChapStt')->default(1);
            //
            $table->primary('id');
            $table->index('name');
            //refer
            $table->foreign('authorId')->references('id')->on('author');
        });
        Schema::create('category', function (Blueprint $table) {
            $table->string('id', 200);
            $table->string('name', 200);
            $table->boolean('accept')->default(true);
            //
            $table->primary('id');
        });
        Schema::create('book_category', function (Blueprint $table) {
            $table->string('bookId', 200);
            $table->string('categoryId', 200)->default(true);
            $table->boolean('accept');
            //
            $table->foreign('bookId')->references('id')->on('book');
            $table->foreign('categoryId')->references('id')->on('category');
        });
        Schema::create('tag', function (Blueprint $table) {
            $table->string('id', 200);
            $table->string('name', 200);
            //
            $table->primary('id');
        });
        Schema::create('book_tag', function (Blueprint $table) {
            $table->string('bookId', 200);
            $table->string('tagId');
            //
            $table->foreign('bookId')->references('id')->on('book');
            $table->foreign('tagId')->references('id')->on('tag');
        });
        Schema::create('user', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('email', 200);
            $table->string('password');
        });
        Schema::create('book_user', function (Blueprint $table) {
            $table->string('bookId', 200);
            $table->integer('vote')->default(0);
            $table->bigInteger('userId')->unsigned();
            //
            $table->foreign('bookId')->references('id')->on('book');
            $table->foreign('userId')->references('id')->on('user');
        });
        Schema::create('chapter', function (Blueprint $table) {
            $table->string('bookId', 200);
            $table->integer('stt');
            $table->string('name', 200);
            $table->timestamp('timeUpload')->useCurrent();
            //
            $table->foreign('bookId')->references('id')->on('book');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
