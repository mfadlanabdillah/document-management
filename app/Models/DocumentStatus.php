<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentStatus extends Model
{
    use UsesUuid, HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function allowedTransitions()
    {
        return $this->belongsToMany(
            self::class,
            'document_status_transitions',
            'from_status_id',
            'to_status_id'
        );
    }

    public function incomingTransitions()
    {
        return $this->belongsToMany(
            self::class,
            'document_status_transitions',
            'to_status_id',
            'from_status_id'
        );
    }
}
