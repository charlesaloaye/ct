<?php

namespace App\Models;

use App\Models\Concerns\BustsPublicCache;
use App\Models\Concerns\Sortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use BustsPublicCache;
    use HasFactory;
    use Sortable;

    protected $fillable = ['name', 'logo_path', 'sort_order'];
}
