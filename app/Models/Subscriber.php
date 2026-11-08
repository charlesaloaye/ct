<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Subscriber extends Model
{
    use HasFactory;

    protected $fillable = ['email', 'confirmed_at'];

    protected $hidden = ['confirm_token', 'unsubscribe_token'];

    protected $casts = ['confirmed_at' => 'datetime'];

    protected static function booted(): void
    {
        static::creating(function (self $subscriber) {
            $subscriber->unsubscribe_token ??= Str::random(48);
            if ($subscriber->confirmed_at === null) {
                $subscriber->confirm_token ??= Str::random(48);
            }
        });
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->whereNotNull('confirmed_at');
    }
}
