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
        if (!Schema::hasTable('author')) {
            Schema::create('author', function (Blueprint $table) {
                $table->string('id', 200);
                $table->string('name', 200);
                //
                $table->primary('id');
            });
        }   
        if (!Schema::hasTable('user')) {
            Schema::create('user', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('avatar', 1000); 
                $table->string('name', 200);
                $table->string('email', 200);
                $table->string('password');
            });
        }
        if (!Schema::hasTable('book')) {
            Schema::create('book', function (Blueprint $table) {
                $table->string('id', 200);
                $table->string('avatar', 1000);
                $table->string('name', 200);
                $table->longText('description');
                $table->string('authorId', 200);
                $table->timestamp('datePublication')->useCurrent();
                $table->timestamp('lastestUpdate')->useCurrent();
                $table->bigInteger('submitUserId')->unsigned()->nullable();
                $table->string('status', 50);
                $table->integer('view')->default(1);
                $table->integer('chaptCount')->default(1);
                $table->string('lastChaptname', 200)->default("");
                $table->integer('lastChapStt')->default(1);
                //
                $table->primary('id');
                $table->index('name');
                //refer
                $table->foreign('submitUserId')->references('id')->on('user');
                $table->foreign('authorId')->references('id')->on('author');
            });
        }
        if (!Schema::hasTable('category')) {
            Schema::create('category', function (Blueprint $table) {
                $table->string('id', 200);
                $table->string('name', 200);
                $table->boolean('accept')->default(true);
                //
                $table->index('name');
                $table->primary('id');
            });
        }
        if (!Schema::hasTable('book_category')) {
            Schema::create('book_category', function (Blueprint $table) {
                $table->string('bookId', 200);
                $table->string('categoryId', 200)->default(true);
                $table->boolean('accept');
                //
                $table->index('bookId')->references('id')->on('book');
                $table->index('categoryId')->references('id')->on('category');
            });
        }
        if (!Schema::hasTable('tag')) {
            Schema::create('tag', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 200);
            });
        }
        if (!Schema::hasTable('book_tag')) {
            Schema::create('book_tag', function (Blueprint $table) {
                $table->string('bookId', 200);
                $table->integer('tagId')->unsigned();
                //
                $table->index('bookId')->references('id')->on('book');
                $table->index('tagId')->references('id')->on('tag');
            });
        }
        if (!Schema::hasTable('reaction')) {
            Schema::create('reaction', function (Blueprint $table) {
                $table->increments('id');
                $table->string('icon',200);
            });
        }
        if (!Schema::hasTable('book_user')) {
            Schema::create('book_user', function (Blueprint $table) {
                $table->string('bookId', 200);
                $table->bigInteger('userId')->unsigned();
                $table->integer('reactionId')->unsigned();
                $table->integer('vote')->default(0);
                //
                $table->foreign('bookId')->references('id')->on('book');
                $table->foreign('userId')->references('id')->on('user');
                $table->foreign('reactionId')->references('id')->on('reaction');
            });
        }
        if (!Schema::hasTable('comments')) {
            Schema::create('comments', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('bookId', 200);
                $table->bigInteger('userId')->unsigned();
                //
                $table->foreign('bookId')->references('id')->on('book');
            });
        }
        if (!Schema::hasTable('chapter')) {
            Schema::create('chapter', function (Blueprint $table) {
                $table->string('bookId', 200);
                $table->integer('stt');
                $table->text('name');
                $table->timestamp('timeUpload')->useCurrent();
                //
                $table->foreign('bookId')->references('id')->on('book');
            });
        }
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
