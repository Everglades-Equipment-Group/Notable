<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecordEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'info',
        'time',
        'date'
    ];

    public function record(): BelongsTo
    {
        return $this->belongsTo(Record::class);
    }
}
