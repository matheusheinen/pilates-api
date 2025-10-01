<?php

use Illuminate\Support\Facades\Route;

// Qualquer rota web vai carregar a view principal do Vue
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '.*');
