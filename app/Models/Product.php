<?php

namespace App\Models;

use App\Models\Concerns\BustsPublicCache;
use App\Models\Concerns\HasContentStatus;
use App\Models\Concerns\Sortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use BustsPublicCache;
    use HasContentStatus;
    use HasFactory;
    use Sortable;

    protected $fillable = ['venture_id', 'mono', 'logo_path', 'role', 'name', 'category', 'description', 'proof', 'domain', 'url', 'status', 'sort_order'];

    public function venture(): BelongsTo
    {
        return $this->belongsTo(Venture::class);
    }
}
