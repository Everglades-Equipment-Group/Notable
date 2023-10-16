<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Record extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'info',
        'units'
    ];

    public function recordEntries(): HasMany
    {
        return $this->hasMany(RecordEntry::class);
    }
}
