<?php

namespace App\Models;

use App\Models\Concerns\BustsPublicCache;
use App\Models\Concerns\Sortable;
use Illuminate\Database\Eloquent\Model;

class StackGroup extends Model
{
    use BustsPublicCache;
    use Sortable;

    protected $fillable = ['label', 'items', 'sort_order'];

    protected $casts = ['items' => 'array'];
}
