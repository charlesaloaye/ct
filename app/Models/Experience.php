<?php

namespace App\Models;

use App\Models\Concerns\BustsPublicCache;
use App\Models\Concerns\HasContentStatus;
use App\Models\Concerns\Sortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    use BustsPublicCache;
    use HasContentStatus;
    use HasFactory;
    use Sortable;

    protected $fillable = ['years', 'role', 'org', 'place', 'description', 'status', 'sort_order'];
}
