<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\TrelloController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/laravel-documentation', function () { return view('laravel-documentation'); })->name('laravel-documentation');
Route::get('/', function () { return view('dashboard'); })->name('dashboard');
Route::get('/projects-java', function () { return view('projects-java'); })->middleware(['auth'])->name('projects-java');
Route::get('/projects-cpp', function () { return view('projects-cpp'); })->middleware(['auth'])->name('projects-cpp');
Route::get('/projects-unity', function () { return view('projects-unity'); })->middleware(['auth'])->name('projects-unity');
Route::get('/projects-unreal-engine', function () { return view('projects-unreal-engine'); })->middleware(['auth'])->name('projects-unreal-engine');
Route::get('/3d-modeling', function () { return view('3d-modeling'); })->middleware(['auth'])->name('3d-modeling');

Route::name('trello.')->prefix('trello')->controller(TrelloController::class)->group(function()
{
    Route::name('post.')->prefix('post')->group(function()
    {
        Route::name('board.create')->post('/board/create', 'post_board_create');
        Route::name('board.update')->post('/board/update', 'post_board_update');
        Route::name('board.destroy')->post('/board/destroy', 'post_board_destroy');

        Route::name('column.create')->post('/column/create', 'post_column_create');
        Route::name('column.update')->post('/column/update', 'post_column_update');
        Route::name('column.destroy')->post('/column/destroy', 'post_column_destroy');

        Route::name('item.create')->post('/item/create', 'post_item_create');
        Route::name('item.update')->post('/item/update', 'post_item_update');
        Route::name('item.destroy')->post('/item/destroy', 'post_item_destroy');

        Route::name('column.reorder')->post('/column/reorder', 'post_column_reorder');
        Route::name('item.reorder')->post('/item/reorder', 'post_item_reorder');
        Route::name('request_update')->post('/request_update', 'post_request_update');
    });
});

require __DIR__.'/auth.php';
