<?php

namespace App\Http\Controllers\publish;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\book;
use App\category;
use App\chapter;
use Illuminate\Support\Facades\URL;
use App\utils\utilsFunction;

class publish extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function home()
    {
        $newBooks = book::getNewestBooks();
        $categoriesList = category::getAccepted();
        $topViewBooks = book::getTopViewBooks();
        $justUpdatedBooks = book::getJustUpdatedBook();
        $recommendBooks = book::getRecommendBooks();
        return view('publish.home', compact('newBooks', 'categoriesList', 'topViewBooks', 'justUpdatedBooks', 'recommendBooks'));
    }

    public function detail($id)
    {
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $amountOfPage = 20;
        $book = book::getBookById($id, $page, $amountOfPage);
        $pageAmount = ceil(chapter::countChaptersByBook($id) / $amountOfPage);
        if ($pageAmount < $page) {
            return redirect(url("truyen/" . $id . "?page=" . $pageAmount));
        } else if ($page < 1) {
            return redirect(url("truyen/" . $id . "?page=1"));
        }
        return view('publish.detail', compact('book'));
    }
    public function chapter($bookId, $chapterStt)
    {
        $content = chapter::getChapter($bookId, $chapterStt);
        return view('publish.chapter', compact('content'));
    }
}
