<?php

Route::get('/', 'publish\publish@home');
Route::get('/truyen/{id}', 'publish\publish@detail');
