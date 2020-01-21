<?php

namespace App\utils;

use App\chapter;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class utilsFunction
{
    public static function pagination($page, $amountEachPage, $count)
    {

        $pagerArr = $page > 1 ? [self::pagerObj("<<", $page)] : [];
        $pagerAmount = ceil($count / $amountEachPage);
        $boolTmp = false;
        for ($i = 1; $i <= $pagerAmount; $i++) {
            if ($i == 1 || ($i >= $page - 1 && $i <= $page + 1) || $i == $pagerAmount) {
                array_push($pagerArr, self::pagerObj($i, $page));
                $boolTmp = true;
            } else if ($boolTmp) {
                array_push($pagerArr, self::pagerObj("...", $page));
                $boolTmp = false;
            }
        }
        if ($page <= $pagerAmount)
            array_push($pagerArr, self::pagerObj(">>", $page));
        return $pagerArr;
    }
    static function pagerObj($var, $page)
    {
        $obj = [];
        $obj['value'] = $var;
        if (is_numeric($var)) {
            $obj['script'] = "window.location.href = '?page=" . $var . "'";
        } else if ($var == ">>") {
            $obj['script'] = "window.location.href = '?page=" . ($page + 1) . "'";
        } else if ($var == "<<") {
            $obj['script'] = "window.location.href = '?page=" . ($page - 1) . "'";
        } else {
            $obj['script'] = "goToPage();";
        }
        return json_decode(json_encode($obj, JSON_NUMERIC_CHECK));
    }
    public static function saveChapter($bookId, $chapterStt, $content)
    {
        $charset = '<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">';
        $content = $charset . $content;
        // Create the context for the request
        $context = stream_context_create(array(
            'http' => array(
                // http://www.php.net/manual/en/context.http.php
                'method' => 'POST',
                'header' => "Content-Type: text/html",
                'timeout' => 120,
                'content' => $content
            )
        ));
        try {
            // Send the request
            $response = file_get_contents(Config::get("configVar.firebase.storageUrl") . $bookId . "%2F" . $chapterStt . '.html', FALSE, $context);
            if ($response === FALSE) {
                die('Error');
            } else {
                return true;
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            Log::info($e->getMessage());
            return false;
        }
        return true;
    }
    public static function getChapterContent($bookId, $chapterStt)
    {
        try {
            // Send the request
            $url = Config::get("configVar.firebase.storageUrl") . $bookId . "%2F" . $chapterStt . '.html?alt=media';
            $contents = file_get_contents($url);
            if ($contents === FALSE) {
                die('Error');
            } else {
                return $contents;
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return "nội dung trang đã bị xóa";
        }
    }
    public static function removeAccents($str)
    {
        if (!$str) return false;
        $unicode = array(
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd' => 'đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
        );
        foreach ($unicode as $nonUnicode => $uni) $str = preg_replace("/($uni)/i", $nonUnicode, $str);
        return $str;
    }

    public static function createSlugId($name)
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
