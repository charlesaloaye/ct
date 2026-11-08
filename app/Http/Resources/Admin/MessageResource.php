<?php

namespace App\Http\Resources\Admin;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * Matches the admin sample message shape: {name, initials, email, type, preview, body}.
 *
 * @mixin Message
 */
class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'initials' => collect(preg_split('/\s+/', trim($this->name)))
                ->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->join(''),
            'email' => $this->email,
            'need' => $this->need,
            'type' => $this->needLabel(),
            'preview' => Str::limit(Str::squish($this->body), 60, '...'),
            'body' => $this->body,
            'read' => $this->read_at !== null,
            'readAt' => $this->read_at?->toIso8601String(),
            'archived' => $this->archived_at !== null,
            'replyBody' => $this->reply_body,
            'repliedAt' => $this->replied_at?->toIso8601String(),
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }
}
