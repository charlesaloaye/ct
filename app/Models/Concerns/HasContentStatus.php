<?php

namespace App\Models\Concerns;

use App\Support\Status;
use Illuminate\Database\Eloquent\Builder;

trait HasContentStatus
{
    public function scopeVisible(Builder $query): Builder
    {
        return $query->whereIn('status', Status::VISIBLE);
    }

    public function isVisible(): bool
    {
        return in_array($this->status, Status::VISIBLE, true);
    }

    public function isHidden(): bool
    {
        return $this->status === Status::HIDDEN;
    }
}
