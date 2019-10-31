<?php 
function timeAgo($time)
{
    $time = strtotime($time);
    $currentTime = time();
    $timeSpan = $currentTime - $time;
    if ($timeSpan < 60) {
        return $timeSpan . " giây trước";
    }
    if ($timeSpan >= 60 && $timeSpan < 3600) {
        return round($timeSpan / 60) . " phút trước";
    }
    if ($timeSpan >= 3600 && $timeSpan < 86400) {
        return round($timeSpan / 3600) . " giờ trước";
    }
    if ($timeSpan >= 86400) {
        return round($timeSpan / 86400) . " ngày trước";
    }
}
