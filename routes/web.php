<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HabitsController;
use App\Http\Controllers\HabitRecordsController;
use App\Http\Controllers\CategoriesController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [HabitsController::class, 'dashboard'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    Route::resource('habits', HabitsController::class);
    Route::resource('categories', CategoriesController::class);
    
    // 記録機能のルート
    Route::post('habits/{habit}/record', [HabitRecordsController::class, 'store'])->name('habit-records.store');
    Route::get('habits/{habit}/records', [HabitRecordsController::class, 'index'])->name('habit-records.index');
    Route::delete('habit-records/{record}', [HabitRecordsController::class, 'destroy'])->name('habit-records.destroy');
});

require __DIR__.'/auth.php';
