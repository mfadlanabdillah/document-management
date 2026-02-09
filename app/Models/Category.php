<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory, UsesUuid;
    //
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}
