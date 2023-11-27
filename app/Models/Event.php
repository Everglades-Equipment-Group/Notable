<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'info',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'all_day',
        'recurring',
        'frequency',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'shared_resources', 'resource_id', 'user_id')
                    ->withPivot('resource_type', 'user_id', 'resource_id', 'can_sort' ,'can_check' ,'can_add' ,'can_edit' ,'can_delete', 'can_share', 'created_at', 'updated_at', 'sort_by', 'sort_direction', 'show_item_info', 'show_deletes', 'input_at')
                    ->withTimestamps()
                    ->wherePivot('resource_type', 'event');
    }
}
