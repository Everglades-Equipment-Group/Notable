<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Record extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'info',
        'units',
        'measuring',
        'sort_by',
        'sort_direction',
        'input_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recordEntries(): HasMany
    {
        return $this->hasMany(RecordEntry::class);
    }
}
