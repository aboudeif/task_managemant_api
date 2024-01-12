<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\User;
use App\Models\Assignment;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /***
     * retrive all tasks
     */
    public function index()
    {
        // Ensure the request is authenticated with Sanctum
        if (!$this->authorize('view-all-tasks', Task::class)) {
            return response()->json(['error' => 'this action is unautorized'], 403);
        }

        $tasks = Task::all();
        return response()->json($tasks);
    }

     /**
     * Add task dependencies
     */
    public function addDependencies(Request $request, $taskId)
    {
        $request->validate([
            'assigned_tasks' => 'required|array',
            'assigned_tasks.*' => 'exists:tasks,id',
        ]);

        $dependenceTaskId = $request->input('dependence_task_id');
        $assignedTaskIds = $request->input('assigned_tasks');

        // Find the dependency task
        $dependenceTask = Task::findOrFail($taskId);

        // Find all assigned tasks
        $assignedTasks = Task::whereIn('id', $assignedTaskIds)->get();

        // Assign the dependence task to each assigned task
        foreach ($assignedTasks as $assignedTask) {
            $assignedTask->update(['parent_id' => $dependenceTask->id]);
        }

        return response()->json(['message' => 'Tasks successfully assigned dependencies']);
    }

    /**
     * Show tasks assigned to user
     */
    public function indexUserTasks(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $tasks = $user->assignments->pluck('task');
        return response()->json($tasks);
    }

    /**
     * Show task with all dependencies tasks
     */
    public function show($taskId)
    {
        $this->authorize('show', Task::class);
        $task = Task::with('dependencies')->findOrFail($taskId);
        return response()->json($task);
    }

    /**
     * create new task
     */
    public function store(Request $request)
    {
        $this->authorize('create', Task::class);
        // Validation rules
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:pending,completed,canceled',
            'due_date' => 'required|date',
            'parent_id' => 'nullable|exists:tasks,id',
            'created_by' => 'required|exists:users,id',
            'updated_by' => 'nullable|exists:users,id',
        ];

        // Custom error messages
        $messages = [
            'parent_id.exists' => 'The specified parent task does not exist.',
            'created_by.exists' => 'The specified creator user does not exist.',
            'updated_by.exists' => 'The specified updater user does not exist.',
        ];

        // Validate the request data
        $this->validate($request, $rules, $messages);

        // Create the task
        $task = Task::create($request->all());

        return response()->json($task, 201);
    }

    /**
     * update whole task
     */
    public function update(Request $request, $taskId)
    {
        $task = Task::findOrFail($taskId);
        $this->authorize('update', Task::class);

        $this->validate($request, [
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:pending,completed,canceled',
            'due_date' => 'nullable|date',
            'parent_id' => 'nullable|exists:tasks,id',
            'created_by' => 'exists:users,id',
            'updated_by' => 'nullable|exists:users,id',
        ]);

        // Check if the new status is 'completed'
        if ($request->input('status') === 'completed') {
            // Check if all dependencies are completed
            $dependencies = $task->dependencies;
    
            foreach ($dependencies as $dependency) {
                if ($dependency->status !== 'completed') {
                    return response()->json(['error' => 'Cannot mark task as completed until all dependencies are completed'], 422);
                }
            }
        }

        $task->update($request->all());
        return response()->json($task);
    }

    public function updateStatus(Request $request, $taskId)
    {    
        // Validation rules
        $rules = [
            'status' => 'required|in:pending,completed,canceled',
        ];
    
        // Custom error messages
        $messages = [
            'status.in' => 'The status must be one of: pending, completed, canceled.',
        ];
    
        // Validate the request data
        $this->validate($request, $rules, $messages);
    
        // Find the task
        $task = Auth::guard('sanctum')->user()->assignments->pluck('task')->where('id','=',$taskId)->first();
        if (!$task) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        // Check if the new status is 'completed'
        if ($request->input('status') === 'completed') {
            // Check if all dependencies are completed
    
            $dependency = $task->dependencies()->where('status', '<>', 'completed')->first();

            if ($dependency) {
                return response()->json(['error' => 'Cannot mark task as completed until all dependencies are completed.'], 422);
            }
        }
    
        // Update the task status
        $task->update(['status' => $request->input('status')]);
    
        return response()->json($task);
    }
    

    /**
     * assign a task to user
     */
    public function assignTask(Request $request)
    {
        // Find the task
        $task = Task::findOrFail($request->task_id);
        
        // Authorize the user
        $this->authorize('assign', Task::class);

        // Validation rules
        $rules = [
            'task_id' => 'required|exists:tasks,id',
            'user_id' => 'required|exists:users,id',
        ];

        // Custom error messages
        $messages = [
            'task_id.exists' => 'The specified task does not exist.',
            'user_id.exists' => 'The specified user does not exist.',
        ];

        // Validate the request data
        $this->validate($request, $rules, $messages);

        // Check if the assignment already exists
        if ($task->assignedUsers()->where('user_id', $request->user_id)->exists()) {
            return response()->json(['message' => 'The task is already assigned to the specified user.'], 422);
        }

        // Create a new assignment if it doesn't exist
        $assignment = Assignment::firstOrCreate([
            'task_id' => $request->task_id,
            'user_id' => $request->user_id,
        ]);

        return response()->json($assignment);
    }


    /**
     * delete task by authorized user
     */
    public function destroy(Request $request)
    {
        // Authorize the action using the policy
        $this->authorize('destroy', Task::class);

        // Validation rules
        $rules = [
            'task_id' => 'required|exists:tasks,id',
        ];

        // Custom error messages
        $messages = [
            'task_id.exists' => 'The specified task does not exist.',
        ];

        // Validate the request data
        $this->validate($request, $rules, $messages);

        $task = Task::findOrFail($request->task_id);

        // Delete the task
        $task->delete();

        return response()->json(['message' => 'The task has deleted successfully'], 204);
    }

    /**
     * filter tasks 
     */
    public function filterTasks(Request $request)
    {
        // Validation rules
        $rules = [
            'status' => ['nullable', Rule::in(['pending', 'completed', 'canceled'])],
            'due_date_start' => 'nullable|date',
            'due_date_end' => 'nullable|date|after_or_equal:due_date_start',
            'assigned_user_id' => 'nullable|exists:users,id',
        ];

        // Custom error messages
        $messages = [
            'due_date_end.after_or_equal' => 'The end due date must be after or equal to the start due date.',
            'assigned_user_id.exists' => 'The specified assigned user does not exist.',
        ];

        // Validate the request data
        $this->validate($request, $rules, $messages);

        // Filter tasks based on the validated criteria
        $tasks = Task::when($request->filled('status'), function ($query) use ($request) {
            $query->where('status', $request->input('status'));
        })
            ->when($request->filled('due_date_start'), function ($query) use ($request) {
                $query->where('due_date', '>=', $request->input('due_date_start'));
            })
            ->when($request->filled('due_date_end'), function ($query) use ($request) {
                $query->where('due_date', '<=', $request->input('due_date_end'));
            })
            ->when($request->filled('assigned_user_id'), function ($query) use ($request) {
                $query->whereHas('assignedUsers', function ($subquery) use ($request) {
                    $subquery->where('users.id', $request->input('assigned_user_id'));
                });
            })
            ->get();

        return response()->json($tasks);
    }
}
