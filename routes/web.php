<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\RecordController;
use Illuminate\Http\Request;
use Livewire\Volt\Volt;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// to use component as full page
Volt::route('/notifications', 'notifications');

Volt::route('/note/{id}', 'notes/note', function (Request $request, string $id) {
    return 'Note '.$id;
});

Volt::route('/record/{id}', 'records/record', function (Request $request, string $id) {
    return 'Record '.$id;
});

Volt::route('/event/{id}', 'events/event', function (Request $request, string $id) {
    return 'Event '.$id;
});

// Route::view('note', 'livewire.notes.note')
//     ->middleware(['auth'])
//     ->name('note');

// Route::get('/note', [NoteController::class, 'index'])
//     ->middleware(['auth', 'verified'])
//     ->name('note');

// Route::get('/records', [RecordController::class, 'index'])
//     ->middleware(['auth', 'verified'])
//     ->name('records');

require __DIR__.'/auth.php';
