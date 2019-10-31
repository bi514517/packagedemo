<?php
  Route::post('/book/', 'Book@index');
  Route::get('/book/detail/{id}', 'Book@detail');
  Route::get('/book/detail/{bookId}/{userId}', 'Book@detailWithUserId');
  Route::put('/book/currentchapt/', 'Book@updateCurrentChap');
  Route::get('/book/reading/{userId}', 'Book@getReadingBookList');
  Route::post('/book/reaction/', 'Book@giveReaction');
?>
