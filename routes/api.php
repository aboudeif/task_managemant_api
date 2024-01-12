<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::prefix('v1')->group(function () {
  

  // Guest users can log in
  Route::post('/login', [AuthController::class, 'login'])->name('login');
  
  // Show all tasks assigned to user
  Route::get('/tasks/user', [TaskController::class, 'indexUserTasks'])->name('task.indexUser');
  
  // Authenticated routes
  Route::middleware('auth:sanctum')->group(function () {
    
    // Update task status by user
    Route::put('/tasks/{taskId}/update-status', [TaskController::class, 'updateStatus'])->name('task.updateStatus');
      
    // Log out route
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Retrieve all tasks
    Route::get('/tasks', [TaskController::class, 'index'])->name('task.index');
    
    // Show task by ID
    Route::get('/tasks/{id}', [TaskController::class, 'show'])->name('task.show');
    
    // Show task with all dependencies
    Route::get('/tasks/{id}', [TaskController::class, 'show'])->name('task.show');
    
    // Create a new task
    Route::post('/tasks', [TaskController::class, 'store'])->name('task.store');
    
    // Update task by an authorized user
    Route::put('/tasks/{id}', [TaskController::class, 'update'])->name('task.update');
    
    // Assign a task to a user
    Route::post('/tasks/assign', [TaskController::class, 'assignTask'])->name('task.assignToUser');
    
    // Filter tasks based on specified criteria
    Route::post('/tasks/filter', [TaskController::class, 'filterTasks'])->name('task.filter');

    // Add tasks as dependencies of a task
    Route::post('/tasks/{taskId}/dependencies', [TaskController::class, 'addDependencies'])
    ->name('task.addDependencies');
    
    // Delete task by an authorized user
    Route::delete('/tasks', [TaskController::class, 'destroy'])->name('task.destroy');
    
  });
  

});

// Route::get('/', function() {
//   return response()->json(['error' => 'No request keys are provided'], 401);
// })->name('no-request-keys');
