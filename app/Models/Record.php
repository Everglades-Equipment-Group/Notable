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
        'sort_by',
        'sort_direction',
        'input_at',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'shared_resources', 'resource_id', 'user_id')
                    ->withPivot('access', 'resource_type', 'user_id', 'resource_id','can_sort' ,'can_check' ,'can_add' ,'can_edit' ,'can_delete', 'can_share', 'created_at', 'updated_at')
                    ->withTimestamps()
                    ->wherePivot('resource_type', 'record');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(RecordEntry::class);
    }
}
