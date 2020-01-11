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
use Illuminate\Support\Facades\Log;
use Exception;

require 'Dom.php';


class LeechTruyenYY extends Controller
{

  function leechSingleBook(Request $request)
  {
    $request = json_decode(str_replace(chr(92), "", $request->getContent()));
    $url = $request->url;
    self::leechBook($url);
  }
  function leechBook($url)
  {
    try {
      $contents = str_get_html(file_get_contents($url)); ///Lấy toàn bộ nội dung html của trang đó
      $detail = $contents->find('div[class=novel-detail]')[0]->find('div[class=info-zone]'); ///Cắt chuỗi
      $detail = $detail[0];
      $detailBonus = $contents->find('div[class=novel-meta]')[0]->find('tr'); ///Cắt chuỗi
      // get data
      $bookName = self::getName($detail);
      $authorName = self::getAuthor($detail);
      $bookAvatar = self::getAvatar($detail);
      $bookStatus = self::getStatus($detailBonus);
      $bookDatePublication = self::getDatePublication($detailBonus);
      $bookDesciption = self::getDescription($contents);
      $categories = self::getCategories($detailBonus);
      $tags = self::getTags($detail);
      // thêm sách vào db
      author::insertAuthor($authorName);
      tag::insertTags($tags);
      category::insertCategories($categories);
      book::insertBook($bookName, $bookAvatar, $bookDesciption, $authorName, $bookDatePublication, $bookStatus);
      book_category::insertBook_Category($bookName, $categories);
      book_tag::insertBook_Tag($bookName, $tags);
      //
      self::getChapters($url, $bookName);
      $bookId = utilsFunction::createSlugId($bookName);
      book::delBookIfNoChapt($bookId);
    } catch (Exception $e) {
      echo "\nERROR : " . $e->getMessage();
      echo "\nERROR : " . $e;
      echo "\nERROR : tải truyện thất bại";
      Log::error($e->getMessage());
      Log::error($e);
      Log::error("tải truyện thất bại");
    }
  }
  public function start()
  {
    echo "\nINFO : lấy danh sách , bắt đầu tải truyệnyy";
    Log::info("lấy danh sách , bắt đầu tải truyệnyy");
    $books = self::getNewBookList();
    foreach ($books as $bookUrl) {
      self::leechBook($bookUrl);
    }
  }
  function getNewBookList()
  {
    $arr = array();;
    $url = Config::get('configVar.truyenyy.domain') . '/' . Config::get('configVar.truyenyy.moicapnhat') . '/'; ///Trang bạn muốn leech
    $contents = str_get_html(file_get_contents($url));
    $container = $contents->find('div[class=weui-panel__bd]')[0]->find('a');
    foreach ($container as $bookURL) {
      $fullUrl = self::addDomain($bookURL->href);
      array_push($arr, $fullUrl);
    }
    return $arr;
  }
  function addDomain($url)
  {
    if (strpos($url, Config::get('configVar.truyenyy.domain')) !== false) {
      return $url;
    } else {
      if ($url[0] !== "/") {
        $url = Config::get('configVar.truyenyy.domain') . "/" . $url;
      } else {
        $url = Config::get('configVar.truyenyy.domain') . $url;
      }
    }
    return $url;
  }
  function addChapListUrl($url)
  {
    $char = substr($url, -1);
    if ($char != "/") {
      return $url . "/" . Config::get('configVar.truyenyy.danhsachchuong') . "/";
    }
    return $url . Config::get('configVar.truyenyy.danhsachchuong') . "/";
  }
  function getAvatar($detail)
  {
    $defaultAvt = " ";
    $img = $detail->find('img[class=lazy]');
    if (is_array($img) && count($img) > 0) {
      $url = explode('data-src="', $img[0]);
      if (is_array($url) && count($url) > 1) {
        $url = explode('"', $url[1]);
        if (is_array($url) && count($url) > 0) {
          return $url[0];
        }
      }
    }
    return $defaultAvt;
  }
  function getName($detail)
  {
    if (isset($detail->find('h1[class=title]')[0])) {
      $name = $detail->find('h1[class=title]')[0]->innertext;
      $name = trim(preg_replace('/\s+/', ' ', $name));
      return strip_tags($name);
    } else {
      throw new Exception("\nkhông tìm thấy tên sách");
    }
  }
  function getAuthor($detail)
  {
    $name = " ";
    if (isset($detail->find('div[class=alt-name mb-1]')[0])) {
      $name = $detail->find('div[class=alt-name mb-1]')[0];
      if (isset($name->find('a')[0])) {
        $name = $name->find('a')[0]->innertext;
        $name = trim(preg_replace('/\s+/', ' ', $name));
        return strip_tags($name);
      }
    }
    throw new Exception("\nkhông tìm thấy tên tác giả");
  }
  function getStatus($detailBonus)
  {
    if (isset($detailBonus[1])) {
      $status = $detailBonus[1];
      if ($status->find('td')[1]) {
        $status = $status->find('td')[1]->innertext;
      }
      $status = trim(preg_replace('/\s+/', ' ', $status));
      return $status;
    }
    throw new Exception("\nkhông tìm thấy trạng thái");
  }
  function getCategories($detailBonus)
  {
    $arr = array();
    if (isset($detailBonus[0]->find('td')[1])) {
      foreach ($detailBonus[0]->find('td')[1]->find('a') as $key) {
        array_push($arr, $key->innertext);
      }
    }
    return $arr;
  }
  function getTags($detail)
  {
    $arr = array();
    $tagsContenter = $detail->find('div[class=alt-name mb-1]');
    if (isset($tagsContenter[1])) {
      foreach ($tagsContenter[1]->find('a') as $key) {
        array_push($arr, $key->innertext);
      }
    }
    return $arr;
  }
  function getDatePublication($detailBonus)
  {
    //$time = $detailBonus[5]->find('td')[1]->innertext ."-01-01";
    $time = now();
    return $time;
  }
  function getDescription($contents)
  {
    $description = "";
    $datas = $contents->find('div[class=novel-summary-more]');
    if (is_array($datas))
      foreach ($datas as $data) {
        $description .= strip_tags($data, "<p><a><div>");
      } else
      $description .= strip_tags($datas, "<p><a><div>");
    $description = preg_replace('/<button\b[^>]*>(.*?)<\/button>/is', "", $description);
    return $description;
  }

  function getTabList($contents, $url)
  {
    $arr = array();
    $listContainer = $contents->find('div[class=cell-box]');
    if (isset($listContainer[0])) {
      $list = $listContainer[0]->find('a');
      foreach ($list as $key) {
        array_push($arr, self::addDomain($key->href));
      }
    } else {
      array_push($arr, $url);
    }
    return $arr;
  }

  function getChaptStt($chapUrl)
  {
    $chapUrl = mb_strtolower($chapUrl, 'UTF-8');
    $res = preg_replace("/[^0-9]/", "", substr($chapUrl, strpos("", "chuong")));
    return (int) $res;
  }

  function getNow()
  {
    //$time = $detailBonus[5]->find('td')[1]->innertext ."-01-01";
    $time = now();
    return $time;
  }

  function getChapterList($tabList, $bookName)
  {
    $bookId = utilsFunction::createSlugId($bookName);
    $stt = 0;
    $chapterName = "";
    // kiểm tra xem tải được gì không
    $hasDownload = false;
    foreach ($tabList as $tab) {
      $content = str_get_html(file_get_contents($tab));
      $chapters = $content->find('div[class=weui-cells]')[1]->find('a');
      foreach ($chapters as $chapter) {
        $chapterName = $chapter->find('div[class=weui-cell__bd weui-cell_primary]')[0]->innertext;
        $chapterName = trim(preg_replace('/\s+/', ' ', $chapterName));
        $chapterUrl = self::addDomain($chapter->href);
        $stt = self::getChaptStt($chapterUrl);
        $hasDownload = true;
        if (!chapter::checkChapterExist($stt, $bookId)) {
          $content = self::getChapterContent($chapterUrl);
          if ($content === true) {
            echo "\ntừ chương " . $stt . "yêu cầu vip hoặc đăng nhập";
            Log::info("từ chương " . $stt . "yêu cầu vip hoặc đăng nhập");
            $hasDownload = $stt != 1;
            break 2;
          }
          if ($hasDownload = utilsFunction::saveChapter($bookId, $stt, $content))
            $hasDownload = chapter::insertChapter($bookId, $stt, $chapterName);
          sleep(0.5);
        }
      }
    }
    // nếu có dữ liệu chương update
    if ($hasDownload) {
      book::updateLastChapt($bookId, $stt, $chapterName, $stt);
    } else {
      book::delBookIfNoChapt($bookId);
    }
  }


  function getChapterContent($url)
  {
    $data = str_get_html(@file_get_contents($url))->find('div[class=chap-content]');
    // kiếm tra vip k
    $isVipRequire = !isset($data[0]);
    if ($isVipRequire == false) {
      // kiểm tra có yêu cầu đăng nhập k
      $isVipRequire = (strpos($data[0], 'Truyện này yêu cầu đăng nhập mới được xem chương.') !== false) || strlen($data[0]) < 300;
    }
    $content = $isVipRequire ? null : $data[0];
    if ($isVipRequire == false) {
      return $content;
    }
    return $isVipRequire;
  }

  function getChapters($url, $bookName)
  {
    echo "\nđang tải truyện " . $bookName;
    Log::info("đang tải truyện " . $bookName);
    $url = self::addChapListUrl($url);
    $contents = str_get_html(file_get_contents($url));
    if (!is_object($contents)) {
      throw new Exception("không lấy được danh sách chương tại đường dẫn " . $url);
    }
    $tabList = self::getTabList($contents, $url);
    self::getChapterList($tabList, $bookName);
  }
}
