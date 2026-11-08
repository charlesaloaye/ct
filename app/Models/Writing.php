<?php

namespace App\Models;

use App\Models\Concerns\BustsPublicCache;
use App\Models\Concerns\HasContentStatus;
use App\Models\Concerns\Sortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** An article Charles published elsewhere (Medium, DEV, Hashnode). The blog links to it. */
class Writing extends Model
{
    use BustsPublicCache;
    use HasContentStatus;
    use HasFactory;
    use Sortable;

    protected $fillable = ['title', 'publication', 'url', 'published_on', 'summary', 'status', 'sort_order'];

    protected $casts = ['published_on' => 'date'];
}
