<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use UsesUuid;
    //
    protected $fillable = [
        'name',
        'slug'
    ];

    public function documents()
    {
        return $this->belongsToMany(Document::class);
    }
}
