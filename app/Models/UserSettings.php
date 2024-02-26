<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'theme',
        'sort_notes_by',
        'sort_notes_direction',
        'sort_items_by',
        'sort_items_direction',
        'sort_events_by',
        'sort_events_direction',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
