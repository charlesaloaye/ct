<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'need', 'body', 'reply_body', 'read_at', 'replied_at', 'archived_at', 'ip', 'user_agent'];

    protected $casts = ['read_at' => 'datetime', 'replied_at' => 'datetime', 'archived_at' => 'datetime'];

    /** Short inbox labels for the default contact form options; any other option shows as typed. */
    public const NEEDS = [
        'Book a session or mentorship' => 'Session',
        'Rescue a stalled project' => 'Rescue',
        'New web app' => 'Web app',
        'Mobile app' => 'Mobile app',
        'APIs, payments or integrations' => 'APIs & payments',
        'Maintenance & support' => 'Support',
    ];

    public function scopeInbox(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function needLabel(): string
    {
        return self::NEEDS[$this->need] ?? ($this->need ?: 'Enquiry');
    }
}
