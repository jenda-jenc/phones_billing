<?php

use App\Http\Controllers\GroupController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\InvoiceController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});
Route::get('/dashboard', [InvoiceController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/invoices/{invoice}/download/{format}', [InvoiceController::class, 'download'])
    ->whereIn('format', ['csv', 'pdf'])
    ->middleware(['auth', 'verified'])
    ->name('invoices.download');

Route::get('/import', [ImportController::class, 'index'])->middleware(['auth', 'verified'])->name('import');
Route::post('/import/process', [ImportController::class, 'processImport'])->middleware(['auth', 'verified'])->name('import.process');
Route::post('/import-table', [ImportController::class, 'processImport'])->middleware(['auth', 'verified'])->name('import.process');
Route::get('/import/data', function () {
    return [
        'tariffs' => \App\Models\Tariff::all(),
        'groups' => \App\Models\Group::with('tariffs')->get(),
    ];
})->name('import.data');
//Route::get('/osoby', function () {
//    return Inertia::render('Osoby');
//})->middleware(['auth', 'verified'])->name('persons.index');

Route::get('/osoby', [PersonController::class, 'index'])->middleware(['auth', 'verified'])->name('persons.index');
Route::post('/osoby', [PersonController::class, 'store'])->middleware(['auth', 'verified'])->name('persons.store');
Route::put('/osoby/{person}', [PersonController::class, 'update'])->middleware(['auth', 'verified'])->name('persons.update');
Route::delete('/osoby/{person}', [PersonController::class, 'destroy'])->middleware(['auth', 'verified'])->name('persons.destroy');
Route::patch('/osoby/{person}/group', [PersonController::class, 'assignGroup'])->middleware(['auth', 'verified'])->name('persons.assignGroup');
Route::post('/osoby/detach-group', [PersonController::class, 'detachGroup'])->name('persons.detach-group');

Route::get('/tarify', [GroupController::class, 'index'])->middleware(['auth', 'verified'])->name('groups.index');
Route::post('/tarify', [GroupController::class, 'store'])->middleware(['auth', 'verified'])->name('groups.store');
Route::put('/tarify/{tarif}', [GroupController::class, 'update'])->middleware(['auth', 'verified'])->name('groups.update');
Route::delete('/tarify/{tarif}', [GroupController::class, 'destroy'])->middleware(['auth', 'verified'])->name('groups.destroy');
Route::post('/tarify/detach', [GroupController::class, 'detachTariff'])->middleware(['auth', 'verified'])->name('groups.detach-tariff');
Route::post('/tarify/attach', [GroupController::class, 'attachTariff'])->middleware(['auth', 'verified'])->name('groups.attach-tariff');
Route::get('/tarify-seznam', [GroupController::class, 'listTariffs'])->middleware(['auth', 'verified'])->name('groups.listTariffs');
Route::post('/tarify-bulk', [GroupController::class, 'bulkStoreTariffs'])->name('tariffs.bulk-store');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
