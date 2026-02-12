<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentStatusTransition extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'from_status_id',
        'to_status_id',
    ];

    public function fromStatus()
    {
        return $this->belongsTo(DocumentStatus::class, 'from_status_id');
    }

    public function toStatus()
    {
        return $this->belongsTo(DocumentStatus::class, 'to_status_id');
    }
}
