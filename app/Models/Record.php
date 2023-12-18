<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Record extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'info',
        'units',
        'measuring',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'shared_resources', 'resource_id', 'user_id')
                    ->withPivot('resource_type', 'user_id', 'resource_id','can_sort' ,'can_check' ,'can_add' ,'can_edit' ,'can_delete', 'can_share', 'created_at', 'updated_at', 'sort_by', 'sort_direction', 'show_deletes', 'input_at', 'show_total', 'show_timeframe', 'show_units', 'show_time', 'show_date')
                    ->withTimestamps()
                    ->wherePivot('resource_type', 'record');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(RecordEntry::class);
    }
}
