<?php

use App\Http\Controllers\GameController;
use App\Livewire\Escalacao;
use App\Livewire\Financas;
use App\Livewire\Mercado;
use App\Livewire\Partida;
use Illuminate\Support\Facades\Route;

Route::get('/', [GameController::class, 'home'])->name('game.home');
Route::post('/season/start', [GameController::class, 'start'])->name('game.start');
Route::post('/season/estadual', [GameController::class, 'startEstadual'])->name('game.estadual');
Route::post('/season/advance', [GameController::class, 'advance'])->name('game.advance');

Route::get('/escalacao', Escalacao::class)->name('game.escalacao');
Route::get('/partida', Partida::class)->name('game.partida');
Route::get('/mercado', Mercado::class)->name('game.mercado');
Route::get('/financas', Financas::class)->name('game.financas');
