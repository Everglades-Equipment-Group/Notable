<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'info',
        'direction'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function noteItems(): HasMany
    {
        return $this->hasMany(NoteItem::class);
    }
}
