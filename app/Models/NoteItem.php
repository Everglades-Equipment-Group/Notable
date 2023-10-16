<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'checked'
    ];

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }
}
