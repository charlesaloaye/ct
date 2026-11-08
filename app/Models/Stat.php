<?php

namespace App\Models;

use App\Models\Concerns\BustsPublicCache;
use App\Models\Concerns\Sortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stat extends Model
{
    use BustsPublicCache;
    use HasFactory;
    use Sortable;

    protected $fillable = ['value', 'label', 'short_label', 'sort_order'];
}
