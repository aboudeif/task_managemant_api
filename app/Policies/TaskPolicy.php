<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TaskPolicy
{
    protected $user;

    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        $this->user = Auth::guard('sanctum')->user();
    }
    
    public function index()
    {
        // Only managers can index all tasks
        return $this->user->role === 'manager';
    }

    public function show()
    {
        // Only managers can show all tasks
        return $this->user->role === 'manager';
    }

    public function create()
    {
        // Only managers can create tasks
        return $this->user->role === 'manager';
    }
    public function destroy()
    {
        // Only managers can create tasks
        return $this->user->role === 'manager';
    }

    public function update()
    {
        // Only managers can update tasks
        return $this->user->role === 'manager';
    }

    public function assign()
    {
        // Only managers can assign tasks
        return $this->user->role === 'manager';
    }

    public function viewAllTasks()
    {
        // Only managers can retrieve all tasks
        return $this->user->role === 'manager';
    }

    public function addDependencies()
    {
        // Only managers can add task dependencies
        return $this->user->role === 'manager';
    }

}
