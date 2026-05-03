<?php

use Illuminate\Support\Facades\Route;

//user:
Route::get('/', function () {
    return view('Main');
});

Route::post('/upload', 'App\Http\Controllers\FileUploadController@uploadFile')->name('upload.file');
Route::post('/search', 'App\Http\Controllers\SearchController@search')->name('search.file');
Route::get('/search/suggest', 'App\Http\Controllers\SearchController@suggest')->name('search.suggest');
Route::get('/', 'App\Http\Controllers\FileUploadController@index')->name('main');


//login:
Route::get('/login', function () {
    return view('Login');
})->name('login.file');

Route::post('/login', 'App\Http\Controllers\LoginController@enter')->name('login.file');


//admin:
Route::post('/admin/insert', 'App\Http\Controllers\AdminController@insert')->name('insert.file');
Route::post('/admin/manualInsert', 'App\Http\Controllers\AdminController@manualInsert')->name('manualInsert.file');
Route::post('/admin/update', 'App\Http\Controllers\AdminController@update')->name('update.file');
Route::post('/admin/select', 'App\Http\Controllers\AdminController@select')->name('select.file');

Route::match(['get', 'post'], '/admin/select', 'App\Http\Controllers\AdminController@select')->name('select.file');

Route::post('/admin/add-column', 'App\Http\Controllers\AdminController@addLocale')->name('addLocale.file');
Route::post('/admin/drop-column', 'App\Http\Controllers\AdminController@deleteLocale')->name('deleteLocale.file');
Route::post('/admin/delete', 'App\Http\Controllers\AdminController@delete')->name('delete.file');

Route::get('/admin', 'App\Http\Controllers\AdminController@index')->name('admin.index');

//translator:
Route::get('/translator', 'App\Http\Controllers\TranslatorController@index')->name('translator.index');
Route::match(['get', 'post'],'/select_tr', 'App\Http\Controllers\TranslatorController@select')->name('select_tr.file');

Route::post('/translator/update', 'App\Http\Controllers\TranslatorController@update')->name('update_tr.file');


