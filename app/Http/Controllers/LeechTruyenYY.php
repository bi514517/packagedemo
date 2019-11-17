<?php

namespace App\Http\Controllers;

use App\utils\utilsFunction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

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
      self::insertAuthor($authorName);
      self::insertTags($tags);
      self::insertCategories($categories);
      self::insertBook($bookName, $bookAvatar, $bookDesciption, $authorName, $bookDatePublication, $bookStatus);
      self::insertBook_Category($bookName, $categories);
      self::insertBook_Tag($bookName, $tags);
      //
      self::getChapters($url, $bookName);
    } catch (Exception $e) { }
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
    $name = $detail->find('h1[class=title]')[0]->innertext;
    $name = trim(preg_replace('/\s+/', ' ', $name));
    return strip_tags($name);
  }
  function getAuthor($detail)
  {
    $name = " ";
    if (isset($detail->find('div[class=alt-name mb-1]')[0]))
      $name = $detail->find('div[class=alt-name mb-1]')[0];
    if (count($name->find('a')) > 0)
      $name = $name->find('a')[0]->innertext;
    $name = trim(preg_replace('/\s+/', ' ', $name));
    return strip_tags($name);
  }
  function getStatus($detailBonus)
  {
    $status = $detailBonus[1]->find('td')[1]->innertext;
    return $status;
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
    return $description;
  }

  function getTabList($contents, $baseUrl)
  {
    $arr = array();
    $listContainer = $contents->find('div[class=cell-box]');
    if (count($listContainer) > 0) {
      $list = $listContainer[0]->find('a');
      foreach ($list as $key) {
        array_push($arr, self::addDomain($key->href));
      }
    }
    array_push($arr, $baseUrl);
    return $arr;
  }
  function getChapters($url, $bookName)
  {
    echo "\nđang tải truyện " . $bookName;
    $url = self::addChapListUrl($url);
    $contents = str_get_html(file_get_contents($url));
    $tabList = self::getTabList($contents, $url);
    $count = 0;
    foreach ($tabList as $tab) {
      $content = str_get_html(file_get_contents($tab));
      $chapters = $content->find('div[class=weui-cells]')[1]->find('a');
      array_reverse($chapters);
      foreach ($chapters as $chapter) {
        $chapterName = $chapter->find('div[class=weui-cell__bd weui-cell_primary]')[0]->innertext;
        $chapterUrl = self::addDomain($chapter->href);
        $count++;
        $bookId = self::createSlugId($bookName);
        $isVipRequire = self::checkChapterExist($count, $bookId) ? false : self::downloadChapter($bookId, $chapterUrl, $chapterName, $count);
        if ($isVipRequire)
          return;
        else {
          self::insertChapter($bookName, $count, $chapterName);
        }
        sleep(0.5);
      }
    }
  }
  function downloadChapter($bookId, $url, $chapterName, $chapterStt)
  {
    $data = str_get_html(@file_get_contents($url))->find('div[class=chap-content]');
    $isVipRequire = (count($data) == 0);
    if ($isVipRequire === false) {
      $isVipRequire = (strpos($data[0], 'Truyện này yêu cầu đăng nhập mới được xem chương.') !== false) || strlen($data[0]) < 300;
    }
    $content = $isVipRequire ? null : $data[0];
    if ($isVipRequire === false) {
      if (utilsFunction::saveChapter($bookId, $chapterStt, $content))
        chapter::insertChapter($bookId, $chapterStt, $chapterName);
    }
    return $isVipRequire;
  }
  function insertAuthor($author)
  {
    DB::insert(
      'insert INTO author (author.id,author.name) values (?, ?) ON DUPLICATE KEY UPDATE author.name = VALUES(author.name) ',
      [self::createSlugId($author), $author]
    );
  }
  function insertCategories($categories)
  {
    foreach ($categories as $category) {
      $query = "insert INTO category (category.id,category.name)
      VALUES (?, ?)
       ON DUPLICATE KEY UPDATE category.name = VALUES(category.name)";
      DB::insert($query, [self::createSlugId($category), $category]);
    }
  }
  function insertTags($tags)
  {
    foreach ($tags as $tag) {
      $query = "insert into tag (tag.name) VALUES
        (?)
        ON DUPLICATE KEY UPDATE tag.name = VALUES(tag.name)";
      DB::insert($query, [$tag]);
    }
  }
  function insertBook($bookName, $bookAvatar, $bookDesciption, $authorName, $bookDatePublication, $bookStatus)
  {
    $query = "insert INTO book(book.id, book.avatar, book.name, book.description, book.authorId , book.datePublication , book.lastestUpdate , book.status)
    VALUES (?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE book.status = VALUES(book.status)";
    DB::insert(
      $query,
      [self::createSlugId($bookName), $bookAvatar, $bookName, $bookDesciption, self::createSlugId($authorName), $bookDatePublication, $bookDatePublication, $bookStatus]
    );
  }
  function delBook($bookId)
  {
    DB::table("book_category")->where("bookId", $bookId)->delete();
    DB::table("book_tag")->where("bookId", $bookId)->delete();
    DB::table("user_book")->where("bookId", $bookId)->delete();
    DB::table("book")->where("book.Id", $bookId)->delete();
    echo "đã xóa truyện " . $bookId;
  }
  function insertBook_Category($bookName, $categories)
  {
    $query = "insert into `book_category`(`bookId`, `categoryId`)
    values (?,?) ON DUPLICATE KEY UPDATE book_category.bookId = VALUES(book_category.bookId)";
    foreach ($categories as $categoryName) {
      DB::insert(
        $query,
        [self::createSlugId($bookName), self::createSlugId($categoryName)]
      );
    }
  }
  function insertBook_Tag($bookName, $tags)
  {
    $query = "insert INTO book_tag (book_tag.bookId, book_tag.tagId)
    VALUES  (?,(SELECT tag.id FROM tag WHERE tag.name = ? LIMIT 1)) ON DUPLICATE KEY UPDATE book_tag.bookId = VALUES(book_tag.bookId)";
    foreach ($tags as $tagName) {
      DB::insert(
        $query,
        [self::createSlugId($bookName), $tagName]
      );
    }
  }
  function insertChapter($bookName, $stt, $chapterName)
  {
    $bookId = self::createSlugId($bookName);
    $updateTime = now();
    $query = "insert INTO `chapter`(`bookId`, `stt`, `timeUpload`, `name`) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE chapter.name = values(chapter.name)";
    DB::insert(
      $query,
      [$bookId, $stt, $updateTime, $chapterName]
    );
    // update info book
    DB::table('book')
      ->where('book.id', $bookId)
      ->update(['book.lastestUpdate' => $updateTime]);
  }
  function checkChapterExist($stt, $bookId)
  {
    return DB::table('chapter')
      ->where('chapter.stt', $stt)
      ->where('chapter.bookId', $bookId)
      ->exists();
  }

  function createSlugId($name)
  {
    $id = mb_strtolower($name, 'UTF-8');
    $id = utilsFunction::removeAccents($id);
    $id = str_replace(" ", "-", $id);
    $id = preg_replace("/\s+/", "", $id);
    $id = preg_replace("/[^a-z0-9\_\-\.]/i", "", $id);
    $id = str_replace(".", "", $id);
    return $id;
  }
}
