<?php

namespace App\Models;

use App\Models\Concerns\BustsPublicCache;
use App\Models\Concerns\HasContentStatus;
use App\Models\Concerns\Sortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** Independent media: an interview, podcast, talk or article about Charles. Real coverage only. */
class Appearance extends Model
{
    use BustsPublicCache;
    use HasContentStatus;
    use HasFactory;
    use Sortable;

    public const KINDS = ['interview', 'podcast', 'talk', 'article', 'feature'];

    protected $fillable = ['title', 'outlet', 'kind', 'url', 'published_on', 'summary', 'status', 'sort_order'];

    protected $casts = ['published_on' => 'date'];
}
