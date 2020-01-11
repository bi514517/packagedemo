<?php

namespace App\Http\Controllers;

use App\author;
use App\book;
use App\book_category;
use App\book_tag;
use App\category;
use App\chapter;
use App\tag;
use App\utils\utilsFunction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Exception;
use Illuminate\Support\Facades\Log;

require 'Dom.php';
class LeechTruyenFull extends Controller
{
    public function start()
    {
        echo "\nINFO : lấy danh sách , bắt đầu tải truyệnfull";
        Log::info("lấy danh sách , bắt đầu tải truyệnfull");
        $books = self::getNewBookList();
        foreach ($books as $bookUrl) {
            self::leechBook($bookUrl);
        }
    }

    function getNewBookList()
    {
        $arr = array();
        $url = Config::get('configVar.truyenfull.domain'); ///Trang bạn muốn leech
        $contents = str_get_html(file_get_contents($url));
        $container = $contents->find('h3[itemprop=name]');
        foreach ($container as $bookURL) {
            if (isset($bookURL->find('a')[0])) {
                $bookURL = $bookURL->find('a')[0];
                $fullUrl = self::addDomain($bookURL->href);
                array_push($arr, $fullUrl);
            }
        }
        return $arr;
    }
    function addDomain($url)
    {
        if (strpos($url, Config::get('configVar.truyenfull.domain')) !== false) {
            return $url;
        } else {
            if ($url[0] !== "/") {
                $url = Config::get('configVar.truyenfull.domain') . "/" . $url;
            } else {
                $url = Config::get('configVar.truyenfull.domain') . $url;
            }
        }
        return $url;
    }
    function leechBook($url)
    {
        try {
            $date = date('Y/m/d H:i:s');
            echo "\nINFO : " . $date . " Bắt đầu tải truyện " . $url;
            Log::info($date . " Bắt đầu tải truyện " . $url);
            $contents = str_get_html(file_get_contents($url)); ///Lấy toàn bộ nội dung html của trang đó
            $detail = null; ///Cắt chuỗi
            if (isset($contents->find('div[class=info]')[0])) {
                $detail = $contents->find('div[class=info]')[0];
            } else throw new Exception("không tìm thấy thông tin sách");
            // get data
            $bookName = self::getName($contents);
            $authorName = self::getAuthor($detail);
            $bookAvatar = self::getAvatar($contents);
            $bookStatus = self::getStatus($detail);
            $bookDatePublication = self::getNow();
            $bookDesciption = self::getDescription($contents);
            $categories = self::getCategories($detail);
            $tags = self::getTags($bookName);

            author::insertAuthor($authorName);
            tag::insertTags($tags);
            category::insertCategories($categories);
            book::insertBook($bookName, $bookAvatar, $bookDesciption, $authorName, $bookDatePublication, $bookStatus);
            book_category::insertBook_Category($bookName, $categories);
            book_tag::insertBook_Tag($bookName, $tags);

            $truyenFullId = self::getTruyenFullId($contents);
            self::getChapters($url, $truyenFullId, $bookName);
            echo "\nINFO : tải truyện thành công ";
            Log::info("tải truyện thành công");
        } catch (Exception $e) {
            echo "\nERROR : " . $e->getMessage();
            echo "\nERROR : " . $e;
            echo "\nERROR : tải truyện thất bại";
            Log::error($e->getMessage());
            Log::error($e);
            Log::error("tải truyện thất bại");
        }
    }
    function getName($contents)
    {
        $names = $contents->find('h1');
        if (isset($names[0])) {
            $name = strip_tags($names[0]);
            $name = trim(preg_replace('/\s+/', ' ', $name));
            return $name;
        } else {
            throw new Exception("không tìm thấy thẻ h1 chứa tên sách");
        }
    }
    function getAuthor($detail)
    {
        $authorInfo = $detail->find("a[itemprop='author']");

        if (isset($authorInfo[0])) {
            return strip_tags($authorInfo[0]);
        } else {
            throw new Exception("không tìm thấy thẻ chứa tên tác giả");
        }
    }
    function getAvatar($contents)
    {
        $avtInfo = $contents->find("div[class=book]");

        if (isset($avtInfo[0])) {
            $avt = $avtInfo[0]->find("img");
            if (isset($avt[0])) {
                $src = explode('src="', $avt[0]);
                if (is_array($src) && count($src) > 1) {
                    $src = explode('"', $src[1]);
                    if (is_array($src) && count($src) > 0) {
                        return $src[0];
                    }
                }
            }
        }
        throw new Exception("không tìm thấy thẻ chứa thông tin ảnh");
    }
    function getStatus($detail)
    {
        foreach ($detail->find("div") as $key) {
            if (!isset($key->find('a')[0])) {
                if ($key->find('span')[0]) {
                    $status = strip_tags($key->find('span')[0]);
                    $status = trim(preg_replace('/\s+/', ' ', $status));
                    return $status;
                }
            }
        }
        echo "\nWARNING : không tìm  thấy dữ liệu trang thái truyện";
        Log::warning("không tìm  thấy dữ liệu trang thái truyện");
        return "còn tiếp";
    }

    function getDescription($contents)
    {
        $data = $contents->find('div[itemprop=description]');
        if (isset($data[0])) {
            return $data[0];
        }
        echo "\nWARNING : không tìm  thấy mô tả truyện";
        Log::warning("không tìm  thấy mô tả truyện");
        return "không có mô tả cụ thể";
    }

    function getCategories($detail)
    {
        $arr = array();
        $categoriesInfo = $detail->find("a[itemprop=genre]");
        foreach ($categoriesInfo as $category) {
            array_push($arr, strip_tags($category));
        }
        if (count($arr) <= 0) {
            echo "\nWARNING : không có thông tin thể loại của truyện";
            Log::warning("không có thông tin thể loại của truyện");
        }
        return $arr;
    }
    function getTags($name)
    {
        $tag = "#";
        $name = mb_strtolower($name, 'UTF-8');
        $name = utilsFunction::removeAccents($name);
        $name = preg_replace("/[^A-Za-z0-9 ]/", '', $name);
        $nameArr = str_split(strtoupper($name), 1);
        for ($i = 0; $i < count($nameArr); $i++) {
            if (!isset($nameArr[$i - 1]) || ($nameArr[$i - 1] == " " && preg_match('/^[a-zA-Z]$/', $nameArr[$i])))
                $tag .= $nameArr[$i];
        }
        return [$tag];
    }
    function getNow()
    {
        //$time = $detailBonus[5]->find('td')[1]->innertext ."-01-01";
        $time = now();
        return $time;
    }

    function getTruyenFullId($content)
    {
        $id = $content->find("input[id=truyen-id]");
        if (isset($id[0])) {
            echo "\nINFO : id trog truyenfull là " . $id[0]->value;
            Log::info("id trog truyenfull là " . $id[0]->value);
            return $id[0]->value;
        } else throw new Exception("không tìm thấy id của truyenfull");
    }

    public function getChapList($contents)
    {
        $arr = array();
        $list = $contents->find("option");
        foreach ($list as $chapter) {
            array_push($arr, $chapter->value);
        }
        if (count($arr) <= 0) {
            throw new Exception("không tìm thấy danh sách chương");
        }
        return $arr;
    }

    function getChaptStt($chapUrl)
    {
        $chapUrl = mb_strtolower($chapUrl, 'UTF-8');
        $res = preg_replace("/[^0-9]/", "", substr($chapUrl, strpos("", "chuong")));
        return (int) $res;
    }

    function getChapters($bookUrl, $truyenFullId, $bookName)
    {
        $bookId = utilsFunction::createSlugId($bookName);
        $stt = 0;
        $chaptName = "";

        $chapListUrl = self::addDomain(Config::get('configVar.truyenfull.danhsachchuong') . $truyenFullId);
        $contents = str_get_html(file_get_contents($chapListUrl));
        if (!is_object($contents)) {
            throw new Exception("không lấy được danh sách chương tại đường dẫn " . $chapListUrl);
        }
        $chapList = self::getChapList($contents);
        foreach ($chapList as $chapter) {
            $chaptUrl = $bookUrl . $chapter;
            $stt = self::getChaptStt($chaptUrl);
            if (!chapter::checkChapterExist($stt, $bookId)) {
                $contents = str_get_html(file_get_contents($chaptUrl));
                if (!is_object($contents)) {
                    echo "\nINFO : chương " . $stt . " không tìm thấy dữ liệu chương";
                    Log::info("chương " . $stt . " không tìm thấy dữ liệu chương");
                    continue;
                }
                if (isset($contents->find("a[class=chapter-title]")[0])) {
                    $chaptName = strip_tags($contents->find("a[class=chapter-title]")[0]);
                } else {
                    echo "\nINFO : chương không tìm thấy tên chương";
                    Log::info("chương không tìm thấy tên chương");
                    continue;
                }
                $contents = $contents->find("div[id=chapter-c]");
                if (isset($contents[0])) {
                    // xóa quảng cáo
                    $contents = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $contents[0]->innertext);
                } else {
                    echo "\nINFO : chương " . $chaptName . " không tìm thấy nội dung";
                    Log::info("chương " . $chaptName . " không tìm thấy nội dung");
                    continue;
                }
                if (utilsFunction::saveChapter($bookId, $stt, $contents))
                    chapter::insertChapter($bookId, $stt, $chaptName);
                sleep(0.5);
            }
        }
        // nếu có dữ liệu chương update
        book::updateLastChapt($bookId, $stt, $chaptName, $stt);
        book::delBookIfNoChapt($bookId);
    }
}
