<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Route::get('/home', function () {
//      return response()->json(['error'=>'no token provided'], 500, null);
// });
// Delete task by an authorized user
Route::delete('/tasks/{taskId}', [TaskController::class, 'destroy'])->name('task.destroy');