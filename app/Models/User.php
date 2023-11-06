<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
// use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function notes(): BelongsToMany
    {
        return $this->belongsToMany(Note::class, 'shared_resources', 'user_id', 'resource_id')
                    ->withPivot('resource_type', 'user_id', 'resource_id', 'can_sort' ,'can_check' ,'can_add' ,'can_edit' ,'can_delete', 'can_share', 'created_at', 'updated_at', 'sort_by', 'sort_direction', 'show_checks', 'move_checked', 'show_item_info', 'show_deletes', 'input_at')
                    ->withTimestamps()
                    ->wherePivot('resource_type', 'note');
    }

    public function records(): BelongsToMany
    {
        return $this->belongsToMany(Record::class, 'shared_resources', 'user_id', 'resource_id')
                    ->withPivot('resource_type', 'user_id', 'resource_id', 'can_sort' ,'can_check' ,'can_add' ,'can_edit' ,'can_delete', 'can_share', 'created_at', 'updated_at', 'sort_by', 'sort_direction','show_item_info', 'show_deletes', 'input_at', 'show_total', 'show_timeframe', 'show_units', 'show_time', 'show_date')->withTimestamps()->wherePivot('resource_type', 'record');
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'shared_resources', 'user_id', 'resource_id')
                    ->withPivot('resource_type', 'user_id', 'resource_id', 'can_sort' ,'can_check' ,'can_add' ,'can_edit' ,'can_delete', 'can_share', 'created_at', 'updated_at', 'sort_by', 'sort_direction','show_item_info', 'show_deletes', 'input_at')->withTimestamps()->wherePivot('resource_type', 'event');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
