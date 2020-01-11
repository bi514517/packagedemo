<?php

Route::get('/', 'publish\publish@home');
Route::get('/truyen/{id}', 'publish\publish@detail');
Route::get('/doc-truyen/{bookid}/chuong-{chapterstt}.html', 'publish\publish@chapter');
