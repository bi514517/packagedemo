<?php
  Route::get('/comment/getCommentByBookId/{bookId}', 'Comment@getCommentByBookId');
  Route::get('/comment/{page}/{numberOfPage}', 'Comment@getComment');
  Route::post('/comment/', 'Comment@createComment');
?>
