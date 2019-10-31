<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

require 'Dom.php';


class LeechTruyenYY extends Controller
{

  function leechSingleBook(Request $request){
      $request = json_decode(str_replace(chr(92),"",$request->getContent()));
      $url = $request->url;
      self::leechBook($url);
  }
  function leechBook($url){
    try {
      $contents = str_get_html(file_get_contents($url)); ///Lấy toàn bộ nội dung html của trang đó
      $detail = $contents->find('div[class=novel-detail]'); ///Cắt chuỗi
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
      self::insertBook($bookName,$bookAvatar,$bookDesciption,$authorName,$bookDatePublication,$bookStatus);
      self::insertBook_Category($bookName,$categories);
      self::insertBook_Tag($bookName,$tags);
      self::getChapters($url,$bookName);
    } catch (Exception $e) {
    }
  }
  function updateBooks() {
      $books = self::getNewBookList();
      foreach ($books as $bookUrl) {
        self::leechBook($bookUrl);
      }
  }
  function getNewBookList() {
    $arr = array();;
    $url = 'https://truyenyy.com/truyen-moi-cap-nhat/'; ///Trang bạn muốn leech
    $contents = str_get_html(file_get_contents($url));
    $container = $contents->find('div[class=weui-panel__bd]')[0]->find('a');
    foreach ($container as $bookURL) {
      $fullUrl = self::addDomain($bookURL->href);
      array_push($arr,$fullUrl);
    }
    return $arr;
  }
  function addDomain($url){
    if(strpos($url, 'https://truyenyy.com') !== false){
      return $url;
    }
    else {
      if($url[0] !== "/") {
        $url = "https://truyenyy.com/" . $url;
      }
      else {
        $url = "https://truyenyy.com" . $url;
      }
    }
    return $url;
  }
  function addChapListUrl($url){
    $char = substr($url, -1);
    if($char != "/")
    {
      return $url."/danh-sach-chuong/";
    }
    return $url."danh-sach-chuong/";
  }
  function getAvatar($detail){
      $img = $detail->find('img[class=lazy]');
      $url = explode('data-src="', $img[0]);
      $url = explode('"', $url[1])[0];
    return $url;
  }
  function getName($detail){
      $name = $detail->find('h1[class=title]')[0]->innertext;
    return $name;
  }
  function getAuthor($detail){
      $name = $detail->find('div[class=alt-name]')[0]->find('a')[0]->innertext;
    return $name;
  }
  function getStatus($detailBonus){
      $status = $detailBonus[1]->find('td')[1]->innertext;
    return $status;
  }
  function getCategories($detailBonus){
    $arr = array();
    if(isset($detailBonus[0]->find('td')[1] ) ) {
      foreach ($detailBonus[0]->find('td')[1]->find('a') as $key) {
        array_push($arr,$key->innertext);
      }
    }
    return $arr;
  }
  function getTags($detail){
   $arr = array();
   $tagsContenter = $detail->find('h3[class=alt-name]');
   if(count($tagsContenter)>0){
     foreach ($tagsContenter[0]->find('a') as $key) {
       array_push($arr,$key->innertext);
     }
   }
   return $arr;
  }
  function getDatePublication($detailBonus){
      //$time = $detailBonus[5]->find('td')[1]->innertext ."-01-01";
    $time = now();
    return $time;
  }
  function getDescription($contents){
    $description = "";
    if(count($contents->find('div[class=novel-summary-more]')) > 0)
      $description = $contents->find('div[class=novel-summary-more]');
    if(count($description[0]->find('div[id=summary_markdown]')) > 0)
      $description = $description[0];
    return $description;
  }
  function getTabList($contents,$baseUrl){
    $arr = array();
    $listContainer = $contents->find('div[class=cell-box]');
    if(count($listContainer) > 0){
      $list = $listContainer[0]->find('a');
      foreach ($list as $key) {
        array_push($arr,self::addDomain($key->href));
      }
    }
    array_push($arr,$baseUrl);
    return $arr;
  }
  function getChapters($url,$bookName){
    echo "đang tải truyện " . $bookName;
    $url = self::addChapListUrl($url);
    $contents = str_get_html(file_get_contents($url));
    $tabList = self::getTabList($contents,$url);
    $count = 0;
    foreach ($tabList as $tab) {
        $content = str_get_html(file_get_contents($tab));
        $chapters = $content->find('div[class=weui-cells]')[1]->find('a');
        foreach ($chapters as $chapter) {
          $chapterName = $chapter->find('div[class=weui-cell__bd weui-cell_primary]')[0]->innertext;
          $chapterUrl = self::addDomain($chapter->href);
          $count++;
          $bookId = self::makeIdFromName($bookName);
          $isVipRequire = self::checkChapterExist($count,$bookId) ? false : self::downloadChapter($bookId,$chapterUrl,$chapterName,$count);
          if( $isVipRequire)
            return;
          else {
            self::insertChapter($bookName,$count,$chapterName);
          }
          sleep(0.2);
        }
    }
  }
  function downloadChapter($bookId,$url,$chapterName,$chapterStt){
    $data = str_get_html(@file_get_contents($url))->find('div[class=chap-content]');
    $isVipRequire = ( count($data) == 0) ;
    if ($isVipRequire === false) {
      $isVipRequire = strpos($data[0], 'Truyện này yêu cầu đăng nhập mới được xem chương.') !== false;
    }
    $content = $isVipRequire ? null : $data[0];
    if($isVipRequire === false) {
      app('App\Http\Controllers\Chapter')->saveChapter($bookId,$chapterStt,$chapterName,$content);
    }
    return $isVipRequire;
  }
  function insertAuthor($author){
    DB::insert('insert INTO author (author.id,author.name) values (?, ?) ON DUPLICATE KEY UPDATE author.name = VALUES(author.name) ',
    [self::makeIdFromName($author), $author]);
  }
  function insertCategories($categories){
    foreach ($categories as $category) {
      $query = "insert INTO category (category.id,category.name)
      VALUES (?, ?)
       ON DUPLICATE KEY UPDATE category.name = VALUES(category.name)";
      DB::insert($query,[self::makeIdFromName($category), $category]);
    }
  }
  function insertTags($tags){
    foreach ($tags as $tag) {
      $query = "insert into tag (tag.name) VALUES
        (?)
        ON DUPLICATE KEY UPDATE tag.name = VALUES(tag.name)";
      DB::insert($query,[ $tag]);
    }
  }
  function insertBook($bookName,$bookAvatar,$bookDesciption,$authorName,$bookDatePublication,$bookStatus) {
    $query = "insert INTO book(book.id, book.avatar, book.name, book.description, book.authorId , book.datePublication , book.lastestUpdate , book.status)
    VALUES (?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE book.status = VALUES(book.status)";
    DB::insert($query,
    [self::makeIdFromName($bookName), $bookAvatar,$bookName,$bookDesciption,self::makeIdFromName($authorName),$bookDatePublication,$bookDatePublication,$bookStatus]);
  }
  function insertBook_Category($bookName,$categories) {
    $query = "insert into `book_category`(`bookId`, `categoryId`)
    values (?,?) ON DUPLICATE KEY UPDATE book_category.bookId = VALUES(book_category.bookId)";
    foreach ($categories as $categoryName) {
      DB::insert($query,
      [self::makeIdFromName($bookName), self::makeIdFromName($categoryName)]);
    }
  }
  function insertBook_Tag($bookName,$tags) {
    $query = "insert INTO book_tag (book_tag.bookId, book_tag.tagId)
    VALUES  (?,(SELECT tag.id FROM tag WHERE tag.name = ? LIMIT 1)) ON DUPLICATE KEY UPDATE book_tag.bookId = VALUES(book_tag.bookId)";
    foreach ($tags as $tagName) {
      DB::insert($query,
      [self::makeIdFromName($bookName),$tagName]);
    }
  }
  function insertChapter($bookName,$stt,$chapterName){
    $bookId = self::makeIdFromName($bookName);
    $updateTime = now();
    $query = "insert INTO `chapter`(`bookId`, `stt`, `timeUpload`, `name`) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE chapter.name = values(chapter.name)";
    DB::insert($query,
    [$bookId,$stt,$updateTime,$chapterName]);
    // update info book
    DB::table('book')
     ->where('book.id', $bookId)
     ->update(['book.lastestUpdate' => $updateTime]);
  }
  function checkChapterExist($stt,$bookId) {
    return DB::table('chapter')
            ->where('chapter.stt', $stt)
            ->where('chapter.bookId', $bookId)
            ->exists();
  }
  function removeAccents($str){
    if(!$str) return false;
       $unicode = array(
          'a'=>'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
          'd'=>'đ',
          'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
          'i'=>'í|ì|ỉ|ĩ|ị',
          'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
          'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
          'y'=>'ý|ỳ|ỷ|ỹ|ỵ',
       );
    foreach($unicode as $nonUnicode=>$uni) $str = preg_replace("/($uni)/i",$nonUnicode,$str);
    return $str;
  }
  function makeIdFromName($name){
    $id = mb_strtolower($name, 'UTF-8');
    $id = self::removeAccents($id);
    $id = str_replace(" ","-",$id);
    $id = preg_replace("/\s+/","",$id);
    $id = preg_replace("/[^a-z0-9\_\-\.]/i","",$id);
    $id = str_replace(".","",$id);
    return $id;
  }
}
