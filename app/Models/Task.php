<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $table = 'tasks';
    protected $fillable = [
        'title',
        'description',
        'status',
        'due_date',
        'parent_id',
        'created_by',
        'updated_by',
    ];
    
    public function createdByUser()
    {  
        return $this->belongsTo(User::class, 'id', 'created_by');
    }

    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'id', 'updated_by');
    }

    public function dependencies()
    {
        return $this->hasMany(Task::class, 'parent_id');
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'task_id');
    }

    public function assignedUsers()
    {
        return $this->hasManyThrough(User::class, Assignment::class, 'task_id', 'id', 'id', 'user_id');
    }
}

