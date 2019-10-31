<?php
  Route::get('/chapter/getchapt/{bookId}/{chapterStt}', 'Chapter@chapterContent');
  Route::get('/chapter/download/{bookId}/{chapterStt}', 'Chapter@download');
  Route::get('/chapter/getlist/{bookId}', 'Chapter@getChapterList');
?>
